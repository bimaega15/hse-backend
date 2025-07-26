<?php
// database/migrations/2025_07_26_000006_drop_observation_forms_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::dropIfExists('observation_forms');
    }

    public function down()
    {
        Schema::create('observation_forms', function (Blueprint $table) {
            $table->id();
            $table->foreignId('report_id')->constrained()->onDelete('cascade');
            $table->integer('at_risk_behavior')->default(0);
            $table->integer('nearmiss_incident')->default(0);
            $table->integer('informasi_risk_mgmt')->default(0);
            $table->integer('sim_k3')->default(0);
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }
};
