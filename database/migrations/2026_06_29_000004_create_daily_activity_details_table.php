<?php
// database/migrations/2026_06_29_000004_create_daily_activity_details_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Daily Activity detail (to-do list items). Filled by the assigned
     * hse_staff via the mobile API.
     *
     * Note: the original spec had typos `desctiption_status` and
     * `realitation_datetime`; corrected here to `description_status` and
     * `realization_datetime`.
     */
    public function up(): void
    {
        Schema::create('daily_activity_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('daily_activity_id')->constrained('daily_activities')->cascadeOnDelete();
            $table->foreignId('activity_id')->constrained('activities')->cascadeOnDelete();
            $table->text('todolist');
            $table->dateTime('activity_datetime');
            $table->enum('status', ['pending', 'in_progress', 'done', 'cancel', 'rejected'])->default('pending');
            $table->text('description_status')->nullable();
            $table->json('pictures_activity')->nullable();
            $table->dateTime('realization_datetime')->nullable();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete(); // hse_staff
            $table->softDeletes();
            $table->timestamps();

            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('daily_activity_details');
    }
};
