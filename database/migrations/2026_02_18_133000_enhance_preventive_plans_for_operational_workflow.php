<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('preventive_plans', function (Blueprint $table): void {
            $table->string('maintenance_domain', 60)->nullable()->after('asset_reference');
            $table->string('failure_mode', 80)->nullable()->after('maintenance_domain');
            $table->foreignId('industrial_machine_id')->nullable()->after('failure_mode')->constrained('industrial_machines')->nullOnDelete();
            $table->foreignId('technical_asset_id')->nullable()->after('industrial_machine_id')->constrained('technical_assets')->nullOnDelete();
            $table->foreignId('logistic_asset_id')->nullable()->after('technical_asset_id')->constrained('logistic_assets')->nullOnDelete();
            $table->unsignedSmallInteger('interval_value')->default(1)->after('frequency');
            $table->enum('trigger_mode', ['calendar', 'meter', 'both'])->default('calendar')->after('interval_value');
            $table->decimal('meter_threshold', 12, 2)->nullable()->after('trigger_mode');
            $table->unsignedInteger('estimated_duration_minutes')->nullable()->after('meter_threshold');
            $table->enum('skill_level', ['operator', 'technician', 'senior_technician', 'specialist'])->default('technician')->after('estimated_duration_minutes');
            $table->boolean('requires_shutdown')->default(false)->after('skill_level');
            $table->text('procedure_steps')->nullable()->after('checklist');
            $table->text('safety_notes')->nullable()->after('procedure_steps');
            $table->text('spare_parts_list')->nullable()->after('safety_notes');
        });
    }

    public function down(): void
    {
        Schema::table('preventive_plans', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('logistic_asset_id');
            $table->dropConstrainedForeignId('technical_asset_id');
            $table->dropConstrainedForeignId('industrial_machine_id');
            $table->dropColumn([
                'maintenance_domain',
                'failure_mode',
                'interval_value',
                'trigger_mode',
                'meter_threshold',
                'estimated_duration_minutes',
                'skill_level',
                'requires_shutdown',
                'procedure_steps',
                'safety_notes',
                'spare_parts_list',
            ]);
        });
    }
};
