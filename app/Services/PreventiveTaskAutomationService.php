<?php

namespace App\Services;

use App\Models\MaintenanceTask;
use App\Models\PreventivePlan;
use App\Models\User;
use App\Notifications\MaintenanceAlertNotification;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Schema;
use Throwable;

class PreventiveTaskAutomationService
{
    public function generateDueTasks(?Carbon $today = null): array
    {
        if (!$this->automationColumnsReady()) {
            return ['tasks_created' => 0, 'plans_updated' => 0];
        }

        $today = ($today ?? now())->startOfDay();
        $created = 0;
        $updatedPlans = 0;

        $plans = PreventivePlan::query()
            ->where('is_active', true)
            ->whereNotNull('next_due_date')
            ->whereDate('next_due_date', '<=', $today->toDateString())
            ->get();

        foreach ($plans as $plan) {
            try {
                $cursor = Carbon::parse($plan->next_due_date)->startOfDay();
                $planTouched = false;

                while ($cursor->lessThanOrEqualTo($today)) {
                    $exists = MaintenanceTask::query()
                        ->where('preventive_plan_id', $plan->id)
                        ->whereDate('generated_for_date', $cursor->toDateString())
                        ->exists();

                    if (!$exists) {
                        $this->createTaskFromPlan($plan, $cursor);
                        $created++;
                    }

                    $cursor = $this->computeNextDueDate($cursor, $plan->frequency, (int) ($plan->interval_value ?: 1));
                    $planTouched = true;
                }

                if ($planTouched) {
                    $plan->next_due_date = $cursor->toDateString();
                    $plan->save();
                    $updatedPlans++;
                }
            } catch (Throwable $e) {
                Log::error('Preventive generation failed for plan', [
                    'plan_id' => $plan->id,
                    'message' => $e->getMessage(),
                ]);
            }
        }

        return ['tasks_created' => $created, 'plans_updated' => $updatedPlans];
    }

    public function sendUpcomingReminders(?Carbon $now = null): int
    {
        if (!$this->automationColumnsReady()) {
            return 0;
        }

        $now = $now ?? now();
        $deadline = $now->copy()->addDay()->endOfDay();
        $sent = 0;

        $tasks = MaintenanceTask::query()
            ->with('preventivePlan')
            ->where('type', 'preventive')
            ->whereIn('status', ['pending', 'in_progress'])
            ->whereNull('reminder_sent_at')
            ->whereNotNull('scheduled_for')
            ->whereDate('scheduled_for', '<=', $deadline->toDateString())
            ->get();

        foreach ($tasks as $task) {
            try {
                $targets = $this->resolveReminderTargets($task);
                if ($targets->isEmpty()) {
                    continue;
                }

                Notification::send(
                    $targets,
                    new MaintenanceAlertNotification(
                        __('gmao.msg.preventive_reminder'),
                        ($task->title ?? 'Preventive task') . ' - ' . optional($task->scheduled_for)->format('Y-m-d')
                    )
                );

                $task->reminder_sent_at = now();
                $task->save();
                $sent += $targets->count();
            } catch (Throwable $e) {
                Log::error('Preventive reminder failed for task', [
                    'task_id' => $task->id,
                    'message' => $e->getMessage(),
                ]);
            }
        }

        return $sent;
    }

    private function createTaskFromPlan(PreventivePlan $plan, Carbon $dueDate): void
    {
        DB::transaction(function () use ($plan, $dueDate): void {
            $title = 'PM | ' . $plan->title . ' | ' . ($plan->asset_reference ?: '#NA');
            $notes = trim(implode("\n\n", array_filter([
                $plan->checklist ? "Checklist:\n{$plan->checklist}" : null,
                $plan->procedure_steps ? "Procedure:\n{$plan->procedure_steps}" : null,
                $plan->safety_notes ? "Safety:\n{$plan->safety_notes}" : null,
                $plan->spare_parts_list ? "Spare Parts:\n{$plan->spare_parts_list}" : null,
            ])));

            MaintenanceTask::create([
                'preventive_plan_id' => $plan->id,
                'technician_id' => $plan->responsible_id,
                'sector' => $plan->sector,
                'title' => $title,
                'type' => 'preventive',
                'status' => 'pending',
                'scheduled_for' => $dueDate->toDateString(),
                'generated_for_date' => $dueDate->toDateString(),
                'estimated_hours' => !empty($plan->estimated_duration_minutes)
                    ? round(((int) $plan->estimated_duration_minutes) / 60, 2)
                    : null,
                'notes' => $notes,
            ]);
        });
    }

    private function computeNextDueDate(Carbon $baseDate, string $frequency, int $interval): Carbon
    {
        $interval = max(1, $interval);
        $next = $baseDate->copy();

        return match ($frequency) {
            'daily' => $next->addDays($interval),
            'weekly' => $next->addWeeks($interval),
            'monthly' => $next->addMonths($interval),
            'quarterly' => $next->addMonths(3 * $interval),
            'yearly' => $next->addYears($interval),
            default => $next->addMonths(1),
        };
    }

    private function resolveReminderTargets(MaintenanceTask $task)
    {
        if (!empty($task->technician_id)) {
            return User::query()->whereKey($task->technician_id)->get();
        }

        return User::query()
            ->whereIn('role', ['super_admin', 'manager', 'technician'])
            ->when(!empty($task->sector), fn ($q) => $q->where('sector', $task->sector))
            ->get();
    }

    private function automationColumnsReady(): bool
    {
        return Schema::hasColumn('maintenance_tasks', 'preventive_plan_id')
            && Schema::hasColumn('maintenance_tasks', 'generated_for_date')
            && Schema::hasColumn('maintenance_tasks', 'reminder_sent_at');
    }
}
