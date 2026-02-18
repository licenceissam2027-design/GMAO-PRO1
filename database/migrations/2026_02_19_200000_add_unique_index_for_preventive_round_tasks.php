<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('maintenance_tasks', function (Blueprint $table): void {
            $table->unique(
                ['preventive_plan_id', 'generated_for_date'],
                'maint_tasks_unique_plan_generated_date'
            );
        });
    }

    public function down(): void
    {
        Schema::table('maintenance_tasks', function (Blueprint $table): void {
            $table->dropUnique('maint_tasks_unique_plan_generated_date');
        });
    }
};
