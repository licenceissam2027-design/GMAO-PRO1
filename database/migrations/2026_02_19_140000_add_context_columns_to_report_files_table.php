<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('report_files', function (Blueprint $table): void {
            $table->string('context_type', 60)->nullable()->after('report_date');
            $table->unsignedBigInteger('context_id')->nullable()->after('context_type');
            $table->string('context_label')->nullable()->after('context_id');
            $table->string('sector', 60)->nullable()->after('context_label');
            $table->index(['context_type', 'context_id'], 'report_context_idx');
            $table->index('sector', 'report_sector_idx');
        });
    }

    public function down(): void
    {
        Schema::table('report_files', function (Blueprint $table): void {
            $table->dropIndex('report_context_idx');
            $table->dropIndex('report_sector_idx');
            $table->dropColumn(['context_type', 'context_id', 'context_label', 'sector']);
        });
    }
};

