<?php

namespace Tests\Feature;

use App\Models\IndustrialMachine;
use App\Models\MaintenanceRequest;
use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OperationsCrudTest extends TestCase
{
    use RefreshDatabase;

    public function test_manager_can_update_project(): void
    {
        $manager = User::factory()->create(['role' => 'manager']);
        $project = Project::create([
            'name' => 'Line Upgrade',
            'code' => 'PRJ-01',
            'priority' => 'medium',
            'status' => 'planned',
            'progress' => 10,
        ]);

        $response = $this->actingAs($manager)->patch(route('projects.update', $project), [
            'name' => 'Line Upgrade Updated',
            'code' => 'PRJ-01',
            'priority' => 'high',
            'status' => 'in_progress',
            'progress' => 35,
            'manager_id' => null,
        ]);

        $response->assertRedirect();
        $response->assertSessionHasNoErrors();

        $this->assertDatabaseHas('projects', [
            'id' => $project->id,
            'name' => 'Line Upgrade Updated',
            'status' => 'in_progress',
            'priority' => 'high',
            'progress' => 35,
        ]);
    }

    public function test_manager_can_update_and_delete_maintenance_request(): void
    {
        $manager = User::factory()->create(['role' => 'manager']);
        $employee = User::factory()->create(['role' => 'employee']);

        $request = MaintenanceRequest::create([
            'requester_id' => $employee->id,
            'asset_type' => 'industrial',
            'asset_reference' => 'M-10',
            'issue_category' => 'mechanical',
            'maintenance_domain' => 'mechanical',
            'failure_mode' => 'bearing_failure',
            'severity' => 'high',
            'status' => 'pending',
            'location' => 'Zone A',
            'description' => 'Mechanical noise from main axis motor.',
            'requested_at' => now(),
        ]);

        $update = $this->actingAs($manager)->patch(route('maintenance.requests.update', $request), [
            'asset_type' => 'industrial',
            'asset_reference' => 'M-10',
            'issue_category' => 'electrical',
            'maintenance_domain' => 'electrical',
            'failure_mode' => 'power_loss',
            'severity' => 'critical',
            'status' => 'in_progress',
            'location' => 'Zone A2',
            'description' => 'Updated after electrical diagnostics on control cabinet.',
            'assigned_to' => null,
        ]);

        $update->assertRedirect();
        $update->assertSessionHasNoErrors();

        $this->assertDatabaseHas('maintenance_requests', [
            'id' => $request->id,
            'issue_category' => 'electrical',
            'severity' => 'critical',
            'status' => 'in_progress',
        ]);

        $delete = $this->actingAs($manager)->delete(route('maintenance.requests.destroy', $request));

        $delete->assertRedirect();

        $this->assertDatabaseMissing('maintenance_requests', ['id' => $request->id]);
    }

    public function test_technician_can_update_machine_but_cannot_delete_it(): void
    {
        $technician = User::factory()->create(['role' => 'technician']);
        $machine = IndustrialMachine::create([
            'name' => 'Compressor A',
            'code' => 'IND-900',
            'status' => 'running',
            'criticality' => 'medium',
        ]);

        $update = $this->actingAs($technician)->patch(route('assets.industrial.update', $machine), [
            'name' => 'Compressor A+',
            'code' => 'IND-900',
            'manufacturer' => 'Atlas',
            'model' => 'C-20',
            'serial_number' => 'S-111',
            'location' => 'Plant 2',
            'status' => 'maintenance',
            'criticality' => 'high',
        ]);

        $update->assertRedirect();
        $update->assertSessionHasNoErrors();

        $this->assertDatabaseHas('industrial_machines', [
            'id' => $machine->id,
            'name' => 'Compressor A+',
            'status' => 'maintenance',
            'criticality' => 'high',
        ]);

        $delete = $this->actingAs($technician)->delete(route('assets.industrial.destroy', $machine));
        $delete->assertForbidden();
    }

    public function test_manager_sees_only_his_sector_requests(): void
    {
        $manager = User::factory()->create(['role' => 'manager', 'sector' => 'production']);
        $employee = User::factory()->create(['role' => 'employee', 'sector' => 'production']);

        MaintenanceRequest::create([
            'request_code' => 'MR-PROD-0001',
            'requester_id' => $employee->id,
            'sector' => 'production',
            'asset_type' => 'industrial',
            'asset_reference' => 'PRD-100',
            'issue_category' => 'mechanical',
            'maintenance_domain' => 'mechanical',
            'failure_mode' => 'bearing_failure',
            'severity' => 'high',
            'status' => 'pending',
            'description' => 'Visible in manager sector.',
            'requested_at' => now(),
        ]);

        MaintenanceRequest::create([
            'request_code' => 'MR-UTIL-0001',
            'requester_id' => $employee->id,
            'sector' => 'utilities',
            'asset_type' => 'industrial',
            'asset_reference' => 'UTL-200',
            'issue_category' => 'electrical',
            'maintenance_domain' => 'electrical',
            'failure_mode' => 'power_loss',
            'severity' => 'high',
            'status' => 'pending',
            'description' => 'Hidden from production manager.',
            'requested_at' => now(),
        ]);

        $response = $this->actingAs($manager)->get(route('maintenance.requests'));

        $response->assertOk();
        $response->assertSee('MR-PROD-0001');
        $response->assertDontSee('MR-UTIL-0001');
    }
}
