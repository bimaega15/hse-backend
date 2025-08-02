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

        if ($hseStaffs->isEmpty()) {
            $this->command->warn('No HSE staff found. Please seed users first.');
            return;
        }

        // Get reports that are in-progress or done (can have details)
        $reports = Report::whereIn('status', ['in-progress', 'done'])->get();

        if ($reports->isEmpty()) {
            $this->command->warn('No processed reports found. Please seed reports first.');
            return;
        }

        $sampleCorrectiveActions = [
            [
                'action' => 'Melakukan penggantian alat safety yang rusak dengan yang baru sesuai standar K3',
                'pic' => 'Safety Officer',
                'status' => 'open',
                'days_ahead' => 7
            ],
            [
                'action' => 'Memberikan training ulang kepada pekerja mengenai penggunaan APD yang benar',
                'pic' => 'HSE Supervisor',
                'status' => 'in_progress',
                'days_ahead' => 14
            ],
            [
                'action' => 'Memperbaiki sistem ventilasi di area kerja untuk mengurangi paparan debu',
                'pic' => 'Maintenance Team',
                'status' => 'open',
                'days_ahead' => 21
            ],
            [
                'action' => 'Melakukan audit mendalam terhadap prosedur kerja di ketinggian',
                'pic' => 'HSE Manager',
                'status' => 'closed',
                'days_ahead' => -5 // Already passed (completed)
            ],
            [
                'action' => 'Menambah rambu-rambu peringatan di area berbahaya',
                'pic' => 'Safety Coordinator',
                'status' => 'in_progress',
                'days_ahead' => 10
            ],
            [
                'action' => 'Melakukan kalibrasi ulang pada alat deteksi gas berbahaya',
                'pic' => 'Technical Staff',
                'status' => 'open',
                'days_ahead' => 30
            ],
            [
                'action' => 'Mengadakan sosialisasi prosedur emergency di seluruh departemen',
                'pic' => 'HSE Team',
                'status' => 'closed',
                'days_ahead' => -10 // Already completed
            ],
            [
                'action' => 'Melakukan pengecekan rutin terhadap kondisi scaffolding setiap minggu',
                'pic' => 'Site Supervisor',
                'status' => 'in_progress',
                'days_ahead' => 7
            ],
            [
                'action' => 'Menyediakan eye wash station di area kimia dan melakukan testing bulanan',
                'pic' => 'Chemical Handler',
                'status' => 'open',
                'days_ahead' => 14
            ],
            [
                'action' => 'Mengimplementasikan sistem permit to work untuk pekerjaan berisiko tinggi',
                'pic' => 'Work Permit Coordinator',
                'status' => 'in_progress',
                'days_ahead' => 28
            ]
        ];

        $createdCount = 0;

        foreach ($reports->take(8) as $report) { // Create details for first 8 reports
            // Random number of details per report (1-3)
            $detailCount = rand(1, 3);

            for ($i = 0; $i < $detailCount; $i++) {
                $actionData = $sampleCorrectiveActions[array_rand($sampleCorrectiveActions)];
                $hseStaff = $hseStaffs->random();

                // Calculate due date
                $dueDate = Carbon::now()->addDays($actionData['days_ahead']);

                $reportDetail = ReportDetail::create([
                    'report_id' => $report->id,
                    'correction_action' => $actionData['action'],
                    'due_date' => $dueDate,
                    'pic' => $actionData['pic'],
                    'status_car' => $actionData['status'],
                    'evidences' => $this->generateSampleEvidences($actionData['status']),
                    'approved_by' => $hseStaff->id,
                    'created_by' => $hseStaff->id,
                    'created_at' => Carbon::now()->subDays(rand(1, 30)),
                    'updated_at' => Carbon::now()->subDays(rand(0, 5)),
                ]);

                $createdCount++;

                $this->command->info("Created report detail {$createdCount}: {$actionData['action']} (Status: {$actionData['status']})");
            }
        }

        $this->command->info("Successfully created {$createdCount} report details for " . $reports->take(8)->count() . " reports.");

        // Show statistics
        $this->showStatistics();
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
