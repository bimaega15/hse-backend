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
            [
                'code' => 'PRJ004',
                'project_name' => 'Konstruksi Gedung Baru Jakarta',
                'start_date' => '2025-01-15',
                'end_date' => '2025-08-15',
                'durasi' => 212,
                'status' => 'open',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'code' => 'PRJ005',
                'project_name' => 'Renovasi Pabrik Bekasi',
                'start_date' => '2025-02-01',
                'end_date' => '2025-07-31',
                'durasi' => 180,
                'status' => 'open',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'code' => 'PRJ006',
                'project_name' => 'Instalasi Warehouse Management System',
                'start_date' => '2025-01-20',
                'end_date' => '2025-05-20',
                'durasi' => 120,
                'status' => 'open',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'code' => 'PRJ007',
                'project_name' => 'Pengembangan Sistem Keamanan Surabaya',
                'start_date' => '2025-03-01',
                'end_date' => '2025-09-30',
                'durasi' => 213,
                'status' => 'open',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'code' => 'PRJ008',
                'project_name' => 'Modernisasi Fasilitas Produksi Balikpapan',
                'start_date' => '2025-02-15',
                'end_date' => '2025-10-15',
                'durasi' => 242,
                'status' => 'open',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'code' => 'PRJ009',
                'project_name' => 'Implementasi IoT Monitoring System Medan',
                'start_date' => '2025-01-10',
                'end_date' => '2025-06-10',
                'durasi' => 151,
                'status' => 'open',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'code' => 'PRJ010',
                'project_name' => 'Upgrade Sistem Ventilasi Jakarta',
                'start_date' => '2025-03-15',
                'end_date' => '2025-08-15',
                'durasi' => 153,
                'status' => 'open',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        DB::table('projects')->insert($projects);
    }
}