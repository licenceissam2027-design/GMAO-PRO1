<?php

namespace Tests\Feature;

use App\Models\MaintenanceRequest;
use App\Models\User;
use App\Notifications\MaintenanceAlertNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class MaintenanceWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_employee_can_create_maintenance_request_and_notification_is_dispatched(): void
    {
        Notification::fake();

        $admin = User::factory()->create(['role' => 'super_admin']);
        $manager = User::factory()->create(['role' => 'manager']);
        $technician = User::factory()->create(['role' => 'technician']);
        $employee = User::factory()->create(['role' => 'employee']);

        $response = $this->actingAs($employee)->post(route('maintenance.requests.store'), [
            'asset_type' => 'industrial',
            'asset_reference' => 'IND-100',
            'issue_category' => 'mechanical',
            'maintenance_domain' => 'mechanical',
            'failure_mode' => 'excessive_vibration',
            'severity' => 'high',
            'location' => 'Zone B',
            'description' => 'Unexpected vibration near spindle motor.',
            'assigned_to' => $technician->id,
        ]);

        $response->assertRedirect();
        $response->assertSessionHasNoErrors();

        $this->assertDatabaseHas('maintenance_requests', [
            'requester_id' => $employee->id,
            'asset_reference' => 'IND-100',
            'issue_category' => 'mechanical',
            'status' => 'pending',
        ]);

        Notification::assertSentTo([$admin, $manager, $technician], MaintenanceAlertNotification::class);
    }

    public function test_manager_can_complete_maintenance_request_and_admin_is_notified(): void
    {
        Notification::fake();

        $admin = User::factory()->create(['role' => 'super_admin']);
        $manager = User::factory()->create(['role' => 'manager']);
        $requester = User::factory()->create(['role' => 'employee']);

        $request = MaintenanceRequest::create([
            'requester_id' => $requester->id,
            'assigned_to' => null,
            'asset_type' => 'industrial',
            'asset_reference' => 'IND-200',
            'issue_category' => 'electrical',
            'maintenance_domain' => 'electrical',
            'failure_mode' => 'power_loss',
            'severity' => 'critical',
            'status' => 'pending',
            'location' => 'Zone C',
            'description' => 'Control panel intermittent shutdown.',
            'requested_at' => now(),
        ]);

        $response = $this->actingAs($manager)->patch(route('maintenance.requests.status', $request), [
            'status' => 'completed',
        ]);

        $response->assertRedirect();
        $response->assertSessionHasNoErrors();

        $request->refresh();
        $this->assertSame('completed', $request->status);
        $this->assertNotNull($request->resolved_at);

        Notification::assertSentTo($admin, MaintenanceAlertNotification::class);
    }
}
