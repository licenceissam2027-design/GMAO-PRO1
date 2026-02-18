<?php

namespace Database\Seeders;

use App\Models\ExpertMission;
use App\Models\IndustrialMachine;
use App\Models\LogisticAsset;
use App\Models\MaintenanceRequest;
use App\Models\MaintenanceTask;
use App\Models\PreventivePlan;
use App\Models\Project;
use App\Models\ReportFile;
use App\Models\SparePart;
use App\Models\TechnicalAsset;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DemoDataSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::updateOrCreate([
            'email' => 'admin@gmao.local',
        ], [
            'name' => 'System Admin',
            'password' => Hash::make('Admin@12345'),
            'role' => 'super_admin',
            'sector' => 'administration',
            'job_title' => 'GMAO Owner',
            'phone' => '0100000001',
        ]);

        $manager = User::updateOrCreate([
            'email' => 'manager@gmao.local',
        ], [
            'name' => 'Maintenance Manager',
            'password' => Hash::make('Manager@12345'),
            'role' => 'manager',
            'sector' => 'production',
            'job_title' => 'Plant Manager',
            'phone' => '0100000002',
        ]);

        $tech = User::updateOrCreate([
            'email' => 'tech@gmao.local',
        ], [
            'name' => 'Senior Technician',
            'password' => Hash::make('Tech@12345'),
            'role' => 'technician',
            'sector' => 'production',
            'job_title' => 'Technician',
            'phone' => '0100000003',
        ]);

        User::updateOrCreate([
            'email' => 'employee@gmao.local',
        ], [
            'name' => 'Factory Employee',
            'password' => Hash::make('Employee@12345'),
            'role' => 'employee',
            'sector' => 'production',
            'job_title' => 'Operator',
            'phone' => '0100000004',
        ]);

        Project::create([
            'name' => 'Production Line A Upgrade',
            'code' => 'PRJ-001',
            'manager_id' => $manager->id,
            'sector' => 'production',
            'status' => 'in_progress',
            'priority' => 'high',
            'progress' => 55,
            'start_date' => now()->subDays(30)->toDateString(),
            'end_date' => now()->addDays(40)->toDateString(),
            'budget' => 150000,
            'description' => 'Performance improvement and breakdown reduction project.',
        ]);

        $machine = IndustrialMachine::create(['name' => 'CNC-01', 'code' => 'IND-001', 'sector' => 'production', 'status' => 'running', 'criticality' => 'high', 'location' => 'Zone A']);
        TechnicalAsset::create(['name' => 'Server-GMAO', 'code' => 'IT-001', 'sector' => 'it', 'category' => 'computer', 'status' => 'active', 'location' => 'Data Room']);
        SparePart::create(['name' => 'Bearing 6205', 'sku' => 'SP-6205', 'sector' => 'production', 'current_stock' => 5, 'minimum_stock' => 8, 'category' => 'Mechanical']);
        LogisticAsset::create(['name' => 'Forklift #2', 'code' => 'LOG-002', 'sector' => 'logistics', 'type' => 'Forklift', 'status' => 'in_use']);
        ExpertMission::create(['expert_name' => 'Jean Dupont', 'company' => 'EuroTech', 'specialty' => 'PLC', 'mission_title' => 'Automation Upgrade', 'start_date' => now()->toDateString(), 'status' => 'active']);
        PreventivePlan::create(['title' => 'Monthly CNC-01 inspection', 'sector' => 'production', 'asset_type' => 'industrial', 'asset_reference' => 'IND-001', 'frequency' => 'monthly', 'next_due_date' => now()->addMonth()->toDateString(), 'responsible_id' => $tech->id]);

        $request = MaintenanceRequest::create([
            'request_code' => 'MR-DEMO-0001',
            'requester_id' => $manager->id,
            'assigned_to' => $tech->id,
            'sector' => 'production',
            'asset_type' => 'industrial',
            'industrial_machine_id' => $machine->id,
            'asset_reference' => 'IND-001',
            'issue_category' => 'mechanical',
            'maintenance_domain' => 'mechanical',
            'failure_mode' => 'excessive_vibration',
            'severity' => 'high',
            'status' => 'pending',
            'is_recurrent' => true,
            'recurrence_count' => 2,
            'location' => 'Zone A',
            'description' => 'Abnormal vibration in the main spindle.',
            'occurrence_at' => now()->subMinutes(45),
            'downtime_minutes' => 20,
            'requested_at' => now(),
        ]);

        MaintenanceTask::create([
            'maintenance_request_id' => $request->id,
            'technician_id' => $tech->id,
            'sector' => 'production',
            'title' => 'Diagnose CNC-01 spindle',
            'type' => 'corrective',
            'status' => 'in_progress',
            'scheduled_for' => now()->toDateString(),
            'estimated_hours' => 3,
        ]);

        ReportFile::create([
            'title' => 'Weekly maintenance report',
            'type' => 'weekly',
            'format' => 'pdf',
            'report_date' => now()->toDateString(),
            'created_by' => $manager->id,
        ]);

        User::where('id', $admin->id)->first()?->notify(new \App\Notifications\MaintenanceAlertNotification('System setup', __('gmao.msg.system_ready')));
    }
}


