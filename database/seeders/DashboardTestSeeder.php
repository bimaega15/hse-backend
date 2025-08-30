<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Report;
use App\Models\Category;
use App\Models\Contributing;
use App\Models\Action;
use App\Models\ReportDetail;
use App\Models\Observation;
use App\Models\ObservationDetail;
use App\Models\Banner;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;

class DashboardTestSeeder extends Seeder
{
    public function run(): void
    {
        // Create master data first
        $this->createMasterData();

        // Create users
        $this->createUsers();

        // Create reports with varied statuses and severities
        $this->createReports();

        // Create report details
        $this->createReportDetails();

        // Create observations
        $this->createObservations();

        // Create banners
        $this->createBanners();
    }

    private function createMasterData(): void
    {
        // Categories
        $categories = [
            ['name' => 'Accident', 'description' => 'Work-related accidents', 'is_active' => true],
            ['name' => 'Near Miss', 'description' => 'Near miss incidents', 'is_active' => true],
            ['name' => 'Hazard Identification', 'description' => 'Identified workplace hazards', 'is_active' => true],
            ['name' => 'Environmental Incident', 'description' => 'Environmental related incidents', 'is_active' => true],
            ['name' => 'Equipment Failure', 'description' => 'Equipment malfunction or failure', 'is_active' => true],
            ['name' => 'Chemical Spill', 'description' => 'Chemical spill incidents', 'is_active' => true],
        ];

        foreach ($categories as $category) {
            Category::firstOrCreate(['name' => $category['name']], $category);
        }

        // Contributing factors
        $contributings = [
            ['name' => 'Human Error', 'description' => 'Human error related factors', 'is_active' => true],
            ['name' => 'Equipment Malfunction', 'description' => 'Equipment related factors', 'is_active' => true],
            ['name' => 'Environmental Conditions', 'description' => 'Environmental factors', 'is_active' => true],
            ['name' => 'Procedural Issues', 'description' => 'Process and procedure related', 'is_active' => true],
            ['name' => 'Training Deficiency', 'description' => 'Lack of proper training', 'is_active' => true],
        ];

        foreach ($contributings as $contributing) {
            Contributing::firstOrCreate(['name' => $contributing['name']], $contributing);
        }

        // Actions
        $actions = [
            ['contributing_id' => 1, 'name' => 'Retrain Employee', 'description' => 'Provide additional training', 'is_active' => true],
            ['contributing_id' => 1, 'name' => 'Improve Supervision', 'description' => 'Enhance supervision', 'is_active' => true],
            ['contributing_id' => 2, 'name' => 'Repair Equipment', 'description' => 'Fix or replace equipment', 'is_active' => true],
            ['contributing_id' => 2, 'name' => 'Preventive Maintenance', 'description' => 'Schedule maintenance', 'is_active' => true],
            ['contributing_id' => 3, 'name' => 'Improve Ventilation', 'description' => 'Better air circulation', 'is_active' => true],
            ['contributing_id' => 3, 'name' => 'Weather Protection', 'description' => 'Protection from weather', 'is_active' => true],
            ['contributing_id' => 4, 'name' => 'Update Procedures', 'description' => 'Revise work procedures', 'is_active' => true],
            ['contributing_id' => 4, 'name' => 'Safety Briefing', 'description' => 'Conduct safety briefing', 'is_active' => true],
            ['contributing_id' => 5, 'name' => 'Safety Training', 'description' => 'Comprehensive safety training', 'is_active' => true],
            ['contributing_id' => 5, 'name' => 'Competency Assessment', 'description' => 'Assess employee competency', 'is_active' => true],
        ];

        foreach ($actions as $action) {
            Action::firstOrCreate(
                ['name' => $action['name'], 'contributing_id' => $action['contributing_id']],
                $action
            );
        }
    }

    private function createUsers(): void
    {
        // Admin user
        User::firstOrCreate([
            'email' => 'admin@hse.com'
        ], [
            'name' => 'HSE Administrator',
            'password' => Hash::make('password123'),
            'role' => 'admin',
            'department' => 'HSE Administration',
            'phone' => '+6281234567890',
            'is_active' => true,
        ]);

        // HSE Staff
        $hseStaff = [
            ['name' => 'Sarah Johnson', 'email' => 'sarah.johnson@hse.com', 'department' => 'HSE Operations'],
            ['name' => 'David Chen', 'email' => 'david.chen@hse.com', 'department' => 'HSE Management'],
            ['name' => 'Emily Rodriguez', 'email' => 'emily.rodriguez@hse.com', 'department' => 'Safety Inspection'],
            ['name' => 'Michael Brown', 'email' => 'michael.brown@hse.com', 'department' => 'Environmental Safety'],
        ];

        foreach ($hseStaff as $staff) {
            User::firstOrCreate([
                'email' => $staff['email']
            ], [
                'name' => $staff['name'],
                'password' => Hash::make('password123'),
                'role' => 'hse_staff',
                'department' => $staff['department'],
                'phone' => '+6281234567' . rand(100, 999),
                'is_active' => true,
            ]);
        }

        // Employees
        $employees = [
            ['name' => 'John Doe', 'email' => 'john.doe@baiktech.com', 'department' => 'Production'],
            ['name' => 'Jane Smith', 'email' => 'jane.smith@baiktech.com', 'department' => 'Maintenance'],
            ['name' => 'Mike Wilson', 'email' => 'mike.wilson@baiktech.com', 'department' => 'Quality Control'],
            ['name' => 'Lisa Garcia', 'email' => 'lisa.garcia@baiktech.com', 'department' => 'Warehouse'],
            ['name' => 'Robert Taylor', 'email' => 'robert.taylor@baiktech.com', 'department' => 'Engineering'],
            ['name' => 'Amanda White', 'email' => 'amanda.white@baiktech.com', 'department' => 'Operations'],
            ['name' => 'James Miller', 'email' => 'james.miller@baiktech.com', 'department' => 'Production'],
            ['name' => 'Maria Lopez', 'email' => 'maria.lopez@baiktech.com', 'department' => 'Safety'],
        ];

        foreach ($employees as $employee) {
            User::firstOrCreate([
                'email' => $employee['email']
            ], [
                'name' => $employee['name'],
                'password' => Hash::make('password123'),
                'role' => 'employee',
                'department' => $employee['department'],
                'phone' => '+6281234567' . rand(100, 999),
                'is_active' => true,
            ]);
        }
    }

    private function createReports(): void
    {
        $employees = User::where('role', 'employee')->get();
        $hseStaff = User::where('role', 'hse_staff')->get();
        $categories = Category::all();
        $contributings = Contributing::all();
        $actions = Action::all();

        $severities = ['low', 'medium', 'high', 'critical'];
        $statuses = ['waiting', 'in-progress', 'done'];

        $descriptions = [
            'Chemical spill in production area requiring immediate cleanup',
            'Employee slip and fall incident in warehouse section',
            'Near miss incident with forklift operation',
            'Electrical hazard identified in maintenance workshop',
            'Fire extinguisher inspection found expired equipment',
            'Improper use of personal protective equipment observed',
            'Blocked emergency exit identified during routine check',
            'Machinery guard missing on production line equipment',
            'Oil leak detected from hydraulic system',
            'Unsafe lifting practice observed in loading area',
            'Gas leak detected in equipment room',
            'First aid kit missing essential supplies',
            'Damaged safety signage needs replacement',
            'Noise level exceeding safe limits in work area',
            'Inadequate lighting in stairwell area',
        ];

        $locations = [
            'Production Floor A',
            'Production Floor B',
            'Warehouse Section 1',
            'Warehouse Section 2',
            'Maintenance Workshop',
            'Quality Control Lab',
            'Loading Dock',
            'Office Building',
            'Chemical Storage',
            'Equipment Room',
            'Parking Area',
            'Cafeteria',
            'Stairwell B',
            'Emergency Exit 3',
            'Generator Room'
        ];

        // Create reports for the last 6 months with varying distribution
        for ($i = 0; $i < 150; $i++) {
            $employee = $employees->random();
            $category = $categories->random();
            $contributing = $contributings->random();
            $action = $actions->where('contributing_id', $contributing->id)->random();

            // Weight the creation date to have more recent reports
            $weeksAgo = $this->weightedRandom([
                1 => 30,  // 30% chance for this week
                2 => 25,  // 25% chance for last week
                4 => 20,  // 20% chance for last month
                8 => 15,  // 15% chance for 2 months ago
                16 => 7,  // 7% chance for 4 months ago
                24 => 3,  // 3% chance for 6 months ago
            ]);

            $createdAt = Carbon::now()->subWeeks($weeksAgo)->subHours(rand(0, 168));

            // Weight severity towards lower levels
            $severity = $this->weightedRandom([
                'low' => 40,
                'medium' => 35,
                'high' => 20,
                'critical' => 5,
            ]);

            // Weight status based on age - older reports more likely to be completed
            if ($weeksAgo > 8) {
                $status = $this->weightedRandom(['waiting' => 5, 'in-progress' => 15, 'done' => 80]);
            } elseif ($weeksAgo > 2) {
                $status = $this->weightedRandom(['waiting' => 15, 'in-progress' => 35, 'done' => 50]);
            } else {
                $status = $this->weightedRandom(['waiting' => 40, 'in-progress' => 35, 'done' => 25]);
            }

            $reportData = [
                'employee_id' => $employee->id,
                'category_id' => $category->id,
                'contributing_id' => $contributing->id,
                'action_id' => $action->id,
                'severity_rating' => $severity,
                'description' => $descriptions[array_rand($descriptions)],
                'location' => $locations[array_rand($locations)],
                'action_taken' => $status !== 'waiting' ? 'Initial assessment completed and follow-up actions identified.' : null,
                'status' => $status,
                'created_at' => $createdAt,
                'updated_at' => $createdAt,
            ];

            // Add HSE staff and timing for non-waiting reports
            if ($status !== 'waiting') {
                $reportData['hse_staff_id'] = $hseStaff->random()->id;
                $reportData['start_process_at'] = $createdAt->copy()->addHours(rand(1, 48));

                if ($status === 'done') {
                    $reportData['completed_at'] = $reportData['start_process_at']->copy()
                        ->addHours(rand(4, 120)); // 4 hours to 5 days to complete
                }
            }

            Report::create($reportData);
        }
    }

    private function createReportDetails(): void
    {
        $reports = Report::whereIn('status', ['in-progress', 'done'])->get();
        $hseStaff = User::where('role', 'hse_staff')->get();

        $correctionActions = [
            'Install additional safety signage in the affected area',
            'Conduct refresher safety training for all staff',
            'Implement daily equipment inspection checklist',
            'Repair or replace faulty safety equipment',
            'Update emergency response procedures',
            'Increase frequency of safety audits in high-risk areas',
            'Provide additional personal protective equipment',
            'Install better lighting in work areas',
            'Create safety barriers around hazardous zones',
            'Develop new safety protocols for equipment operation',
        ];

        foreach ($reports as $report) {
            // Each report can have 1-3 report details
            $detailCount = rand(1, 3);

            for ($i = 0; $i < $detailCount; $i++) {
                $createdAt = $report->start_process_at ?? $report->created_at;
                $dueDate = $createdAt->copy()->addDays(rand(7, 30));

                // Status based on report status and due date
                if ($report->status === 'done') {
                    $statusCar = $this->weightedRandom(['closed' => 70, 'in_progress' => 20, 'open' => 10]);
                } else {
                    $statusCar = $this->weightedRandom(['open' => 40, 'in_progress' => 45, 'closed' => 15]);
                }

                // Check if overdue
                if ($dueDate < now() && $statusCar !== 'closed') {
                    $statusCar = 'open'; // Keep overdue items as open
                }

                ReportDetail::create([
                    'report_id' => $report->id,
                    'correction_action' => $correctionActions[array_rand($correctionActions)],
                    'due_date' => $dueDate,
                    'pic' => 'Safety Team Leader',
                    'status_car' => $statusCar,
                    'approved_by' => $hseStaff->random()->id,
                    'created_by' => $hseStaff->random()->id,
                    'created_at' => $createdAt,
                    'updated_at' => $createdAt,
                ]);
            }
        }
    }

    private function createObservations(): void
    {
        $users = User::where('role', 'employee')->get();
        $categories = Category::all();

        $observationTypes = ['at_risk_behavior', 'nearmiss_incident', 'informal_risk_mgmt', 'sim_k3'];
        $severities = ['low', 'medium', 'high', 'critical'];
        $statuses = ['draft', 'submitted', 'reviewed'];

        for ($i = 0; $i < 50; $i++) {
            $user = $users->random();
            $createdAt = Carbon::now()->subDays(rand(0, 90));

            $observation = Observation::create([
                'user_id' => $user->id,
                'waktu_observasi' => $createdAt->format('H:i'),
                'waktu_mulai' => $createdAt->format('H:i'),
                'waktu_selesai' => $createdAt->copy()->addHours(rand(1, 4))->format('H:i'),
                'status' => $statuses[array_rand($statuses)],
                'notes' => 'Observation conducted during routine workplace monitoring.',
                'created_at' => $createdAt,
                'updated_at' => $createdAt,
            ]);

            // Create 1-3 observation details
            $detailCount = rand(1, 3);
            $typeCounts = array_fill_keys($observationTypes, 0);

            for ($j = 0; $j < $detailCount; $j++) {
                $type = $observationTypes[array_rand($observationTypes)];
                $typeCounts[$type]++;

                ObservationDetail::create([
                    'observation_id' => $observation->id,
                    'observation_type' => $type,
                    'category_id' => $categories->random()->id,
                    'description' => 'Detailed observation of workplace safety conditions and practices.',
                    'severity' => $severities[array_rand($severities)],
                    'action_taken' => rand(0, 1) ? 'Immediate corrective action taken on site.' : null,
                ]);
            }

            // Update counters
            $observation->update($typeCounts);
        }
    }

    private function createBanners(): void
    {
        $banners = [
            [
                'title' => 'Safety First Initiative',
                'description' => 'Join our organization-wide safety first initiative. Report incidents and help create a safer workplace.',
                'icon' => 'shield-check',
                'background_color' => '#28a745',
                'text_color' => '#ffffff',
                'is_active' => true,
                'sort_order' => 1,
            ],
            [
                'title' => 'Monthly Safety Training',
                'description' => 'Attend mandatory safety training sessions. Next session: Emergency Response Procedures.',
                'icon' => 'graduation-cap',
                'background_color' => '#007bff',
                'text_color' => '#ffffff',
                'is_active' => true,
                'sort_order' => 2,
            ],
            [
                'title' => 'Incident Reporting',
                'description' => 'Quick and easy incident reporting system. Report incidents immediately for faster response.',
                'icon' => 'file-text',
                'background_color' => '#fd7e14',
                'text_color' => '#ffffff',
                'is_active' => true,
                'sort_order' => 3,
            ],
            [
                'title' => 'HSE Compliance Dashboard',
                'description' => 'Monitor compliance metrics and safety performance indicators in real-time.',
                'icon' => 'bar-chart',
                'background_color' => '#6f42c1',
                'text_color' => '#ffffff',
                'is_active' => true,
                'sort_order' => 4,
            ],
        ];

        foreach ($banners as $banner) {
            Banner::firstOrCreate(
                ['title' => $banner['title']],
                $banner
            );
        }
    }

    /**
     * Weighted random selection
     */
    private function weightedRandom(array $weightedValues)
    {
        $totalWeight = array_sum($weightedValues);
        $randomNumber = rand(1, $totalWeight);

        $currentWeight = 0;
        foreach ($weightedValues as $value => $weight) {
            $currentWeight += $weight;
            if ($randomNumber <= $currentWeight) {
                return $value;
            }
        }

        return array_key_first($weightedValues);
    }
}
