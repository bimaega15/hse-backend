<?php
// database/migrations/2025_08_02_000001_create_report_details_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('report_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('report_id')->constrained('reports')->onDelete('cascade');
            $table->text('correction_action')->comment('Koreksi & Tindakan Korektif');
            $table->date('due_date')->comment('Tanggal selesai');
            $table->foreignId('users_id')->constrained('users')->comment('Person In Charge (Employee)');
            $table->enum('status_car', ['open', 'in_progress', 'closed'])->default('open')->comment('Status CAR');
            $table->json('evidences')->nullable()->comment('Bukti gambar');
            $table->foreignId('approved_by')->constrained('users')->comment('HSE Staff yang approve');
            $table->foreignId('created_by')->constrained('users')->comment('User yang input detail report');
            $table->timestamps();
            $table->softDeletes();

            // Add indexes for better performance
            $table->index(['report_id', 'status_car']);
            $table->index(['due_date']);
            $table->index(['approved_by']);
            $table->index(['users_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('report_details');
    }
};
