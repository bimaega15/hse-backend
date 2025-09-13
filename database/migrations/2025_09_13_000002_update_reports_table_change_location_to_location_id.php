<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('reports', function (Blueprint $table) {
            // Add the new location_id column
            $table->unsignedBigInteger('location_id')->nullable()->after('action_id');

            // Add foreign key constraint
            $table->foreign('location_id')->references('id')->on('locations')->onDelete('set null');

            // Remove the old location column
            $table->dropColumn('location');
        });
    }

    public function down()
    {
        Schema::table('reports', function (Blueprint $table) {
            // Drop foreign key constraint
            $table->dropForeign(['location_id']);

            // Drop the location_id column
            $table->dropColumn('location_id');

            // Re-add the location column
            $table->string('location')->nullable();
        });
    }
};