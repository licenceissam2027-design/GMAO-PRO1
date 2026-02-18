<?php

namespace App\Repositories\Eloquent;

use App\Models\Project;
use App\Models\ProjectPhase;
use App\Repositories\Contracts\ProjectRepositoryInterface;

class ProjectRepository implements ProjectRepositoryInterface
{
    public function create(array $data): Project
    {
        return Project::create($data);
    }

    public function update(Project $project, array $data): bool
    {
        return $project->update($data);
    }

    public function delete(Project $project): ?bool
    {
        return $project->delete();
    }

    public function createPhase(Project $project, array $data): ProjectPhase
    {
        return $project->phases()->create($data);
    }

    public function updatePhase(ProjectPhase $phase, array $data): bool
    {
        return $phase->update($data);
    }

    public function deletePhase(ProjectPhase $phase): ?bool
    {
        return $phase->delete();
    }
}
