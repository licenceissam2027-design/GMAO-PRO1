<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('maintenance_requests', function (Blueprint $table): void {
            $table->index('status');
            $table->index('severity');
            $table->index('requested_at');
            $table->index('occurrence_at');
            $table->index(['sector', 'status']);
        });

        Schema::table('maintenance_tasks', function (Blueprint $table): void {
            $table->index('status');
            $table->index('type');
            $table->index('scheduled_for');
            $table->index(['sector', 'status']);
        });

        Schema::table('projects', function (Blueprint $table): void {
            $table->index('status');
            $table->index('priority');
            $table->index(['sector', 'status']);
        });
    }

    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table): void {
            $table->dropIndex(['status']);
            $table->dropIndex(['priority']);
            $table->dropIndex(['sector', 'status']);
        });

        Schema::table('maintenance_tasks', function (Blueprint $table): void {
            $table->dropIndex(['status']);
            $table->dropIndex(['type']);
            $table->dropIndex(['scheduled_for']);
            $table->dropIndex(['sector', 'status']);
        });

        Schema::table('maintenance_requests', function (Blueprint $table): void {
            $table->dropIndex(['status']);
            $table->dropIndex(['severity']);
            $table->dropIndex(['requested_at']);
            $table->dropIndex(['occurrence_at']);
            $table->dropIndex(['sector', 'status']);
        });
    }
};

