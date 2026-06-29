<?php
// database/seeders/HseKpiSeeder.php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\CategoryKpi;
use App\Models\HseKpi;
use App\Models\HseKpiDetail;
use App\Models\Project;
use App\Models\User;
use App\Support\KpiScoring;
use Carbon\Carbon;

class HseKpiSeeder extends Seeder
{
    public function run(): void
    {
        $staffIds   = User::where('role', 'hse_staff')->where('is_active', true)->pluck('id')->toArray();
        if (empty($staffIds)) {
            $staffIds = User::where('is_active', true)->pluck('id')->toArray();
        }
        $projectIds = Project::pluck('id')->toArray();

        $lagging = CategoryKpi::where('category_name', 'Lagging Indicator')->first();
        $leading = CategoryKpi::where('category_name', 'Leading Indicator')->first();

        if (empty($staffIds) || empty($projectIds) || !$lagging || !$leading) {
            $this->command?->warn('HseKpiSeeder skipped: requires users, projects, and KPI categories.');
            return;
        }

        // Indicator templates [activity_name, type_target, target]
        $laggingIndicators = [
            ['Fatality', '<=', 0],
            ['Luka / cedera / sakit menyebabkan hari kerja hilang (Lostime Injury)', '<=', 0],
            ['Medical Treatment Cases (P3K)', '<=', 0],
            ['NearMiss Frequency Rate (NMFR)', '<', 40],
            ['Prosentase Probability Nearmiss to Fatality', '<', 6],
            ['Fatality Illness', '<=', 0],
        ];

        $leadingIndicators = [
            ['Pelaksanaan HSSE Management Walk Through (MWT) / Manajemen Visit', 'x', 4],
            ['Pemberian Reward Keselamatan & Budaya K3', 'x', 4],
            ['Pelaksanaan Ops & HSSE Meeting', '%', 100],
            ['Pelaksanaan HSSE Talk / Tool Box Meeting (Team Leader)', '%', 100],
            ['Pelaksanaan HSSE General Induction', '%', 100],
            ['Pelaksanaan HSSE Job Induction untuk Pekerja', '%', 100],
            ['Pelaksanaan Program HSSE Training Project', '%', 90],
            ['Pelaksanaan Daily Checkup Pekerja', '%', 95],
            ['Pelaksanaan Pemeriksaan Harian Kelelahan (Fatigue) Pekerja (OLS & WAT)', '%', 95],
            ['Observasi Prilaku Keselamatan untuk Mendapatkan Index Behavior', 'Jam Per Hari', 2],
            ['Pelaksanaan HSSE Meeting Mitra OH', '%', 90],
        ];

        $now = Carbon::now();
        $count = 0;

        // Create one lagging + one leading KPI for up to 3 projects across recent months
        foreach (array_slice($projectIds, 0, 3) as $i => $projectId) {
            $reportDate = $now->copy()->subMonths($i)->startOfMonth()->addDays(rand(0, 25));
            $assigned = collect($staffIds)->shuffle()->take(rand(1, 3))->values()->toArray();

            // --- Lagging KPI ---
            $count += $this->makeKpi($lagging, $projectId, $assigned, $reportDate, $laggingIndicators, function ($target) {
                return (float) rand(0, 6); // small counts
            });

            // --- Leading KPI ---
            $count += $this->makeKpi($leading, $projectId, $assigned, $reportDate->copy(), $leadingIndicators, function ($target) {
                return round($target * (rand(40, 105) / 100), 1); // ~40%-105% of target
            });
        }

        $this->command?->info("HseKpiSeeder: {$count} HSE KPI records seeded (with details).");
    }

    private function makeKpi(CategoryKpi $category, int $projectId, array $assigned, Carbon $reportDate, array $indicators, callable $realisasiFn): int
    {
        $key = KpiScoring::keyFromName($category->category_name);

        $kpi = HseKpi::create([
            'category_kpi_id' => $category->id,
            'project_id'      => $projectId,
            'users_id'        => $assigned,
            'report_date'     => $reportDate,
            'description'     => 'Laporan pencapaian kinerja ' . $category->category_name,
            'rumus'           => [KpiScoring::rumusFor($key)],
        ]);

        foreach ($indicators as [$name, $type, $target]) {
            HseKpiDetail::create([
                'hse_kpi_id'    => $kpi->id,
                'activity_name' => $name,
                'type_target'   => $type,
                'target'        => (float) $target,
                'realisasi'     => $realisasiFn((float) $target),
                'rumus'         => null,
            ]);
        }

        $kpi->load('details');
        $kpi->recalculateAverage();

        return 1;
    }
}
