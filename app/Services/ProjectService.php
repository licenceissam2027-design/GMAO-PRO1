<?php

namespace App\Services;

use App\Models\Project;
use App\Models\ProjectPhase;
use App\Repositories\Contracts\ProjectRepositoryInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ProjectService
{
    public function __construct(private readonly ProjectRepositoryInterface $projectRepository)
    {
    }

    public function create(array $data): Project
    {
        return DB::transaction(function () use ($data): Project {
            $phases = $data['phases'] ?? [];
            unset($data['phases']);

            $project = $this->projectRepository->create($data);
            $phasesEnabled = Schema::hasTable('project_phases');

            if ($phasesEnabled) {
                foreach ($phases as $index => $phase) {
                    if (empty(trim((string) ($phase['title'] ?? '')))) {
                        continue;
                    }

                    $this->projectRepository->createPhase($project, [
                        ...$phase,
                        'execution_mode' => $phase['execution_mode'] ?? 'sequential',
                        'phase_order' => (int) ($phase['phase_order'] ?? ($index + 1)),
                        'status' => $phase['status'] ?? 'planned',
                        'progress' => (int) ($phase['progress'] ?? 0),
                    ]);
                }
            }

            if ($phasesEnabled) {
                $this->refreshProjectProgress($project);
            }

            return $project;
        });
    }

    public function update(Project $project, array $data): bool
    {
        return $this->projectRepository->update($project, $data);
    }

    public function delete(Project $project): ?bool
    {
        return $this->projectRepository->delete($project);
    }

    public function createPhase(Project $project, array $data): ProjectPhase
    {
        $phase = $this->projectRepository->createPhase($project, $data);
        $this->refreshProjectProgress($project);

        return $phase;
    }

    public function updatePhase(ProjectPhase $phase, array $data): bool
    {
        $result = $this->projectRepository->updatePhase($phase, $data);
        $this->refreshProjectProgress($phase->project);

        return $result;
    }

    public function deletePhase(ProjectPhase $phase): ?bool
    {
        $project = $phase->project;
        $result = $this->projectRepository->deletePhase($phase);
        if ($project) {
            $this->refreshProjectProgress($project);
        }

        return $result;
    }

    private function refreshProjectProgress(Project $project): void
    {
        if (!Schema::hasTable('project_phases')) {
            return;
        }

        $phases = $project->phases()->get(['progress', 'status']);
        if ($phases->isEmpty()) {
            return;
        }

        $avgProgress = (int) round($phases->avg('progress'));
        $status = $project->status;
        if ($phases->every(fn (ProjectPhase $p): bool => $p->status === 'completed')) {
            $status = 'completed';
        } elseif ($phases->contains(fn (ProjectPhase $p): bool => $p->status === 'in_progress')) {
            $status = 'in_progress';
        }

        $project->update([
            'progress' => max(0, min(100, $avgProgress)),
            'status' => $status,
        ]);
    }
}
