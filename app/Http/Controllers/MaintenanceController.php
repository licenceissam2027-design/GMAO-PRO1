<?php

namespace App\Http\Controllers;

use App\Http\Requests\Maintenance\StoreMaintenanceRequest;
use App\Http\Requests\Maintenance\StoreMaintenanceTaskRequest;
use App\Http\Requests\Maintenance\StorePreventivePlanRequest;
use App\Http\Requests\Maintenance\UpdateMaintenanceRequest;
use App\Http\Requests\Maintenance\UpdateMaintenanceRequestStatusRequest;
use App\Http\Requests\Maintenance\UpdateMaintenanceTaskRequest;
use App\Http\Requests\Maintenance\UpdatePreventiveExecutionRequest;
use App\Http\Requests\Maintenance\UpdatePreventivePlanRequest;
use App\Models\IndustrialMachine;
use App\Models\LogisticAsset;
use App\Models\MaintenanceRequest;
use App\Models\MaintenanceTask;
use App\Models\PreventivePlan;
use App\Models\ReportFile;
use App\Models\TechnicalAsset;
use App\Models\User;
use App\Support\GmaoOptions;
use App\Services\MaintenanceService;
use App\Services\PreventiveTaskAutomationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MaintenanceController extends Controller
{
    public function __construct(private readonly MaintenanceService $maintenanceService)
    {
    }

    public function requests(Request $request): View
    {
        $this->authorize('viewAny', MaintenanceRequest::class);

        $requestsQuery = MaintenanceRequest::query()->with(['machine', 'requester', 'assignee'])->latest();
        $user = $request->user();

        if ($user->isRole('employee')) {
            $requestsQuery->where('requester_id', $user->id);
        } elseif (!$user->isRole('super_admin') && !empty($user->sector)) {
            $requestsQuery->where(function ($query) use ($user): void {
                $query->where('sector', $user->sector)
                    ->orWhere('assigned_to', $user->id);
            });
        }

        if ($request->filled('sector')) {
            $requestsQuery->where('sector', $request->query('sector'));
        }

        return view('maintenance.requests', [
            'requests' => $requestsQuery->paginate(15)->withQueryString(),
            'technicians' => User::where('role', 'technician')
                ->when(!empty($user?->sector), fn ($q) => $q->where('sector', $user->sector))
                ->get(),
            'machines' => IndustrialMachine::query()
                ->when(!empty($user?->sector), fn ($q) => $q->where('sector', $user->sector))
                ->orderBy('name')
                ->get(),
            'maintenanceDomains' => GmaoOptions::MAINTENANCE_DOMAINS,
            'failureModes' => GmaoOptions::FAILURE_MODES,
            'failureModeLabels' => collect(GmaoOptions::allFailureModes())
                ->mapWithKeys(fn (string $mode): array => [$mode => GmaoOptions::failureLabel($mode, app()->getLocale())])
                ->all(),
            'domainLabels' => GmaoOptions::domainLabels(app()->getLocale()),
            'sectors' => GmaoOptions::SECTORS,
            'selectedSector' => (string) $request->query('sector', ''),
        ]);
    }

    public function createRequest(Request $request): View
    {
        $this->authorize('create', MaintenanceRequest::class);
        $user = $request->user();

        return view('maintenance.requests-create', [
            'technicians' => User::where('role', 'technician')
                ->when(!empty($user?->sector), fn ($q) => $q->where('sector', $user->sector))
                ->get(),
            'machines' => IndustrialMachine::query()
                ->when(!empty($user?->sector), fn ($q) => $q->where('sector', $user->sector))
                ->orderBy('name')
                ->get(),
            'technicalAssets' => TechnicalAsset::query()
                ->when(!empty($user?->sector), fn ($q) => $q->where('sector', $user->sector))
                ->orderBy('name')
                ->get(),
            'logisticAssets' => LogisticAsset::query()
                ->when(!empty($user?->sector), fn ($q) => $q->where('sector', $user->sector))
                ->orderBy('name')
                ->get(),
            'maintenanceDomains' => GmaoOptions::MAINTENANCE_DOMAINS,
            'failureModes' => GmaoOptions::FAILURE_MODES,
            'failureModeLabels' => collect(GmaoOptions::allFailureModes())
                ->mapWithKeys(fn (string $mode): array => [$mode => GmaoOptions::failureLabel($mode, app()->getLocale())])
                ->all(),
            'domainLabels' => GmaoOptions::domainLabels(app()->getLocale()),
            'sectors' => GmaoOptions::SECTORS,
        ]);
    }

    public function showRequest(MaintenanceRequest $maintenanceRequest): View
    {
        $this->authorize('view', $maintenanceRequest);

        $maintenanceRequest->load(['machine', 'technicalAsset', 'logisticAsset', 'requester', 'assignee']);

        $similarRequests = MaintenanceRequest::query()
            ->whereKeyNot($maintenanceRequest->id)
            ->where('maintenance_domain', $maintenanceRequest->maintenance_domain)
            ->where('failure_mode', $maintenanceRequest->failure_mode)
            ->when(
                !empty($maintenanceRequest->industrial_machine_id),
                fn ($query) => $query->where('industrial_machine_id', $maintenanceRequest->industrial_machine_id),
                fn ($query) => $query->where('asset_reference', $maintenanceRequest->asset_reference)
            )
            ->latest()
            ->take(8)
            ->get();

        return view('maintenance.request-show', [
            'requestItem' => $maintenanceRequest,
            'similarRequests' => $similarRequests,
            'linkedReports' => ReportFile::query()
                ->where('context_type', 'maintenance_request')
                ->where('context_id', $maintenanceRequest->id)
                ->latest()
                ->take(10)
                ->get(),
            'domainLabels' => GmaoOptions::domainLabels(app()->getLocale()),
            'failureModeLabels' => collect(GmaoOptions::allFailureModes())
                ->mapWithKeys(fn (string $mode): array => [$mode => GmaoOptions::failureLabel($mode, app()->getLocale())])
                ->all(),
        ]);
    }

    public function storeRequest(StoreMaintenanceRequest $request): RedirectResponse
    {
        $this->authorize('create', MaintenanceRequest::class);

        $this->maintenanceService->createRequest($request->validated(), $request->user()->id);

        return back()->with('success', __('gmao.msg.request_created'));
    }

    public function updateRequest(UpdateMaintenanceRequest $request, MaintenanceRequest $maintenanceRequest): RedirectResponse
    {
        $this->authorize('update', $maintenanceRequest);

        $this->maintenanceService->updateRequest($maintenanceRequest, $request->validated());

        return back()->with('success', __('gmao.msg.request_updated'));
    }

    public function destroyRequest(MaintenanceRequest $maintenanceRequest): RedirectResponse
    {
        $this->authorize('delete', $maintenanceRequest);

        $this->maintenanceService->deleteRequest($maintenanceRequest);

        return back()->with('success', __('gmao.msg.request_deleted'));
    }

    public function updateRequestStatus(UpdateMaintenanceRequestStatusRequest $request, MaintenanceRequest $maintenanceRequest): RedirectResponse
    {
        $this->authorize('updateStatus', $maintenanceRequest);

        $this->maintenanceService->updateRequestStatus($maintenanceRequest, $request->validated('status'));

        return back()->with('success', __('gmao.msg.status_updated'));
    }

    public function plans(Request $request): View
    {
        $this->authorize('viewAny', PreventivePlan::class);

        $plansQuery = PreventivePlan::query()->latest();
        $user = $request->user();

        if (!$user->isRole('super_admin') && !empty($user->sector)) {
            $plansQuery->where('sector', $user->sector);
        }

        if ($request->filled('sector')) {
            $plansQuery->where('sector', $request->query('sector'));
        }

        return view('maintenance.plans', [
            'plans' => $plansQuery->paginate(15)->withQueryString(),
            'users' => User::whereIn('role', ['super_admin', 'manager', 'technician'])->get(),
            'machines' => IndustrialMachine::query()
                ->when(!empty($user?->sector), fn ($q) => $q->where('sector', $user->sector))
                ->orderBy('name')
                ->get(),
            'technicalAssets' => TechnicalAsset::query()
                ->when(!empty($user?->sector), fn ($q) => $q->where('sector', $user->sector))
                ->orderBy('name')
                ->get(),
            'logisticAssets' => LogisticAsset::query()
                ->when(!empty($user?->sector), fn ($q) => $q->where('sector', $user->sector))
                ->orderBy('name')
                ->get(),
            'maintenanceDomains' => GmaoOptions::MAINTENANCE_DOMAINS,
            'failureModes' => GmaoOptions::FAILURE_MODES,
            'failureModeLabels' => collect(GmaoOptions::allFailureModes())
                ->mapWithKeys(fn (string $mode): array => [$mode => GmaoOptions::failureLabel($mode, app()->getLocale())])
                ->all(),
            'domainLabels' => GmaoOptions::domainLabels(app()->getLocale()),
            'sectors' => GmaoOptions::SECTORS,
            'selectedSector' => (string) $request->query('sector', ''),
        ]);
    }

    public function createPlan(Request $request): View
    {
        $this->authorize('create', PreventivePlan::class);
        $user = $request->user();

        return view('maintenance.plans-create', [
            'users' => User::whereIn('role', ['super_admin', 'manager', 'technician'])->get(),
            'machines' => IndustrialMachine::query()
                ->when(!empty($user?->sector), fn ($q) => $q->where('sector', $user->sector))
                ->orderBy('name')
                ->get(),
            'technicalAssets' => TechnicalAsset::query()
                ->when(!empty($user?->sector), fn ($q) => $q->where('sector', $user->sector))
                ->orderBy('name')
                ->get(),
            'logisticAssets' => LogisticAsset::query()
                ->when(!empty($user?->sector), fn ($q) => $q->where('sector', $user->sector))
                ->orderBy('name')
                ->get(),
            'maintenanceDomains' => GmaoOptions::MAINTENANCE_DOMAINS,
            'failureModes' => GmaoOptions::FAILURE_MODES,
            'failureModeLabels' => collect(GmaoOptions::allFailureModes())
                ->mapWithKeys(fn (string $mode): array => [$mode => GmaoOptions::failureLabel($mode, app()->getLocale())])
                ->all(),
            'domainLabels' => GmaoOptions::domainLabels(app()->getLocale()),
            'sectors' => GmaoOptions::SECTORS,
        ]);
    }

    public function storePlan(StorePreventivePlanRequest $request): RedirectResponse
    {
        $this->authorize('create', PreventivePlan::class);

        $this->maintenanceService->createPlan($request->validated());

        return back()->with('success', __('gmao.msg.plan_created'));
    }

    public function updatePlan(UpdatePreventivePlanRequest $request, PreventivePlan $preventivePlan): RedirectResponse
    {
        $this->authorize('update', $preventivePlan);

        $this->maintenanceService->updatePlan($preventivePlan, $request->validated());

        return back()->with('success', __('gmao.msg.plan_updated'));
    }

    public function destroyPlan(PreventivePlan $preventivePlan): RedirectResponse
    {
        $this->authorize('delete', $preventivePlan);

        $this->maintenanceService->deletePlan($preventivePlan);

        return back()->with('success', __('gmao.msg.plan_deleted'));
    }

    public function tasks(Request $request): View
    {
        $this->authorize('viewAny', MaintenanceTask::class);

        $tasksQuery = MaintenanceTask::query()->latest();
        $user = $request->user();

        if (!$user->isRole('super_admin') && !empty($user->sector)) {
            $tasksQuery->where(function ($query) use ($user): void {
                $query->where('sector', $user->sector)
                    ->orWhere('technician_id', $user->id);
            });
        }

        if ($request->filled('sector')) {
            $tasksQuery->where('sector', $request->query('sector'));
        }

        return view('maintenance.tasks', [
            'tasks' => $tasksQuery->paginate(15)->withQueryString(),
            'requests' => MaintenanceRequest::latest()
                ->when(!empty($user?->sector), fn ($q) => $q->where('sector', $user->sector))
                ->take(60)
                ->get(),
            'technicians' => User::where('role', 'technician')
                ->when(!empty($user?->sector), fn ($q) => $q->where('sector', $user->sector))
                ->get(),
            'sectors' => GmaoOptions::SECTORS,
            'selectedSector' => (string) $request->query('sector', ''),
        ]);
    }

    public function rounds(Request $request, PreventiveTaskAutomationService $automationService): View
    {
        $this->authorize('viewAny', MaintenanceTask::class);
        $automationService->generateDueTasks();

        $user = $request->user();
        $roundQuery = MaintenanceTask::query()
            ->with('preventivePlan')
            ->where('type', 'preventive')
            ->whereDate('scheduled_for', '<=', now()->toDateString())
            ->whereIn('status', ['pending', 'in_progress', 'completed'])
            ->orderBy('scheduled_for')
            ->orderBy('id');

        if ($user->isRole('technician')) {
            $roundQuery->where(function ($query) use ($user): void {
                $query->where('technician_id', $user->id)
                    ->orWhere(function ($q) use ($user): void {
                        $q->whereNull('technician_id');
                        if (!empty($user->sector)) {
                            $q->where('sector', $user->sector);
                        }
                    });
            });
        } elseif (!$user->isRole('super_admin') && !empty($user->sector)) {
            $roundQuery->where('sector', $user->sector);
        }

        $total = (clone $roundQuery)->count();
        $completed = (clone $roundQuery)->where('status', 'completed')->count();
        $roundTasks = $roundQuery->paginate(20)->withQueryString();

        return view('maintenance.rounds', [
            'roundTasks' => $roundTasks,
            'totalRounds' => $total,
            'completedRounds' => $completed,
            'pendingRounds' => max(0, $total - $completed),
            'domainLabels' => GmaoOptions::domainLabels(app()->getLocale()),
            'failureModeLabels' => collect(GmaoOptions::allFailureModes())
                ->mapWithKeys(fn (string $mode): array => [$mode => GmaoOptions::failureLabel($mode, app()->getLocale())])
                ->all(),
        ]);
    }

    public function createTask(Request $request): View
    {
        $this->authorize('create', MaintenanceTask::class);
        $user = $request->user();

        return view('maintenance.tasks-create', [
            'requests' => MaintenanceRequest::latest()
                ->when(!empty($user?->sector), fn ($q) => $q->where('sector', $user->sector))
                ->take(60)
                ->get(),
            'technicians' => User::where('role', 'technician')
                ->when(!empty($user?->sector), fn ($q) => $q->where('sector', $user->sector))
                ->get(),
            'sectors' => GmaoOptions::SECTORS,
        ]);
    }

    public function storeTask(StoreMaintenanceTaskRequest $request): RedirectResponse
    {
        $this->authorize('create', MaintenanceTask::class);

        $this->maintenanceService->createTask($request->validated());

        return back()->with('success', __('gmao.msg.task_created'));
    }

    public function updateTask(UpdateMaintenanceTaskRequest $request, MaintenanceTask $maintenanceTask): RedirectResponse
    {
        $this->authorize('update', $maintenanceTask);

        $this->maintenanceService->updateTask($maintenanceTask, $request->validated());

        return back()->with('success', __('gmao.msg.task_updated'));
    }

    public function updateRoundExecution(UpdatePreventiveExecutionRequest $request, MaintenanceTask $maintenanceTask): RedirectResponse
    {
        $this->authorize('update', $maintenanceTask);
        if ($maintenanceTask->type !== 'preventive') {
            return back()->withErrors(['general' => __('gmao.msg.forbidden')]);
        }

        $openedRequest = $this->maintenanceService->recordPreventiveExecution(
            $maintenanceTask,
            $request->validated(),
            (int) $request->user()->id
        );

        if ($openedRequest) {
            return back()->with('success', __('gmao.msg.request_created'));
        }

        return back()->with('success', __('gmao.msg.task_updated'));
    }

    public function destroyTask(MaintenanceTask $maintenanceTask): RedirectResponse
    {
        $this->authorize('delete', $maintenanceTask);

        $this->maintenanceService->deleteTask($maintenanceTask);

        return back()->with('success', __('gmao.msg.task_deleted'));
    }
}
