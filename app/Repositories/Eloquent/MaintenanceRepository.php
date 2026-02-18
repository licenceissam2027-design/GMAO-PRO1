<?php

namespace App\Repositories\Eloquent;

use App\Models\MaintenanceRequest;
use App\Models\MaintenanceTask;
use App\Models\PreventivePlan;
use App\Models\User;
use App\Repositories\Contracts\MaintenanceRepositoryInterface;
use Illuminate\Support\Collection;

class MaintenanceRepository implements MaintenanceRepositoryInterface
{
    public function createRequest(array $data): MaintenanceRequest
    {
        return MaintenanceRequest::create($data);
    }

    public function saveRequest(MaintenanceRequest $request): bool
    {
        return $request->save();
    }

    public function updateRequest(MaintenanceRequest $request, array $data): bool
    {
        return $request->update($data);
    }

    public function deleteRequest(MaintenanceRequest $request): ?bool
    {
        return $request->delete();
    }

    public function createPlan(array $data): PreventivePlan
    {
        return PreventivePlan::create($data);
    }

    public function updatePlan(PreventivePlan $plan, array $data): bool
    {
        return $plan->update($data);
    }

    public function deletePlan(PreventivePlan $plan): ?bool
    {
        return $plan->delete();
    }

    public function createTask(array $data): MaintenanceTask
    {
        return MaintenanceTask::create($data);
    }

    public function updateTask(MaintenanceTask $task, array $data): bool
    {
        return $task->update($data);
    }

    public function deleteTask(MaintenanceTask $task): ?bool
    {
        return $task->delete();
    }

    public function findUserById(int $id): ?User
    {
        return User::find($id);
    }

    public function usersByRoles(array $roles): Collection
    {
        return User::whereIn('role', $roles)->get();
    }
}
