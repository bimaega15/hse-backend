<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('observation_details', function (Blueprint $table) {
            $table->foreignId('contributing_id')->nullable()->after('category_id')->constrained()->onDelete('set null');
            $table->foreignId('action_id')->nullable()->after('contributing_id')->constrained()->onDelete('set null');
            $table->foreignId('location_id')->nullable()->after('action_id')->constrained()->onDelete('set null');
            $table->foreignId('project_id')->nullable()->after('location_id')->constrained()->onDelete('set null');
            $table->datetime('report_date')->nullable()->after('activator_id');
            $table->json('images')->nullable()->after('action_taken');
        });
    }

    public function down()
    {
        Schema::table('observation_details', function (Blueprint $table) {
            $table->dropForeign(['contributing_id']);
            $table->dropColumn('contributing_id');
            $table->dropForeign(['action_id']);
            $table->dropColumn('action_id');
            $table->dropForeign(['location_id']);
            $table->dropColumn('location_id');
            $table->dropForeign(['project_id']);
            $table->dropColumn('project_id');
            $table->dropColumn('report_date');
            $table->dropColumn('images');
        });
    }
};