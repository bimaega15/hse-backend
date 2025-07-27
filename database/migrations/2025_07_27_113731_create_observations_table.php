<?php
// database/migrations/2025_07_27_000001_create_observations_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('observations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->time('waktu_observasi');
            $table->integer('at_risk_behavior')->default(0);
            $table->integer('nearmiss_incident')->default(0);
            $table->integer('informal_risk_mgmt')->default(0);
            $table->integer('sim_k3')->default(0);
            $table->time('waktu_mulai');
            $table->time('waktu_selesai');
            $table->enum('status', ['draft', 'submitted', 'reviewed'])->default('draft');
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('observations');
    }
};
