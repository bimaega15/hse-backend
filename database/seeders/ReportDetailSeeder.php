<?php
// database/seeders/ReportDetailSeeder.php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Report;
use App\Models\ReportDetail;
use App\Models\User;
use Carbon\Carbon;

class ReportDetailSeeder extends Seeder
{
    public function run()
    {
        // Get HSE staff users
        $hseStaffs = User::where('role', 'hse_staff')->get();

        // Get employee users for PIC
        $employees = User::where('role', 'employee')->get();

        if ($hseStaffs->isEmpty()) {
            $this->command->warn('No HSE staff found. Please seed users first.');
            return;
        }

        if ($employees->isEmpty()) {
            $this->command->warn('No employees found for PIC. Please seed users first.');
            return;
        }

        // Get reports that are in-progress or done (can have details)
        $reports = Report::whereIn('status', ['in-progress', 'done'])->get();

        if ($reports->isEmpty()) {
            $this->command->warn('No processed reports found. Please seed reports first.');
            return;
        }

        // Corrective actions specific to each report scenario
        $sampleCorrectiveActions = [
            // For UNSAFE CONDITION - Pahat tumpul
            [
                'action' => 'Mengganti semua pahat yang tumpul dengan yang baru sesuai standar K3',
                'status' => 'open',
                'days_ahead' => 3
            ],
            [
                'action' => 'Melakukan inspeksi rutin mingguan terhadap kondisi hand tools',
                'status' => 'open',
                'days_ahead' => 7
            ],

            // For UNSAFE BEHAVIOR - Tidak memakai helm
            [
                'action' => 'Memberikan pelatihan ulang safety awareness kepada seluruh pekerja',
                'status' => 'in_progress',
                'days_ahead' => 14
            ],
            [
                'action' => 'Menyediakan helm keselamatan baru untuk semua pekerja',
                'status' => 'closed',
                'days_ahead' => -2
            ],
            [
                'action' => 'Melakukan monitoring penggunaan APD harian oleh supervisor',
                'status' => 'in_progress',
                'days_ahead' => 30
            ],

            // For ENVIRONMENTAL HAZARD - Pencahayaan
            [
                'action' => 'Mengganti semua lampu yang mati dengan LED berkualitas tinggi',
                'status' => 'closed',
                'days_ahead' => -1
            ],
            [
                'action' => 'Melakukan audit pencahayaan di seluruh area gudang',
                'status' => 'closed',
                'days_ahead' => -3
            ],

            // For EQUIPMENT FAILURE - Bearing conveyor
            [
                'action' => 'Mengganti bearing motor conveyor yang rusak segera',
                'status' => 'open',
                'days_ahead' => 2
            ],
            [
                'action' => 'Melakukan preventive maintenance rutin pada sistem conveyor',
                'status' => 'open',
                'days_ahead' => 15
            ],

            // For APD rusak
            [
                'action' => 'Pengadaan sarung tangan safety baru dengan spesifikasi terbaru',
                'status' => 'in_progress',
                'days_ahead' => 5
            ],
            [
                'action' => 'Membuat checklist harian kondisi APD untuk setiap pekerja',
                'status' => 'open',
                'days_ahead' => 10
            ],

            // For EMERGENCY - Skip safety briefing
            [
                'action' => 'Implementasi sistem wajib hadir safety briefing dengan barcode scanner',
                'status' => 'closed',
                'days_ahead' => -5
            ],
            [
                'action' => 'Menambah durasi dan materi safety briefing harian',
                'status' => 'closed',
                'days_ahead' => -7
            ],

            // For Kebisingan
            [
                'action' => 'Melakukan maintenance komprehensif pada mesin kompressor',
                'status' => 'open',
                'days_ahead' => 4
            ],
            [
                'action' => 'Menyediakan ear plug dan ear muff untuk area berisik',
                'status' => 'open',
                'days_ahead' => 7
            ]
        ];

        $createdCount = 0;

        foreach ($reports as $report) {
            // Create 2-3 corrective actions per report based on report status
            $detailCount = $report->status === 'done' ? rand(2, 3) : rand(1, 2);

            // Get relevant actions based on report description/category
            $relevantActions = $this->getRelevantActions($report, $sampleCorrectiveActions);

            for ($i = 0; $i < $detailCount && $i < count($relevantActions); $i++) {
                $actionData = $relevantActions[$i];
                $hseStaff = $hseStaffs->random();
                $picEmployee = $employees->random();

                // Calculate due date based on report creation and action timeline
                $baseDate = $report->created_at ?? Carbon::now();
                $dueDate = $baseDate->copy()->addDays($actionData['days_ahead']);

                // Adjust status based on report status and due date
                $status = $this->determineDetailStatus($report, $actionData, $dueDate);

                $reportDetail = ReportDetail::create([
                    'report_id' => $report->id,
                    'correction_action' => $actionData['action'],
                    'due_date' => $dueDate,
                    'users_id' => $picEmployee->id, // Employee as PIC
                    'status_car' => $status,
                    'evidences' => $this->generateSampleEvidences($status),
                    'approved_by' => $hseStaff->id,
                    'created_by' => $hseStaff->id,
                    'created_at' => $baseDate->copy()->addHours(rand(1, 24)),
                    'updated_at' => Carbon::now()->subDays(rand(0, 3)),
                ]);

                $createdCount++;

                $this->command->info("Created report detail {$createdCount} for Report #{$report->id}: {$actionData['action']} (Status: {$status})");
            }
        }

        $this->command->info("Successfully created {$createdCount} report details for " . $reports->count() . " reports.");

        // Show statistics
        $this->showStatistics();
    }

    /**
     * Get relevant corrective actions based on report content
     */
    private function getRelevantActions($report, $allActions)
    {
        $description = strtolower($report->description);
        $category = strtolower($report->category_id ?? '');

        // Create action groups based on report content
        $relevantIndices = [];

        if (strpos($description, 'pahat') !== false || strpos($description, 'tumpul') !== false) {
            $relevantIndices = [0, 1]; // Pahat actions
        } elseif (strpos($description, 'helm') !== false || strpos($description, 'apd') !== false) {
            $relevantIndices = [2, 3, 4]; // APD/Helm actions
        } elseif (strpos($description, 'pencahayaan') !== false || strpos($description, 'lampu') !== false) {
            $relevantIndices = [5, 6]; // Lighting actions
        } elseif (strpos($description, 'bearing') !== false || strpos($description, 'conveyor') !== false) {
            $relevantIndices = [7, 8]; // Equipment actions
        } elseif (strpos($description, 'sarung tangan') !== false) {
            $relevantIndices = [9, 10]; // Glove actions
        } elseif (strpos($description, 'briefing') !== false || strpos($description, 'safety') !== false) {
            $relevantIndices = [11, 12]; // Safety briefing actions
        } elseif (strpos($description, 'kebisingan') !== false || strpos($description, 'bising') !== false) {
            $relevantIndices = [13, 14]; // Noise actions
        } else {
            // Default random selection
            $relevantIndices = array_rand($allActions, min(3, count($allActions)));
            if (!is_array($relevantIndices)) {
                $relevantIndices = [$relevantIndices];
            }
        }

        $relevantActions = [];
        foreach ($relevantIndices as $index) {
            if (isset($allActions[$index])) {
                $relevantActions[] = $allActions[$index];
            }
        }

        // If no relevant actions found, return random ones
        if (empty($relevantActions)) {
            $randomIndices = array_rand($allActions, min(2, count($allActions)));
            if (!is_array($randomIndices)) {
                $randomIndices = [$randomIndices];
            }
            foreach ($randomIndices as $index) {
                $relevantActions[] = $allActions[$index];
            }
        }

        return $relevantActions;
    }

    /**
     * Determine detail status based on report status and due date
     */
    private function determineDetailStatus($report, $actionData, $dueDate)
    {
        $now = Carbon::now();

        if ($report->status === 'done') {
            // For completed reports, most details should be closed
            return rand(0, 100) < 80 ? 'closed' : 'in_progress';
        } elseif ($report->status === 'in-progress') {
            // For in-progress reports, mix of statuses
            if ($dueDate->isPast()) {
                return rand(0, 100) < 60 ? 'in_progress' : 'closed';
            } else {
                $rand = rand(0, 100);
                if ($rand < 40) return 'open';
                elseif ($rand < 80) return 'in_progress';
                else return 'closed';
            }
        } else {
            // For waiting reports, mostly open
            return rand(0, 100) < 90 ? 'open' : 'in_progress';
        }
    }

    /**
     * Generate sample evidence paths based on status
     */
    private function generateSampleEvidences($status)
    {
        // Only add evidences for in_progress and closed status
        if (in_array($status, ['in_progress', 'closed'])) {
            $evidenceCount = rand(1, 3);
            $evidences = [];

            for ($i = 1; $i <= $evidenceCount; $i++) {
                $evidences[] = "report_evidences/sample_evidence_{$i}.jpg";
            }

            return $evidences;
        }

        return null;
    }

    /**
     * Show statistics of created report details
     */
    private function showStatistics()
    {
        $total = ReportDetail::count();
        $open = ReportDetail::where('status_car', 'open')->count();
        $inProgress = ReportDetail::where('status_car', 'in_progress')->count();
        $closed = ReportDetail::where('status_car', 'closed')->count();
        $overdue = ReportDetail::where('due_date', '<', now())
            ->where('status_car', '!=', 'closed')
            ->count();

        $this->command->info("\n=== Report Details Statistics ===");
        $this->command->info("Total Details: {$total}");
        $this->command->info("Open: {$open}");
        $this->command->info("In Progress: {$inProgress}");
        $this->command->info("Closed: {$closed}");
        $this->command->info("Overdue: {$overdue}");

        if ($total > 0) {
            $completionRate = round(($closed / $total) * 100, 2);
            $this->command->info("Completion Rate: {$completionRate}%");
        }
    }
}
