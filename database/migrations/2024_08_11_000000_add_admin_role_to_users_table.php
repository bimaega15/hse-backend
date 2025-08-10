<?php
// database/migrations/2024_08_11_000000_add_admin_role_to_users_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        // Update the enum to include 'admin' role
        DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('employee', 'hse_staff', 'admin') DEFAULT 'employee'");
    }

    public function down()
    {
        // Revert back to original enum without admin
        DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('employee', 'hse_staff') DEFAULT 'employee'");
    }
};
