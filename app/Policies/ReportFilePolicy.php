<?php

namespace App\Policies;

use App\Models\ReportFile;
use App\Models\User;

class ReportFilePolicy
{
    public function viewAny(User $user): bool
    {
        return $this->active($user);
    }

    public function create(User $user): bool
    {
        return $this->active($user) && $user->isRole('super_admin', 'manager', 'technician');
    }

    public function update(User $user, ReportFile $reportFile): bool
    {
        return $this->create($user);
    }

    public function delete(User $user, ReportFile $reportFile): bool
    {
        return $this->active($user) && $user->isRole('super_admin', 'manager');
    }

    private function active(User $user): bool
    {
        return (bool) $user->is_active;
    }
}
