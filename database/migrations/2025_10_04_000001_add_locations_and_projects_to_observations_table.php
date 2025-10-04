<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('observations', function (Blueprint $table) {
            $table->foreignId('location_id')->nullable()->constrained('locations')->onDelete('cascade');
            $table->foreignId('project_id')->nullable()->constrained('projects')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::table('observations', function (Blueprint $table) {
            $table->dropForeign(['location_id']);
            $table->dropForeign(['project_id']);
            $table->dropColumn(['location_id', 'project_id']);
        });
    }
};