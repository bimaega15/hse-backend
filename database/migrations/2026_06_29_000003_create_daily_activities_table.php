<?php
// database/migrations/2026_06_29_000003_create_daily_activities_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Daily Activity header. Created & assigned by admin to an hse_staff user.
     */
    public function up(): void
    {
        Schema::create('daily_activities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete(); // assigned hse_staff
            $table->dateTime('datetime_activity');
            $table->foreignId('project_id')->constrained('projects')->cascadeOnDelete();
            $table->foreignId('location_id')->constrained('locations')->cascadeOnDelete();
            $table->text('description')->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->index('datetime_activity');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('daily_activities');
    }
};
