<?php
// database/seeders/UserSeeder.php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run()
    {
        // Create HSE Staff
        User::create([
            'name' => 'Jane HSE',
            'email' => 'jane.hse@company.com',
            'password' => Hash::make('demo123'),
            'role' => 'hse_staff',
            'department' => 'Health, Safety & Environment',
            'phone' => '08123456789',
            'is_active' => true
        ]);

        // Create Employee
        User::create([
            'name' => 'John Doe',
            'email' => 'john.doe@company.com',
            'password' => Hash::make('demo123'),
            'role' => 'employee',
            'department' => 'IT Department',
            'phone' => '08987654321',
            'is_active' => true
        ]);

        // Create more sample users
        User::create([
            'name' => 'Alice Smith',
            'email' => 'alice.smith@company.com',
            'password' => Hash::make('demo123'),
            'role' => 'employee',
            'department' => 'Operations',
            'phone' => '08111222333',
            'is_active' => true
        ]);

        User::create([
            'name' => 'Bob Wilson',
            'email' => 'bob.wilson@company.com',
            'password' => Hash::make('demo123'),
            'role' => 'hse_staff',
            'department' => 'Health, Safety & Environment',
            'phone' => '08444555666',
            'is_active' => true
        ]);

        User::create([
            'name' => 'Carol Brown',
            'email' => 'carol.brown@company.com',
            'password' => Hash::make('demo123'),
            'role' => 'employee',
            'department' => 'Production',
            'phone' => '08777888999',
            'is_active' => true
        ]);
    }
}
