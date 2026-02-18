<?php

namespace App\Policies;

use App\Models\ExpertMission;
use App\Models\User;

class ExpertMissionPolicy
{
    public function viewAny(User $user): bool
    {
        return $this->active($user) && $user->isRole('super_admin', 'manager');
    }

    public function create(User $user): bool
    {
        return $this->viewAny($user);
    }

    public function update(User $user, ExpertMission $expertMission): bool
    {
        return $this->viewAny($user);
    }

    public function delete(User $user, ExpertMission $expertMission): bool
    {
        return $this->viewAny($user);
    }

    private function active(User $user): bool
    {
        return (bool) $user->is_active;
    }
}
