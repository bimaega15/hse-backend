<?php
// database/seeders/ReportSeeder.php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Report;
use App\Models\User;
use App\Models\ObservationForm;
use Carbon\Carbon;

class ReportSeeder extends Seeder
{
    public function run()
    {
        $employees = User::where('role', 'employee')->get();
        $hseStaffs = User::where('role', 'hse_staff')->get();

        // Create sample reports
        $reports = [
            [
                'employee_id' => $employees->first()->id,
                'category' => 'Life Safety Equipment',
                'equipment_type' => 'Fire Extinguisher',
                'contributing_factor' => 'Defective machinery/equipment',
                'description' => 'Fire extinguisher expired and needs replacement',
                'location' => 'Building A - Floor 2',
                'status' => 'waiting',
                'created_at' => Carbon::now()->subHours(2),
            ],
            [
                'employee_id' => $employees->skip(1)->first()->id,
                'category' => 'Emergency Equipment',
                'equipment_type' => 'Emergency Light',
                'contributing_factor' => 'Life Safety Equipment',
                'description' => 'Emergency light not working properly',
                'location' => 'Building B - Floor 1',
                'status' => 'in-progress',
                'start_process_at' => Carbon::now()->subHour(),
                'hse_staff_id' => $hseStaffs->first()->id,
                'created_at' => Carbon::now()->subDay(),
            ],
            [
                'employee_id' => $employees->last()->id,
                'category' => 'Electrical Equipment',
                'equipment_type' => 'Smoke Detector',
                'contributing_factor' => 'Lack of maintenance',
                'description' => 'Smoke detector making unusual sounds',
                'location' => 'Building C - Floor 3',
                'status' => 'done',
                'start_process_at' => Carbon::now()->subDays(2),
                'completed_at' => Carbon::now()->subDay(),
                'hse_staff_id' => $hseStaffs->last()->id,
                'created_at' => Carbon::now()->subDays(3),
            ],
            [
                'employee_id' => $employees->first()->id,
                'category' => 'Mechanical Equipment',
                'equipment_type' => 'Fire Alarm',
                'contributing_factor' => 'Improper procedure',
                'description' => 'Fire alarm testing procedure not followed correctly',
                'location' => 'Building A - Floor 1',
                'status' => 'waiting',
                'created_at' => Carbon::now()->subHours(5),
            ],
            [
                'employee_id' => $employees->skip(1)->first()->id,
                'category' => 'Others',
                'equipment_type' => 'Others',
                'contributing_factor' => 'Others',
                'description' => 'Safety signage faded and needs replacement',
                'location' => 'Building B - Parking Area',
                'status' => 'in-progress',
                'start_process_at' => Carbon::now()->subHours(3),
                'hse_staff_id' => $hseStaffs->first()->id,
                'created_at' => Carbon::now()->subHours(6),
            ]
        ];

        foreach ($reports as $reportData) {
            $report = Report::create($reportData);

            // Create observation form for completed reports
            if ($report->status === 'done') {
                ObservationForm::create([
                    'report_id' => $report->id,
                    'at_risk_behavior' => rand(0, 5),
                    'nearmiss_incident' => rand(0, 3),
                    'informasi_risk_mgmt' => rand(1, 5),
                    'sim_k3' => rand(1, 4),
                    'notes' => 'Report completed successfully. All safety measures have been implemented.'
                ]);
            }
        }
    }
}
