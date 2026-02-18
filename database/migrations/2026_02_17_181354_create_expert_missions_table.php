<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('expert_missions', function (Blueprint $table) {
            $table->id();
            $table->string('expert_name');
            $table->string('company')->nullable();
            $table->string('specialty');
            $table->string('mission_title');
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->enum('status', ['planned', 'active', 'closed'])->default('planned');
            $table->decimal('daily_rate', 10, 2)->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('expert_missions');
    }
};

