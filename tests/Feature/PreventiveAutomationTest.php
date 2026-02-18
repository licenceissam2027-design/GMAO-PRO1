<?php

namespace Tests\Feature;

use App\Models\MaintenanceTask;
use App\Models\PreventivePlan;
use App\Models\User;
use App\Notifications\MaintenanceAlertNotification;
use App\Services\PreventiveTaskAutomationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class PreventiveAutomationTest extends TestCase
{
    use RefreshDatabase;

    public function test_due_plan_generates_preventive_task_and_advances_next_due_date(): void
    {
        $technician = User::factory()->create(['role' => 'technician', 'sector' => 'production']);
        $plan = PreventivePlan::create([
            'title' => 'Weekly CNC PM',
            'sector' => 'production',
            'asset_type' => 'industrial',
            'asset_reference' => 'CNC-01',
            'frequency' => 'weekly',
            'interval_value' => 1,
            'trigger_mode' => 'calendar',
            'next_due_date' => now()->subDays(2)->toDateString(),
            'responsible_id' => $technician->id,
            'is_active' => true,
        ]);

        $result = app(PreventiveTaskAutomationService::class)->generateDueTasks(now()->startOfDay());

        $this->assertGreaterThanOrEqual(1, $result['tasks_created']);
        $this->assertDatabaseHas('maintenance_tasks', [
            'preventive_plan_id' => $plan->id,
            'type' => 'preventive',
        ]);

        $plan->refresh();
        $this->assertTrue($plan->next_due_date > now()->toDateString());
    }

    public function test_upcoming_preventive_task_sends_reminder_once(): void
    {
        Notification::fake();

        $technician = User::factory()->create(['role' => 'technician', 'sector' => 'production']);
        $plan = PreventivePlan::create([
            'title' => 'Daily Pump PM',
            'sector' => 'production',
            'asset_type' => 'industrial',
            'asset_reference' => 'PUMP-01',
            'frequency' => 'daily',
            'interval_value' => 1,
            'trigger_mode' => 'calendar',
            'next_due_date' => now()->toDateString(),
            'responsible_id' => $technician->id,
            'is_active' => true,
        ]);

        $task = MaintenanceTask::create([
            'preventive_plan_id' => $plan->id,
            'technician_id' => $technician->id,
            'sector' => 'production',
            'title' => 'PM | Daily Pump PM | PUMP-01',
            'type' => 'preventive',
            'status' => 'pending',
            'scheduled_for' => now()->addDay()->toDateString(),
            'generated_for_date' => now()->addDay()->toDateString(),
        ]);

        $sentFirst = app(PreventiveTaskAutomationService::class)->sendUpcomingReminders(now());
        $task->refresh();
        $this->assertSame(1, $sentFirst);
        $this->assertNotNull($task->reminder_sent_at);
        Notification::assertSentTo($technician, MaintenanceAlertNotification::class);

        $sentSecond = app(PreventiveTaskAutomationService::class)->sendUpcomingReminders(now());
        $this->assertSame(0, $sentSecond);
    }
}
