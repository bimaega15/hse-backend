<?php
// database/migrations/2026_06_29_000007_create_hse_kpi_detail_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * HSE KPI detail: one indicator line.
     *
     *  - type_target : how the target is expressed (%, <, >, <=, >=, x, Jam Per Hari)
     *  - target / realisasi : target value vs actual achievement
     *  - rumus : optional per-indicator value-range override (else inherits hse_kpi.rumus)
     */
    public function up(): void
    {
        Schema::create('hse_kpi_detail', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hse_kpi_id')->constrained('hse_kpi')->cascadeOnDelete();
            $table->string('activity_name');
            $table->enum('type_target', ['%', '<', '>', '<=', '>=', 'x', 'Jam Per Hari']);
            $table->double('target');
            $table->double('realisasi')->nullable();
            $table->json('rumus')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hse_kpi_detail');
    }
};
