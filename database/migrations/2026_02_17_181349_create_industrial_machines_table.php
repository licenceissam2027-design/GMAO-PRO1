<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('industrial_machines', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code')->unique();
            $table->string('manufacturer')->nullable();
            $table->string('model')->nullable();
            $table->string('serial_number')->nullable();
            $table->string('location')->nullable();
            $table->enum('status', ['running', 'stopped', 'maintenance'])->default('running');
            $table->enum('criticality', ['low', 'medium', 'high'])->default('medium');
            $table->date('last_maintenance_at')->nullable();
            $table->date('next_maintenance_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('industrial_machines');
    }
};

