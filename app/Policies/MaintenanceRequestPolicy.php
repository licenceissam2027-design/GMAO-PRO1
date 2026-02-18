<?php

namespace App\Policies;

use App\Models\MaintenanceRequest;
use App\Models\User;

class MaintenanceRequestPolicy
{
    public function viewAny(User $user): bool
    {
        return $this->active($user);
    }

    public function create(User $user): bool
    {
        return $this->active($user);
    }

    public function view(User $user, MaintenanceRequest $maintenanceRequest): bool
    {
        if (!$this->active($user)) {
            return false;
        }

        if ($user->isRole('super_admin')) {
            return true;
        }

        if ($user->isRole('manager')) {
            return $this->sameSector($user, $maintenanceRequest->sector);
        }

        if ($user->isRole('technician')) {
            return $maintenanceRequest->assigned_to === $user->id || $this->sameSector($user, $maintenanceRequest->sector);
        }

        return $maintenanceRequest->requester_id === $user->id;
    }

    public function update(User $user, MaintenanceRequest $maintenanceRequest): bool
    {
        if (!$this->active($user)) {
            return false;
        }

        if ($user->isRole('super_admin')) {
            return true;
        }

        if ($user->isRole('manager')) {
            return $this->sameSector($user, $maintenanceRequest->sector);
        }

        if ($user->isRole('technician')) {
            return $maintenanceRequest->assigned_to === $user->id || $this->sameSector($user, $maintenanceRequest->sector);
        }

        return false;
    }

    public function updateStatus(User $user, MaintenanceRequest $maintenanceRequest): bool
    {
        return $this->update($user, $maintenanceRequest);
    }

    public function delete(User $user, MaintenanceRequest $maintenanceRequest): bool
    {
        if (!$this->active($user)) {
            return false;
        }

        if ($user->isRole('super_admin')) {
            return true;
        }

        return $user->isRole('manager') && $this->sameSector($user, $maintenanceRequest->sector);
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
