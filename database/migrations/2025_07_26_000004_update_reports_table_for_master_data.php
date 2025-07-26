<?php
// database/migrations/2025_07_26_000004_update_reports_table_for_master_data.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('reports', function (Blueprint $table) {
            // Add foreign key columns for master data
            $table->foreignId('category_id')->nullable()->after('hse_staff_id')->constrained()->onDelete('set null');
            $table->foreignId('contributing_id')->nullable()->after('category_id')->constrained()->onDelete('set null');
            $table->foreignId('action_id')->nullable()->after('contributing_id')->constrained()->onDelete('set null');

            // Keep old string columns for backward compatibility
            // You can remove these after migrating all existing data
            // $table->dropColumn(['category', 'equipment_type', 'contributing_factor']);
        });
    }

    public function down()
    {
        Schema::table('reports', function (Blueprint $table) {
            $table->dropForeign(['category_id']);
            $table->dropForeign(['contributing_id']);
            $table->dropForeign(['action_id']);
            $table->dropColumn(['category_id', 'contributing_id', 'action_id']);
        });
    }
};
