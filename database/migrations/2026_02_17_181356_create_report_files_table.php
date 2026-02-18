<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('report_files', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->enum('type', ['daily', 'weekly', 'monthly', 'yearly', 'custom'])->default('custom');
            $table->enum('format', ['excel', 'word', 'pdf'])->default('pdf');
            $table->date('report_date');
            $table->string('file_path')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('report_files');
    }
};

