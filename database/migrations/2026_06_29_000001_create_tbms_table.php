<?php
// database/migrations/2026_06_29_000001_create_tbms_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * TBM / Safety Talk (Toolbox Meeting) records.
     */
    public function up(): void
    {
        Schema::create('tbms', function (Blueprint $table) {
            $table->id();
            $table->dateTime('date_time_tbm');
            $table->foreignId('speaker')->constrained('users')->cascadeOnDelete();
            $table->foreignId('project')->constrained('projects')->cascadeOnDelete();
            $table->foreignId('location')->constrained('locations')->cascadeOnDelete();
            $table->integer('participant_count')->default(0);
            $table->text('summary_topic')->nullable();
            $table->json('activity_pictures')->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->index('date_time_tbm');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tbms');
    }
};
