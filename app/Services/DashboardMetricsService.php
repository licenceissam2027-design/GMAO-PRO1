<?php

namespace App\Services;

use App\Models\IndustrialMachine;
use App\Models\LogisticAsset;
use App\Models\MaintenanceRequest;
use App\Models\MaintenanceTask;
use App\Models\Project;
use App\Models\SparePart;
use App\Models\TechnicalAsset;
use App\Models\User;
use App\Support\GmaoOptions;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;

class DashboardMetricsService
{
    public function build(User $user): array
    {
        $projects = $this->scopeByUser(Project::query(), $user);
        $requests = $this->scopeByUser(MaintenanceRequest::query(), $user);
        $tasks = $this->scopeByUser(MaintenanceTask::query(), $user);
        $industrialMachines = $this->scopeByUser(IndustrialMachine::query(), $user);
        $technicalAssets = $this->scopeByUser(TechnicalAsset::query(), $user);
        $logisticAssets = $this->scopeByUser(LogisticAsset::query(), $user);
        $spareParts = $this->scopeByUser(SparePart::query(), $user);

        $projectStatus = [
            __('gmao.enum.status.planned') => (clone $projects)->where('status', 'planned')->count(),
            __('gmao.enum.status.in_progress') => (clone $projects)->where('status', 'in_progress')->count(),
            __('gmao.enum.status.completed') => (clone $projects)->where('status', 'completed')->count(),
            __('gmao.enum.status.delayed') => (clone $projects)->where('status', 'delayed')->count(),
        ];

        $requestStatus = [
            __('gmao.enum.status.pending') => (clone $requests)->where('status', 'pending')->count(),
            __('gmao.enum.status.in_progress') => (clone $requests)->where('status', 'in_progress')->count(),
            __('gmao.enum.status.completed') => (clone $requests)->where('status', 'completed')->count(),
            __('gmao.enum.status.stopped') => (clone $requests)->where('status', 'stopped')->count(),
        ];

        $tasksByType = [
            __('gmao.enum.type.corrective') => (clone $tasks)->where('type', 'corrective')->count(),
            __('gmao.enum.type.preventive') => (clone $tasks)->where('type', 'preventive')->count(),
            __('gmao.enum.type.predictive') => (clone $tasks)->where('type', 'predictive')->count(),
        ];

        $kpi = $this->buildKpiCards(
            projects: $projects,
            requests: $requests,
            tasks: $tasks,
            industrialMachines: $industrialMachines,
            technicalAssets: $technicalAssets,
            logisticAssets: $logisticAssets,
            spareParts: $spareParts
        );

        return [
            'projectStatus' => $projectStatus,
            'requestStatus' => $requestStatus,
            'tasksByType' => $tasksByType,
            'kpiCards' => $kpi,
            'sectorBacklog' => $this->buildSectorBacklog($requests),
        ];
    }

    private function buildKpiCards(
        Builder $projects,
        Builder $requests,
        Builder $tasks,
        Builder $industrialMachines,
        Builder $technicalAssets,
        Builder $logisticAssets,
        Builder $spareParts
    ): array {
        $requestsCount = (clone $requests)->count();
        $recurrentCount = (clone $requests)->where('is_recurrent', true)->count();
        $openRequests = (clone $requests)->whereIn('status', ['pending', 'in_progress'])->count();
        $criticalRequests = (clone $requests)->where('severity', 'critical')->count();
        $lowStockCount = (clone $spareParts)->whereColumn('current_stock', '<=', 'minimum_stock')->count();
        $completedTasksThisMonth = (clone $tasks)
            ->where('status', 'completed')
            ->whereBetween('completed_at', [now()->startOfMonth(), now()->endOfMonth()])
            ->count();

        $totalMachines = (clone $industrialMachines)->count();
        $runningMachines = (clone $industrialMachines)->where('status', 'running')->count();
        $availabilityRate = $totalMachines > 0 ? round(($runningMachines / $totalMachines) * 100, 1) : 0.0;

        $mttrHours = $this->calculateMttrHours($requests);
        $mtbfHours = $this->calculateMtbfHours($requests);
        $recurrentRate = $requestsCount > 0 ? round(($recurrentCount / $requestsCount) * 100, 1) : 0.0;

        $totalAssets = $totalMachines + (clone $technicalAssets)->count() + (clone $logisticAssets)->count();

        return [
            'open_requests' => $openRequests,
            'critical_requests' => $criticalRequests,
            'completed_tasks_month' => $completedTasksThisMonth,
            'low_stock_parts' => $lowStockCount,
            'asset_pool' => $totalAssets,
            'availability_rate' => $availabilityRate,
            'mttr_hours' => $mttrHours,
            'mtbf_hours' => $mtbfHours,
            'recurrent_rate' => $recurrentRate,
        ];
    }

    private function buildSectorBacklog(Builder $requests): array
    {
        $pendingBySector = [];
        foreach (GmaoOptions::SECTORS as $sector) {
            $pendingBySector[__('gmao.enum.sector.' . $sector)] = (clone $requests)
                ->where('sector', $sector)
                ->whereIn('status', ['pending', 'in_progress', 'stopped'])
                ->count();
        }

        $pendingBySector[__('gmao.dashboard.unassigned_sector')] = (clone $requests)
            ->where(function (Builder $query): void {
                $query->whereNull('sector')->orWhere('sector', '');
            })
            ->whereIn('status', ['pending', 'in_progress', 'stopped'])
            ->count();

        return $pendingBySector;
    }

    private function calculateMttrHours(Builder $requests): float
    {
        $durations = (clone $requests)
            ->where('status', 'completed')
            ->whereNotNull('occurrence_at')
            ->whereNotNull('resolved_at')
            ->get(['occurrence_at', 'resolved_at'])
            ->map(function (MaintenanceRequest $request): float {
                $start = $request->occurrence_at instanceof Carbon ? $request->occurrence_at : Carbon::parse($request->occurrence_at);
                $end = $request->resolved_at instanceof Carbon ? $request->resolved_at : Carbon::parse($request->resolved_at);

                return max(0, $start->diffInMinutes($end)) / 60;
            })
            ->filter(fn (float $hours): bool => $hours > 0)
            ->values();

        if ($durations->isEmpty()) {
            return 0.0;
        }

        return round($durations->avg(), 2);
    }

    private function calculateMtbfHours(Builder $requests): float
    {
        $items = (clone $requests)
            ->whereNotNull('industrial_machine_id')
            ->whereNotNull('occurrence_at')
            ->orderBy('industrial_machine_id')
            ->orderBy('occurrence_at')
            ->get(['industrial_machine_id', 'occurrence_at']);

        $intervals = [];
        $lastByMachine = [];

        foreach ($items as $item) {
            $machineId = (int) $item->industrial_machine_id;
            $current = $item->occurrence_at instanceof Carbon ? $item->occurrence_at : Carbon::parse($item->occurrence_at);
            if (isset($lastByMachine[$machineId])) {
                $hours = $lastByMachine[$machineId]->diffInMinutes($current) / 60;
                if ($hours > 0) {
                    $intervals[] = $hours;
                }
            }
            $lastByMachine[$machineId] = $current;
        }

        if (empty($intervals)) {
            return 0.0;
        }

        return round(array_sum($intervals) / count($intervals), 2);
    }

    private function scopeByUser(Builder $query, User $user): Builder
    {
        if ($user->isRole('super_admin') || empty($user->sector)) {
            return $query;
        }

        return $query->where('sector', $user->sector);
    }
}
