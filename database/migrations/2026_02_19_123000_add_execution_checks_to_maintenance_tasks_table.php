<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('maintenance_tasks', function (Blueprint $table): void {
            $table->json('execution_checks')->nullable()->after('round_completed_at');
        });
    }

    public function down(): void
    {
        Schema::table('maintenance_tasks', function (Blueprint $table): void {
            $table->dropColumn('execution_checks');
        });
    }
};

