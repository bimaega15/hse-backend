<?php
// database/migrations/2026_06_29_000005_create_category_kpi_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * KPI category (e.g. "Lagging Indicator", "Leading Indicator").
     */
    public function up(): void
    {
        Schema::create('category_kpi', function (Blueprint $table) {
            $table->id();
            $table->string('category_name');
            $table->enum('status', ['active', 'not active'])->default('active');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('category_kpi');
    }
};
