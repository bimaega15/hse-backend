<?php
// database/migrations/2025_07_27_000002_create_observation_details_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('observation_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('observation_id')->constrained()->onDelete('cascade');
            $table->enum('observation_type', ['at_risk_behavior', 'nearmiss_incident', 'informal_risk_mgmt', 'sim_k3']);
            $table->foreignId('category_id')->constrained()->onDelete('cascade');
            $table->text('description');
            $table->enum('severity', ['low', 'medium', 'high', 'critical']);
            $table->text('action_taken')->nullable();
            $table->timestamps();

            $table->index(['observation_id', 'observation_type']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('observation_details');
    }
};
