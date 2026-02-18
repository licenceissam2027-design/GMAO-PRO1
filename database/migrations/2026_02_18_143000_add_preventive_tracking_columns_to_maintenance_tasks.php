<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('maintenance_tasks', function (Blueprint $table): void {
            $table->foreignId('preventive_plan_id')
                ->nullable()
                ->after('maintenance_request_id')
                ->constrained('preventive_plans')
                ->nullOnDelete();
            $table->date('generated_for_date')->nullable()->after('scheduled_for');
            $table->dateTime('reminder_sent_at')->nullable()->after('generated_for_date');
            $table->index(['preventive_plan_id', 'generated_for_date'], 'maint_tasks_plan_due_idx');
        });
    }

    public function down(): void
    {
        Schema::table('maintenance_tasks', function (Blueprint $table): void {
            $table->dropIndex('maint_tasks_plan_due_idx');
            $table->dropConstrainedForeignId('preventive_plan_id');
            $table->dropColumn(['generated_for_date', 'reminder_sent_at']);
        });
    }
};
