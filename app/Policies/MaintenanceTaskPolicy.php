<?php

namespace App\Policies;

use App\Models\MaintenanceTask;
use App\Models\User;

class MaintenanceTaskPolicy
{
    public function viewAny(User $user): bool
    {
        return $this->active($user) && $user->isRole('super_admin', 'manager', 'technician');
    }

    public function create(User $user): bool
    {
        return $this->active($user) && $user->isRole('super_admin', 'manager');
    }

    public function update(User $user, MaintenanceTask $maintenanceTask): bool
    {
        if (!$this->active($user)) {
            return false;
        }

        if ($user->isRole('super_admin')) {
            return true;
        }

        if ($user->isRole('manager')) {
            return $this->sameSector($user, $maintenanceTask->sector);
        }

        if ($user->isRole('technician')) {
            return $maintenanceTask->technician_id === $user->id || $this->sameSector($user, $maintenanceTask->sector);
        }

        return false;
    }

    public function delete(User $user, MaintenanceTask $maintenanceTask): bool
    {
        if (!$this->active($user)) {
            return false;
        }

        if ($user->isRole('super_admin')) {
            return true;
        }

        return $user->isRole('manager') && $this->sameSector($user, $maintenanceTask->sector);
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
