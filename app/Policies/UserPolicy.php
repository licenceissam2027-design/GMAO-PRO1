<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    public function viewAny(User $user): bool
    {
        return $this->active($user) && $user->isRole('super_admin', 'manager');
    }

    public function create(User $user): bool
    {
        return $this->active($user) && $user->isRole('super_admin');
    }

    public function update(User $user, User $target): bool
    {
        if (!$this->active($user)) {
            return false;
        }

        return $user->isRole('super_admin');
    }

    public function delete(User $user, User $target): bool
    {
        if (!$this->active($user)) {
            return false;
        }

        return $user->isRole('super_admin');
    }

    private function active(User $user): bool
    {
        return (bool) $user->is_active;
    }
}
