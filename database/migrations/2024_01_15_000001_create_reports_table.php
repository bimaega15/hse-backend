<?php
// database/migrations/2024_01_15_000001_create_reports_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('users');
            $table->string('category');
            $table->string('equipment_type');
            $table->string('contributing_factor');
            $table->text('description');
            $table->string('location');
            $table->enum('status', ['waiting', 'in-progress', 'done'])->default('waiting');
            $table->json('images')->nullable();
            $table->timestamp('start_process_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->foreignId('hse_staff_id')->nullable()->constrained('users');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('reports');
    }
};