<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('project_phases', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('project_id')->constrained('projects')->cascadeOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->enum('execution_mode', ['sequential', 'parallel'])->default('sequential');
            $table->unsignedSmallInteger('phase_order')->default(1);
            $table->enum('status', ['planned', 'in_progress', 'completed', 'blocked'])->default('planned');
            $table->unsignedTinyInteger('progress')->default(0);
            $table->foreignId('responsible_id')->nullable()->constrained('users')->nullOnDelete();
            $table->date('planned_start_date')->nullable();
            $table->date('planned_end_date')->nullable();
            $table->date('actual_start_date')->nullable();
            $table->date('actual_end_date')->nullable();
            $table->foreignId('depends_on_phase_id')->nullable()->constrained('project_phases')->nullOnDelete();
            $table->timestamps();

            $table->index(['project_id', 'phase_order']);
            $table->index(['project_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('project_phases');
    }
};

