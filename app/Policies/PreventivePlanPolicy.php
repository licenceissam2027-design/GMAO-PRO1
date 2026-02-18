<?php

namespace App\Policies;

use App\Models\PreventivePlan;
use App\Models\User;

class PreventivePlanPolicy
{
    public function viewAny(User $user): bool
    {
        return $this->active($user) && $user->isRole('super_admin', 'manager', 'technician');
    }

    public function create(User $user): bool
    {
        return $this->viewAny($user);
    }

    public function update(User $user, PreventivePlan $preventivePlan): bool
    {
        if (!$this->viewAny($user)) {
            return false;
        }

        return $this->sameSector($user, $preventivePlan->sector);
    }

    public function delete(User $user, PreventivePlan $preventivePlan): bool
    {
        if (!$this->active($user)) {
            return false;
        }

        if (!$user->isRole('super_admin', 'manager')) {
            return false;
        }

        return $this->sameSector($user, $preventivePlan->sector);
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

    private function active(User $user): bool
    {
        return (bool) $user->is_active;
    }
}
