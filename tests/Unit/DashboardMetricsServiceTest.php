<?php

namespace Tests\Unit;

use App\Models\IndustrialMachine;
use App\Models\LogisticAsset;
use App\Models\MaintenanceRequest;
use App\Models\MaintenanceTask;
use App\Models\Project;
use App\Models\SparePart;
use App\Models\TechnicalAsset;
use App\Models\User;
use App\Services\DashboardMetricsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardMetricsServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_metrics_are_scoped_by_sector_for_non_admin_user(): void
    {
        $manager = User::factory()->create(['role' => 'manager', 'sector' => 'production']);
        $otherUser = User::factory()->create(['role' => 'manager', 'sector' => 'quality']);

        Project::create([
            'name' => 'P1',
            'code' => 'P1',
            'manager_id' => $manager->id,
            'sector' => 'production',
            'status' => 'planned',
            'priority' => 'medium',
            'progress' => 10,
        ]);
        Project::create([
            'name' => 'P2',
            'code' => 'P2',
            'manager_id' => $otherUser->id,
            'sector' => 'quality',
            'status' => 'planned',
            'priority' => 'medium',
            'progress' => 10,
        ]);

        MaintenanceRequest::create([
            'requester_id' => $manager->id,
            'sector' => 'production',
            'asset_type' => 'industrial',
            'asset_reference' => 'M-01',
            'issue_category' => 'breakdown',
            'severity' => 'critical',
            'status' => 'pending',
            'description' => 'Prod issue',
            'requested_at' => now()->subHours(5),
            'occurrence_at' => now()->subHours(4),
        ]);
        MaintenanceRequest::create([
            'requester_id' => $otherUser->id,
            'sector' => 'quality',
            'asset_type' => 'industrial',
            'asset_reference' => 'M-02',
            'issue_category' => 'breakdown',
            'severity' => 'critical',
            'status' => 'pending',
            'description' => 'Quality issue',
            'requested_at' => now()->subHours(5),
            'occurrence_at' => now()->subHours(4),
        ]);

        MaintenanceTask::create([
            'sector' => 'production',
            'title' => 'Task 1',
            'type' => 'corrective',
            'status' => 'completed',
            'completed_at' => now()->subDay(),
        ]);

        IndustrialMachine::create([
            'name' => 'Machine A',
            'code' => 'MA',
            'sector' => 'production',
            'status' => 'running',
            'criticality' => 'medium',
        ]);
        TechnicalAsset::create([
            'name' => 'PC A',
            'code' => 'PCA',
            'sector' => 'production',
            'category' => 'computer',
            'status' => 'active',
        ]);
        LogisticAsset::create([
            'name' => 'Truck A',
            'code' => 'TA',
            'sector' => 'production',
            'type' => 'truck',
            'status' => 'available',
        ]);
        SparePart::create([
            'name' => 'Bearing',
            'sku' => 'B-01',
            'sector' => 'production',
            'current_stock' => 2,
            'minimum_stock' => 5,
        ]);

        $metrics = app(DashboardMetricsService::class)->build($manager);

        $this->assertSame(1, $metrics['kpiCards']['open_requests']);
        $this->assertSame(1, $metrics['kpiCards']['critical_requests']);
        $this->assertSame(1, $metrics['kpiCards']['completed_tasks_month']);
        $this->assertSame(1, $metrics['kpiCards']['low_stock_parts']);
        $this->assertSame(3, $metrics['kpiCards']['asset_pool']);
    }
}
