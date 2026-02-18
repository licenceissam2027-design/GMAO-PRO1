<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('maintenance_requests', function (Blueprint $table): void {
            $table->string('request_code', 30)->nullable()->unique()->after('id');
            $table->string('maintenance_domain', 60)->nullable()->after('issue_category');
            $table->string('failure_mode', 80)->nullable()->after('maintenance_domain');
            $table->foreignId('industrial_machine_id')->nullable()->after('asset_reference')->constrained('industrial_machines')->nullOnDelete();
            $table->dateTime('occurrence_at')->nullable()->after('description');
            $table->unsignedInteger('downtime_minutes')->nullable()->after('occurrence_at');
            $table->boolean('is_recurrent')->default(false)->after('status');
            $table->unsignedSmallInteger('recurrence_count')->default(1)->after('is_recurrent');

            $table->index(['industrial_machine_id', 'maintenance_domain', 'failure_mode'], 'maint_req_machine_domain_failure_idx');
        });
    }

    public function down(): void
    {
        Schema::table('maintenance_requests', function (Blueprint $table): void {
            $table->dropIndex('maint_req_machine_domain_failure_idx');
            $table->dropConstrainedForeignId('industrial_machine_id');
            $table->dropColumn([
                'request_code',
                'maintenance_domain',
                'failure_mode',
                'occurrence_at',
                'downtime_minutes',
                'is_recurrent',
                'recurrence_count',
            ]);
        });
    }
};
