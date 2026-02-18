<?php

namespace App\Repositories\Contracts;

use App\Models\MaintenanceRequest;
use App\Models\MaintenanceTask;
use App\Models\PreventivePlan;
use App\Models\User;
use Illuminate\Support\Collection;

interface MaintenanceRepositoryInterface
{
    public function createRequest(array $data): MaintenanceRequest;

    public function saveRequest(MaintenanceRequest $request): bool;

    public function updateRequest(MaintenanceRequest $request, array $data): bool;

    public function deleteRequest(MaintenanceRequest $request): ?bool;

    public function createPlan(array $data): PreventivePlan;

    public function updatePlan(PreventivePlan $plan, array $data): bool;

    public function deletePlan(PreventivePlan $plan): ?bool;

    public function createTask(array $data): MaintenanceTask;

    public function updateTask(MaintenanceTask $task, array $data): bool;

    public function deleteTask(MaintenanceTask $task): ?bool;

    public function findUserById(int $id): ?User;

    public function usersByRoles(array $roles): Collection;
}
