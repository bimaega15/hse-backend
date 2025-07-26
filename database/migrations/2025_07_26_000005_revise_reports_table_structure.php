<?php
// database/migrations/2025_07_26_000005_revise_reports_table_structure.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('reports', function (Blueprint $table) {
            // Remove old varchar columns
            $table->dropColumn([
                'category',
                'equipment_type',
                'contributing_factor'
            ]);

            // Add new columns
            $table->enum('severity_rating', ['low', 'medium', 'high', 'critical'])->after('action_id');
            $table->text('action_taken')->nullable()->after('severity_rating');
        });
    }

    public function down()
    {
        Schema::table('reports', function (Blueprint $table) {
            // Remove new columns
            $table->dropColumn([
                'severity_rating',
                'action_taken'
            ]);

            // Add back old columns
            $table->string('category')->after('hse_staff_id');
            $table->string('equipment_type')->after('category');
            $table->string('contributing_factor')->after('equipment_type');
        });
    }
};
