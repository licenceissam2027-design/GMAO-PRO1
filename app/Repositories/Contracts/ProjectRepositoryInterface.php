<?php

namespace App\Repositories\Contracts;

use App\Models\Project;
use App\Models\ProjectPhase;

interface ProjectRepositoryInterface
{
    public function create(array $data): Project;

    public function update(Project $project, array $data): bool;

    public function delete(Project $project): ?bool;

    public function createPhase(Project $project, array $data): ProjectPhase;

    public function updatePhase(ProjectPhase $phase, array $data): bool;

    public function deletePhase(ProjectPhase $phase): ?bool;
}
