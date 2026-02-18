<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('maintenance_tasks', function (Blueprint $table): void {
            $table->boolean('did_lubrication')->default(false)->after('actual_hours');
            $table->boolean('did_measurement')->default(false)->after('did_lubrication');
            $table->boolean('did_inspection')->default(false)->after('did_measurement');
            $table->boolean('did_replacement')->default(false)->after('did_inspection');
            $table->boolean('did_cleaning')->default(false)->after('did_replacement');
            $table->boolean('anomaly_detected')->default(false)->after('did_cleaning');
            $table->string('measurement_reading', 120)->nullable()->after('anomaly_detected');
            $table->string('inspection_location', 180)->nullable()->after('measurement_reading');
            $table->text('execution_note')->nullable()->after('inspection_location');
            $table->text('anomaly_note')->nullable()->after('execution_note');
            $table->dateTime('round_completed_at')->nullable()->after('anomaly_note');
            $table->index(['type', 'scheduled_for', 'status'], 'maint_task_type_schedule_status_idx');
        });
    }

    public function down(): void
    {
        Schema::table('maintenance_tasks', function (Blueprint $table): void {
            $table->dropIndex('maint_task_type_schedule_status_idx');
            $table->dropColumn([
                'did_lubrication',
                'did_measurement',
                'did_inspection',
                'did_replacement',
                'did_cleaning',
                'anomaly_detected',
                'measurement_reading',
                'inspection_location',
                'execution_note',
                'anomaly_note',
                'round_completed_at',
            ]);
        });
    }
};

