<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('observation_details', function (Blueprint $table) {
            $table->foreignId('activator_id')->nullable()->after('category_id')->constrained()->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::table('observation_details', function (Blueprint $table) {
            $table->dropForeign(['activator_id']);
            $table->dropColumn('activator_id');
        });
    }
};