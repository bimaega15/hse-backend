<?php
// database/seeders/TbmSeeder.php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Tbm;
use App\Models\User;
use App\Models\Project;
use App\Models\Location;
use Carbon\Carbon;

class TbmSeeder extends Seeder
{
    public function run(): void
    {
        $userIds     = User::where('is_active', true)->pluck('id')->toArray();
        $projectIds  = Project::pluck('id')->toArray();
        $locationIds = Location::pluck('id')->toArray();

        if (empty($userIds) || empty($projectIds) || empty($locationIds)) {
            $this->command?->warn('TbmSeeder skipped: requires users, projects, and locations to exist first.');
            return;
        }

        $topics = [
            'Penggunaan APD (Alat Pelindung Diri) yang benar di area kerja',
            'Prosedur Lock Out Tag Out (LOTO) pada peralatan listrik',
            'Pencegahan kebakaran dan penggunaan APAR',
            'Bekerja di ketinggian dan penggunaan body harness',
            'Identifikasi bahaya dan penilaian risiko (HIRA) sebelum bekerja',
            'Penanganan bahan kimia berbahaya (B3) sesuai MSDS',
            'Keselamatan berkendara dan defensive driving',
            'Ergonomi dan teknik mengangkat beban yang aman',
            'Tanggap darurat dan jalur evakuasi',
            'Housekeeping 5R untuk lingkungan kerja yang aman',
            'Keselamatan pekerjaan panas (hot work) dan izin kerja',
            'Pencegahan terpeleset, tersandung, dan terjatuh',
        ];

        $now = Carbon::now();
        $records = [];

        // Generate ~60 records spread across the last 3 months so the
        // daily/monthly trending charts have meaningful data.
        for ($i = 0; $i < 60; $i++) {
            // Bias more records to the current month for the "trend harian sebulan" chart
            $daysBack = $i < 30 ? rand(0, $now->day - 1 >= 0 ? $now->day : 0) : rand(0, 89);

            $dateTime = $now->copy()
                ->subDays($daysBack)
                ->setTime(rand(7, 9), [0, 15, 30, 45][rand(0, 3)], 0);

            $records[] = [
                'date_time_tbm'     => $dateTime,
                'speaker'           => $userIds[array_rand($userIds)],
                'project'           => $projectIds[array_rand($projectIds)],
                'location'          => $locationIds[array_rand($locationIds)],
                'participant_count' => rand(5, 45),
                'summary_topic'     => $topics[array_rand($topics)],
                'activity_pictures' => null,
                'created_at'        => $dateTime,
                'updated_at'        => $dateTime,
            ];
        }

        foreach (array_chunk($records, 25) as $chunk) {
            Tbm::insert($chunk);
        }

        $this->command?->info('TbmSeeder: created ' . count($records) . ' TBM / Safety Talk records.');
    }
}
