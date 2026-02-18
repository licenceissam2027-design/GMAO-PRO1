<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('preventive_plans', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->enum('asset_type', ['industrial', 'technical', 'logistic', 'other'])->default('other');
            $table->string('asset_reference')->nullable();
            $table->enum('frequency', ['daily', 'weekly', 'monthly', 'quarterly', 'yearly'])->default('monthly');
            $table->date('next_due_date');
            $table->date('last_done_date')->nullable();
            $table->foreignId('responsible_id')->nullable()->constrained('users')->nullOnDelete();
            $table->boolean('is_active')->default(true);
            $table->text('checklist')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('preventive_plans');
    }
};

