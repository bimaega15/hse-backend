<?php
// database/migrations/2026_06_29_000006_create_hse_kpi_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * HSE KPI header: one KPI program for a project + reporting period.
     *
     *  - users_id : JSON array of hse_staff user ids (can be multiple)
     *  - report_date : reporting period (added so the report can filter per tanggal/bulan)
     *  - average : computed average % pencapaian across details
     *  - rumus : JSON value-range bands (sangat baik / baik / cukup / kurang / kurang baik)
     */
    public function up(): void
    {
        Schema::create('hse_kpi', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_kpi_id')->constrained('category_kpi')->cascadeOnDelete();
            $table->foreignId('project_id')->constrained('projects')->cascadeOnDelete();
            $table->json('users_id')->nullable();
            $table->date('report_date');
            $table->text('description')->nullable();
            $table->double('average')->nullable();
            $table->json('rumus')->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->index('report_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hse_kpi');
    }
};
