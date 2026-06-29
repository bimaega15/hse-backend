<?php
// database/seeders/DailyActivitySeeder.php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\DailyActivity;
use App\Models\DailyActivityDetail;
use App\Models\Activity;
use App\Models\User;
use App\Models\Project;
use App\Models\Location;
use Carbon\Carbon;

class DailyActivitySeeder extends Seeder
{
    public function run(): void
    {
        // Assigned personnel = hse_staff (fallback to any active user)
        $staffIds = User::where('role', 'hse_staff')->where('is_active', true)->pluck('id')->toArray();
        if (empty($staffIds)) {
            $staffIds = User::where('is_active', true)->pluck('id')->toArray();
        }

        $projectIds  = Project::pluck('id')->toArray();
        $locationIds = Location::pluck('id')->toArray();
        $activityIds = Activity::pluck('id')->toArray();

        if (empty($staffIds) || empty($projectIds) || empty($locationIds) || empty($activityIds)) {
            $this->command?->warn('DailyActivitySeeder skipped: requires users, projects, locations, and activities.');
            return;
        }

        $todolists = [
            'Permintaan APD dari pekerja',
            'Melaporkan kegiatan ke management',
            'Persiapan audit internal',
            'Inspeksi housekeeping area gudang',
            'Pengecekan APAR dan hydrant',
            'Sosialisasi prosedur LOTO',
            'Pemeriksaan izin kerja panas',
            'Monitoring pekerjaan di ketinggian',
            'Briefing K3 sebelum shift',
            'Penanganan temuan unsafe condition',
        ];

        $statusDescriptions = [
            'pending'     => 'Menunggu pelaksanaan',
            'in_progress' => 'Sedang dikerjakan di lapangan',
            'done'        => 'Telah selesai dilaksanakan',
            'cancel'      => 'Dibatalkan karena perubahan jadwal',
            'rejected'    => 'Ditolak, perlu revisi',
        ];

        $statuses = array_keys(DailyActivityDetail::STATUSES);
        $now = Carbon::now();

        // Create ~18 daily activity headers across the last 2 months
        for ($i = 0; $i < 18; $i++) {
            $staffId  = $staffIds[array_rand($staffIds)];
            $dateTime = $now->copy()->subDays(rand(0, 55))->setTime(rand(7, 16), [0, 30][rand(0, 1)], 0);

            $header = DailyActivity::create([
                'user_id'           => $staffId,
                'datetime_activity' => $dateTime,
                'project_id'        => $projectIds[array_rand($projectIds)],
                'location_id'       => $locationIds[array_rand($locationIds)],
                'description'       => 'Laporan aktivitas harian HSE personnel',
            ]);

            // Each header gets 1-4 detail to-do items
            $detailCount = rand(1, 4);
            for ($j = 0; $j < $detailCount; $j++) {
                $status = $statuses[array_rand($statuses)];
                $activityDt = $dateTime->copy()->addHours(rand(0, 6));

                DailyActivityDetail::create([
                    'daily_activity_id'    => $header->id,
                    'activity_id'          => $activityIds[array_rand($activityIds)],
                    'todolist'             => $todolists[array_rand($todolists)],
                    'activity_datetime'    => $activityDt,
                    'status'               => $status,
                    'description_status'   => $statusDescriptions[$status],
                    'pictures_activity'    => null,
                    'realization_datetime' => $status === 'done' ? $activityDt->copy()->addHours(rand(1, 5)) : null,
                    'user_id'              => $staffId,
                ]);
            }
        }

        $this->command?->info('DailyActivitySeeder: 18 headers with details seeded.');
    }
}
