<?php

namespace App\Services;

use App\Models\IndustrialMachine;
use App\Models\LogisticAsset;
use App\Models\MaintenanceRequest;
use App\Models\MaintenanceTask;
use App\Models\PreventivePlan;
use App\Models\TechnicalAsset;
use App\Models\User;
use App\Notifications\MaintenanceAlertNotification;
use App\Repositories\Contracts\MaintenanceRepositoryInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;

class MaintenanceService
{
    public function __construct(private readonly MaintenanceRepositoryInterface $maintenanceRepository)
    {
    }

    public function createRequest(array $data, int $requesterId): MaintenanceRequest
    {
        return DB::transaction(function () use ($data, $requesterId): MaintenanceRequest {
            $requester = $this->maintenanceRepository->findUserById($requesterId);
            $recurrenceCount = $this->calculateRecurrenceCount($data);
            $normalized = $this->normalizeAssetBindings($data);

            $ticket = $this->maintenanceRepository->createRequest([
                ...$normalized,
                'request_code' => $this->generateRequestCode(),
                'requester_id' => $requesterId,
                'sector' => $normalized['sector'] ?? $requester?->sector,
                'occurrence_at' => $normalized['occurrence_at'] ?? now(),
                'is_recurrent' => $recurrenceCount > 1,
                'recurrence_count' => $recurrenceCount,
                'requested_at' => now(),
            ]);

            $targets = $this->maintenanceRepository
                ->usersByRoles(['super_admin', 'manager', 'technician'])
                ->filter(fn (User $user): bool => $user->isRole('super_admin') || empty($ticket->sector) || $user->sector === $ticket->sector);
            Notification::send($targets, new MaintenanceAlertNotification(__('gmao.msg.new_request'), "{$ticket->request_code}"));

            return $ticket;
        });
    }

    public function updateRequestStatus(MaintenanceRequest $request, string $status): void
    {
        DB::transaction(function () use ($request, $status): void {
            $request->status = $status;

            if ($status === 'completed') {
                $request->resolved_at = now();
                $admins = $this->maintenanceRepository
                    ->usersByRoles(['super_admin', 'manager'])
                    ->filter(fn (User $user): bool => $user->isRole('super_admin') || empty($request->sector) || $user->sector === $request->sector);
                Notification::send($admins, new MaintenanceAlertNotification(__('gmao.msg.task_done'), "#{$request->id}"));
            }

            $this->maintenanceRepository->saveRequest($request);
        });
    }

    public function createPlan(array $data): PreventivePlan
    {
        $data = $this->normalizePlanBindings($data);
        $data['is_active'] = (bool) ($data['is_active'] ?? true);
        $data['requires_shutdown'] = (bool) ($data['requires_shutdown'] ?? false);

        return $this->maintenanceRepository->createPlan($data);
    }

    public function updateRequest(MaintenanceRequest $request, array $data): bool
    {
        $data = $this->normalizeAssetBindings($data);
        $recurrenceCount = $this->calculateRecurrenceCount($data, $request->id);

        if (empty($request->request_code)) {
            $data['request_code'] = $this->generateRequestCode();
        }

        if (($data['status'] ?? null) === 'completed' && !$request->resolved_at) {
            $data['resolved_at'] = now();
        }

        if (($data['status'] ?? null) !== 'completed') {
            $data['resolved_at'] = null;
        }

        $data['is_recurrent'] = $recurrenceCount > 1;
        $data['recurrence_count'] = $recurrenceCount;

        return $this->maintenanceRepository->updateRequest($request, $data);
    }

    public function deleteRequest(MaintenanceRequest $request): ?bool
    {
        return $this->maintenanceRepository->deleteRequest($request);
    }

    public function updatePlan(PreventivePlan $plan, array $data): bool
    {
        $data = $this->normalizePlanBindings($data);
        $data['is_active'] = (bool) ($data['is_active'] ?? false);
        $data['requires_shutdown'] = (bool) ($data['requires_shutdown'] ?? false);

        return $this->maintenanceRepository->updatePlan($plan, $data);
    }

    public function deletePlan(PreventivePlan $plan): ?bool
    {
        return $this->maintenanceRepository->deletePlan($plan);
    }

    public function createTask(array $data): MaintenanceTask
    {
        return DB::transaction(function () use ($data): MaintenanceTask {
            if (empty($data['sector']) && !empty($data['maintenance_request_id'])) {
                $linkedRequest = MaintenanceRequest::find($data['maintenance_request_id']);
                $data['sector'] = $linkedRequest?->sector;
            }

            if (($data['status'] ?? null) === 'completed') {
                $data['completed_at'] = now();
            }

            $task = $this->maintenanceRepository->createTask($data);

            if (!empty($data['technician_id'])) {
                $technician = $this->maintenanceRepository->findUserById((int) $data['technician_id']);
                if ($technician) {
                    $technician->notify(new MaintenanceAlertNotification(__('gmao.msg.new_assignment'), "#{$task->id}"));
                }
            }

            return $task;
        });
    }

    public function updateTask(MaintenanceTask $task, array $data): bool
    {
        if (($data['status'] ?? null) === 'completed' && !$task->completed_at) {
            $data['completed_at'] = now();
        }

        if (($data['status'] ?? null) !== 'completed') {
            $data['completed_at'] = null;
        }

        return $this->maintenanceRepository->updateTask($task, $data);
    }

    public function recordPreventiveExecution(MaintenanceTask $task, array $data, int $actorId): ?MaintenanceRequest
    {
        return DB::transaction(function () use ($task, $data, $actorId): ?MaintenanceRequest {
            $taskData = [
                'status' => $data['execution_status'],
                'did_lubrication' => (bool) ($data['did_lubrication'] ?? false),
                'did_measurement' => (bool) ($data['did_measurement'] ?? false),
                'did_inspection' => (bool) ($data['did_inspection'] ?? false),
                'did_replacement' => (bool) ($data['did_replacement'] ?? false),
                'did_cleaning' => (bool) ($data['did_cleaning'] ?? false),
                'anomaly_detected' => (bool) ($data['anomaly_detected'] ?? false),
                'measurement_reading' => $data['measurement_reading'] ?? null,
                'inspection_location' => $data['inspection_location'] ?? null,
                'execution_note' => $data['execution_note'] ?? null,
                'anomaly_note' => $data['anomaly_note'] ?? null,
                'actual_hours' => $data['actual_hours'] ?? $task->actual_hours,
                'execution_checks' => $this->normalizeExecutionChecks($data['execution_checks'] ?? []),
            ];

            if ($taskData['status'] === 'completed') {
                $taskData['completed_at'] = now();
                $taskData['round_completed_at'] = now();
            } elseif ($taskData['status'] === 'in_progress') {
                $taskData['completed_at'] = null;
            }

            $openedRequest = null;
            $shouldCreateRequest = $taskData['anomaly_detected'] && (bool) ($data['create_request_on_anomaly'] ?? false);

            if ($shouldCreateRequest && empty($task->maintenance_request_id)) {
                $openedRequest = $this->createRequestFromPreventiveTask($task, $taskData, $actorId);
                $taskData['maintenance_request_id'] = $openedRequest->id;
            }

            $this->maintenanceRepository->updateTask($task, $taskData);

            if ($task->preventivePlan && $taskData['status'] === 'completed') {
                $task->preventivePlan->update(['last_done_date' => now()->toDateString()]);
            }

            return $openedRequest;
        });
    }

    public function deleteTask(MaintenanceTask $task): ?bool
    {
        return $this->maintenanceRepository->deleteTask($task);
    }

    private function generateRequestCode(): string
    {
        $prefix = now()->format('Ymd');

        for ($attempt = 0; $attempt < 8; $attempt++) {
            $candidate = sprintf('MR-%s-%06d', $prefix, random_int(1, 999999));
            if (!MaintenanceRequest::where('request_code', $candidate)->exists()) {
                return $candidate;
            }
        }

        return sprintf('MR-%s-%s', $prefix, now()->format('Hisv'));
    }

    private function calculateRecurrenceCount(array $data, ?int $excludeRequestId = null): int
    {
        $windowDays = $this->recurrenceWindowDays($data['severity'] ?? 'medium');
        $query = MaintenanceRequest::query()
            ->where('maintenance_domain', $data['maintenance_domain'] ?? '')
            ->where('failure_mode', $data['failure_mode'] ?? '')
            ->where('created_at', '>=', now()->subDays($windowDays));

        if ($excludeRequestId !== null) {
            $query->whereKeyNot($excludeRequestId);
        }

        $assetType = $data['asset_type'] ?? null;
        if ($assetType === 'industrial' && !empty($data['industrial_machine_id'])) {
            $query->where('industrial_machine_id', $data['industrial_machine_id']);
        } elseif ($assetType === 'technical' && !empty($data['technical_asset_id'])) {
            $query->where('technical_asset_id', $data['technical_asset_id']);
        } elseif ($assetType === 'logistic' && !empty($data['logistic_asset_id'])) {
            $query->where('logistic_asset_id', $data['logistic_asset_id']);
        } elseif (!empty($data['asset_reference'])) {
            $query->where('asset_reference', $data['asset_reference']);
        }

        return $query->count() + 1;
    }

    private function recurrenceWindowDays(string $severity): int
    {
        return match ($severity) {
            'critical' => 365,
            'high' => 180,
            'medium' => 120,
            default => 90,
        };
    }

    private function normalizeAssetBindings(array $data): array
    {
        $type = $data['asset_type'] ?? 'other';
        $data['industrial_machine_id'] = $data['industrial_machine_id'] ?? null;
        $data['technical_asset_id'] = $data['technical_asset_id'] ?? null;
        $data['logistic_asset_id'] = $data['logistic_asset_id'] ?? null;

        if ($type !== 'industrial') {
            $data['industrial_machine_id'] = null;
        }
        if ($type !== 'technical') {
            $data['technical_asset_id'] = null;
        }
        if ($type !== 'logistic') {
            $data['logistic_asset_id'] = null;
        }

        if (empty($data['asset_reference'])) {
            if ($type === 'industrial' && !empty($data['industrial_machine_id'])) {
                $data['asset_reference'] = IndustrialMachine::whereKey($data['industrial_machine_id'])->value('code');
            } elseif ($type === 'technical' && !empty($data['technical_asset_id'])) {
                $data['asset_reference'] = TechnicalAsset::whereKey($data['technical_asset_id'])->value('code');
            } elseif ($type === 'logistic' && !empty($data['logistic_asset_id'])) {
                $data['asset_reference'] = LogisticAsset::whereKey($data['logistic_asset_id'])->value('code');
            }
        }

        return $data;
    }

    private function normalizePlanBindings(array $data): array
    {
        $type = $data['asset_type'] ?? 'other';
        $data['industrial_machine_id'] = $data['industrial_machine_id'] ?? null;
        $data['technical_asset_id'] = $data['technical_asset_id'] ?? null;
        $data['logistic_asset_id'] = $data['logistic_asset_id'] ?? null;

        if ($type !== 'industrial') {
            $data['industrial_machine_id'] = null;
        }
        if ($type !== 'technical') {
            $data['technical_asset_id'] = null;
        }
        if ($type !== 'logistic') {
            $data['logistic_asset_id'] = null;
        }

        if (empty($data['asset_reference'])) {
            if ($type === 'industrial' && !empty($data['industrial_machine_id'])) {
                $data['asset_reference'] = IndustrialMachine::whereKey($data['industrial_machine_id'])->value('code');
            } elseif ($type === 'technical' && !empty($data['technical_asset_id'])) {
                $data['asset_reference'] = TechnicalAsset::whereKey($data['technical_asset_id'])->value('code');
            } elseif ($type === 'logistic' && !empty($data['logistic_asset_id'])) {
                $data['asset_reference'] = LogisticAsset::whereKey($data['logistic_asset_id'])->value('code');
            }
        }

        return $data;
    }

    private function createRequestFromPreventiveTask(MaintenanceTask $task, array $taskData, int $actorId): MaintenanceRequest
    {
        $plan = $task->preventivePlan;

        $request = $this->maintenanceRepository->createRequest([
            'request_code' => $this->generateRequestCode(),
            'requester_id' => $actorId,
            'assigned_to' => $task->technician_id,
            'sector' => $task->sector ?: $plan?->sector,
            'asset_type' => $plan?->asset_type ?: 'other',
            'asset_reference' => $plan?->asset_reference ?: null,
            'industrial_machine_id' => $plan?->industrial_machine_id,
            'technical_asset_id' => $plan?->technical_asset_id,
            'logistic_asset_id' => $plan?->logistic_asset_id,
            'issue_category' => 'breakdown',
            'maintenance_domain' => $plan?->maintenance_domain ?: 'other',
            'failure_mode' => $plan?->failure_mode ?: 'other',
            'severity' => 'high',
            'status' => 'pending',
            'location' => $taskData['inspection_location'] ?: null,
            'description' => trim((string) ($taskData['anomaly_note'] ?: $taskData['execution_note'] ?: 'Preventive anomaly detected')),
            'occurrence_at' => now(),
            'requested_at' => now(),
        ]);

        $targets = $this->maintenanceRepository
            ->usersByRoles(['super_admin', 'manager'])
            ->filter(fn (User $user): bool => $user->isRole('super_admin') || empty($request->sector) || $user->sector === $request->sector);
        Notification::send($targets, new MaintenanceAlertNotification(__('gmao.msg.new_request'), "{$request->request_code}"));

        return $request;
    }

    private function normalizeExecutionChecks(array $checks): array
    {
        return collect($checks)
            ->map(function ($item): array {
                return [
                    'label' => trim((string) data_get($item, 'label', '')),
                    'done' => (bool) data_get($item, 'done', false),
                    'note' => trim((string) data_get($item, 'note', '')),
                ];
            })
            ->filter(fn (array $item): bool => $item['label'] !== '')
            ->values()
            ->all();
    }
}
