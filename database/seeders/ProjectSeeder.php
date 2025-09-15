<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ProjectSeeder extends Seeder
{
    /**
     * Run the database seeder.
     */
    public function run(): void
    {
        $projects = [
            [
                'code' => 'PRJ001',
                'project_name' => 'HSE Management System',
                'start_date' => '2025-01-01',
                'end_date' => '2025-12-31',
                'durasi' => 365,
                'status' => 'open',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'code' => 'PRJ002',
                'project_name' => 'Safety Training Program',
                'start_date' => '2025-02-01',
                'end_date' => '2025-06-30',
                'durasi' => 150,
                'status' => 'open',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'code' => 'PRJ003',
                'project_name' => 'Environmental Impact Assessment',
                'start_date' => '2024-10-01',
                'end_date' => '2024-12-31',
                'durasi' => 92,
                'status' => 'closed',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        DB::table('projects')->insert($projects);
    }
}