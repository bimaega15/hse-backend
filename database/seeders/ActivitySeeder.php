<?php
// database/seeders/ActivitySeeder.php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Activity;

class ActivitySeeder extends Seeder
{
    public function run(): void
    {
        $activities = [
            ['name' => 'Toolbox Meeting',      'description' => 'Pertemuan singkat membahas keselamatan sebelum bekerja'],
            ['name' => 'Safety Meeting',       'description' => 'Rapat keselamatan kerja berkala'],
            ['name' => 'Inspeksi',             'description' => 'Inspeksi area kerja dan peralatan'],
            ['name' => 'Audit Internal',       'description' => 'Audit internal sistem manajemen K3'],
            ['name' => 'Patrol K3',            'description' => 'Patroli keselamatan dan kesehatan kerja di lapangan'],
            ['name' => 'Training / Pelatihan',  'description' => 'Pelatihan dan sosialisasi K3 kepada pekerja'],
            ['name' => 'Investigasi Insiden',  'description' => 'Investigasi dan analisa insiden / near miss'],
            ['name' => 'Monitoring APD',       'description' => 'Pemantauan penggunaan Alat Pelindung Diri'],
        ];

        foreach ($activities as $activity) {
            Activity::firstOrCreate(
                ['name' => $activity['name']],
                ['description' => $activity['description'], 'is_active' => true]
            );
        }

        $this->command?->info('ActivitySeeder: ' . count($activities) . ' activities seeded.');
    }
}
