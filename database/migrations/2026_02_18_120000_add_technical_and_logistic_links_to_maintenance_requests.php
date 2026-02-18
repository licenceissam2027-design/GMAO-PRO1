<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('maintenance_requests', function (Blueprint $table): void {
            $table->foreignId('technical_asset_id')
                ->nullable()
                ->after('industrial_machine_id')
                ->constrained('technical_assets')
                ->nullOnDelete();
            $table->foreignId('logistic_asset_id')
                ->nullable()
                ->after('technical_asset_id')
                ->constrained('logistic_assets')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('maintenance_requests', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('logistic_asset_id');
            $table->dropConstrainedForeignId('technical_asset_id');
        });
    }
};
