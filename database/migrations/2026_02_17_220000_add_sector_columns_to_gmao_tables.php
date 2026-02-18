<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('sector', 80)->nullable()->after('role')->index();
        });

        Schema::table('projects', function (Blueprint $table) {
            $table->string('sector', 80)->nullable()->after('manager_id')->index();
        });

        Schema::table('maintenance_requests', function (Blueprint $table) {
            $table->string('sector', 80)->nullable()->after('assigned_to')->index();
        });

        Schema::table('preventive_plans', function (Blueprint $table) {
            $table->string('sector', 80)->nullable()->after('title')->index();
        });

        Schema::table('maintenance_tasks', function (Blueprint $table) {
            $table->string('sector', 80)->nullable()->after('technician_id')->index();
        });

        Schema::table('industrial_machines', function (Blueprint $table) {
            $table->string('sector', 80)->nullable()->after('code')->index();
        });

        Schema::table('technical_assets', function (Blueprint $table) {
            $table->string('sector', 80)->nullable()->after('code')->index();
        });

        Schema::table('spare_parts', function (Blueprint $table) {
            $table->string('sector', 80)->nullable()->after('sku')->index();
        });

        Schema::table('logistic_assets', function (Blueprint $table) {
            $table->string('sector', 80)->nullable()->after('code')->index();
        });
    }

    public function down(): void
    {
        Schema::table('logistic_assets', function (Blueprint $table) {
            $table->dropColumn('sector');
        });

        Schema::table('spare_parts', function (Blueprint $table) {
            $table->dropColumn('sector');
        });

        Schema::table('technical_assets', function (Blueprint $table) {
            $table->dropColumn('sector');
        });

        Schema::table('industrial_machines', function (Blueprint $table) {
            $table->dropColumn('sector');
        });

        Schema::table('maintenance_tasks', function (Blueprint $table) {
            $table->dropColumn('sector');
        });

        Schema::table('preventive_plans', function (Blueprint $table) {
            $table->dropColumn('sector');
        });

        Schema::table('maintenance_requests', function (Blueprint $table) {
            $table->dropColumn('sector');
        });

        Schema::table('projects', function (Blueprint $table) {
            $table->dropColumn('sector');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('sector');
        });
    }
};
