<?php

namespace App\Http\Controllers;

use App\Http\Requests\Projects\StoreProjectRequest;
use App\Http\Requests\Projects\StoreProjectPhaseRequest;
use App\Http\Requests\Projects\UpdateProjectRequest;
use App\Http\Requests\Projects\UpdateProjectPhaseRequest;
use App\Models\Project;
use App\Models\ProjectPhase;
use App\Models\User;
use App\Support\GmaoOptions;
use App\Services\ProjectService;
use Illuminate\Database\QueryException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProjectController extends Controller
{
    public function __construct(private readonly ProjectService $projectService)
    {
    }

    public function index(Request $request): View
    {
        $this->authorize('viewAny', Project::class);
        $phasesEnabled = Schema::hasTable('project_phases');

        $buildQuery = function (bool $withPhases) use ($request) {
            $query = Project::query()
                ->with('manager')
                ->when($withPhases, fn ($q) => $q->with(['phases.responsible', 'phases.dependsOn']))
                ->latest();

            if (!$request->user()?->isRole('super_admin') && !empty($request->user()?->sector)) {
                $query->where('sector', $request->user()->sector);
            }

            if ($request->filled('sector')) {
                $query->where('sector', $request->query('sector'));
            }

            return $query;
        };

        try {
            $projects = $buildQuery($phasesEnabled)->paginate(12)->withQueryString();
        } catch (QueryException $e) {
            if (!$this->isMissingProjectPhasesTable($e)) {
                throw $e;
            }

            $phasesEnabled = false;
            $projects = $buildQuery(false)->paginate(12)->withQueryString();
        }

        if (!$phasesEnabled) {
            $projects->getCollection()->each(function (Project $project): void {
                $project->setRelation('phases', new Collection());
            });
        }

        return view('projects.index', [
            'projects' => $projects,
            'managers' => User::whereIn('role', ['super_admin', 'manager'])->get(),
            'phaseOwners' => User::whereIn('role', ['super_admin', 'manager', 'technician'])
                ->where('is_active', true)
                ->when(!$request->user()?->isRole('super_admin') && !empty($request->user()?->sector), fn ($q) => $q->where('sector', $request->user()->sector))
                ->orderBy('name')
                ->get(),
            'sectors' => GmaoOptions::SECTORS,
            'selectedSector' => (string) $request->query('sector', ''),
            'phaseModes' => GmaoOptions::PROJECT_PHASE_MODES,
            'phaseStatuses' => GmaoOptions::PROJECT_PHASE_STATUSES,
            'phasesEnabled' => $phasesEnabled,
        ]);
    }

    public function create(): View
    {
        $this->authorize('create', Project::class);
        $phasesEnabled = Schema::hasTable('project_phases');

        return view('projects.create', [
            'managers' => User::whereIn('role', ['super_admin', 'manager'])->get(),
            'phaseOwners' => User::whereIn('role', ['super_admin', 'manager', 'technician'])
                ->where('is_active', true)
                ->when(!auth()->user()?->isRole('super_admin') && !empty(auth()->user()?->sector), fn ($q) => $q->where('sector', auth()->user()->sector))
                ->orderBy('name')
                ->get(),
            'sectors' => GmaoOptions::SECTORS,
            'phaseModes' => GmaoOptions::PROJECT_PHASE_MODES,
            'phaseStatuses' => GmaoOptions::PROJECT_PHASE_STATUSES,
            'phasesEnabled' => $phasesEnabled,
        ]);
    }

    public function store(StoreProjectRequest $request): RedirectResponse
    {
        $this->authorize('create', Project::class);

        $this->projectService->create($request->validated());

        return back()->with('success', __('gmao.msg.project_created'));
    }

    public function update(UpdateProjectRequest $request, Project $project): RedirectResponse
    {
        $this->authorize('update', $project);

        $this->projectService->update($project, $request->validated());

        return back()->with('success', __('gmao.msg.project_updated'));
    }

    public function destroy(Project $project): RedirectResponse
    {
        $this->authorize('delete', $project);

        $this->projectService->delete($project);

        return back()->with('success', __('gmao.msg.project_deleted'));
    }

    public function storePhase(StoreProjectPhaseRequest $request, Project $project): RedirectResponse
    {
        $this->authorize('update', $project);
        if (!Schema::hasTable('project_phases')) {
            return back()->withErrors(['general' => __('gmao.msg.project_phases_unavailable')]);
        }

        $this->projectService->createPhase($project, $request->validated());

        return back()->with('success', __('gmao.msg.project_phase_created'));
    }

    public function updatePhase(UpdateProjectPhaseRequest $request, ProjectPhase $projectPhase): RedirectResponse
    {
        $this->authorize('update', $projectPhase->project);
        if (!Schema::hasTable('project_phases')) {
            return back()->withErrors(['general' => __('gmao.msg.project_phases_unavailable')]);
        }

        $this->projectService->updatePhase($projectPhase, $request->validated());

        return back()->with('success', __('gmao.msg.project_phase_updated'));
    }

    public function destroyPhase(ProjectPhase $projectPhase): RedirectResponse
    {
        $this->authorize('update', $projectPhase->project);
        if (!Schema::hasTable('project_phases')) {
            return back()->withErrors(['general' => __('gmao.msg.project_phases_unavailable')]);
        }

        $this->projectService->deletePhase($projectPhase);

        return back()->with('success', __('gmao.msg.project_phase_deleted'));
    }

    private function isMissingProjectPhasesTable(QueryException $exception): bool
    {
        $message = strtolower($exception->getMessage());

        return str_contains($message, 'project_phases') && (
            str_contains($message, 'base table or view not found') ||
            str_contains($message, 'unknown table')
        );
    }
}
