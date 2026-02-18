<?php

namespace App\Policies;

use App\Models\Project;
use App\Models\User;

class ProjectPolicy
{
    public function viewAny(User $user): bool
    {
        return $this->active($user);
    }

    public function create(User $user): bool
    {
        return $this->active($user) && $user->isRole('super_admin', 'manager');
    }

    public function update(User $user, Project $project): bool
    {
        if (!$this->create($user)) {
            return false;
        }

        return $this->sameSector($user, $project->sector);
    }

    public function delete(User $user, Project $project): bool
    {
        return $user->isRole('super_admin') && $this->sameSector($user, $project->sector);
    }

    private function active(User $user): bool
    {
        return (bool) $user->is_active;
    }

    private function sameSector(User $user, ?string $recordSector): bool
    {
        if ($user->isRole('super_admin')) {
            return true;
        }

        if (empty($user->sector)) {
            return false;
        }

        if (empty($recordSector)) {
            return true;
        }

        return $user->sector === $recordSector;
    }
}
