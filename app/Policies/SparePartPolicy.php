<?php

namespace App\Policies;

use App\Models\SparePart;
use App\Models\User;

class SparePartPolicy
{
    public function viewAny(User $user): bool
    {
        return $this->active($user);
    }

    public function create(User $user): bool
    {
        return $this->active($user) && $user->isRole('super_admin', 'manager', 'technician');
    }

    public function update(User $user, SparePart $sparePart): bool
    {
        if (!$this->create($user)) {
            return false;
        }

        return $this->sameSector($user, $sparePart->sector);
    }

    public function delete(User $user, SparePart $sparePart): bool
    {
        if (!$this->active($user)) {
            return false;
        }

        if (!$user->isRole('super_admin', 'manager')) {
            return false;
        }

        return $this->sameSector($user, $sparePart->sector);
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
