<?php
// database/seeders/ObservationSeeder.php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Observation;
use App\Models\ObservationDetail;
use App\Models\User;
use App\Models\Category;
use App\Models\Activator;
use App\Models\Contributing;
use App\Models\Action;
use App\Models\Location;
use App\Models\Project;
use Carbon\Carbon;

class ObservationSeeder extends Seeder
{
    public function run()
    {
        $users = User::whereIn('role', ['employee', 'hse_staff'])->get();
        $categories = Category::active()->get();
        $activators = Activator::active()->get();
        $contributings = Contributing::active()->get();
        $actions = Action::active()->get();
        $locations = Location::active()->get();
        $projects = Project::where('status', 'open')->get();

        if ($users->isEmpty()) {
            $this->command->warn('No users found. Please run UserSeeder first.');
            return;
        }

        if ($categories->isEmpty()) {
            $this->command->warn('No categories found. Please run CategorySeeder first.');
            return;
        }

        if ($activators->isEmpty()) {
            $this->command->warn('No activators found. Please run ActivatorSeeder first.');
            return;
        }

        if ($contributings->isEmpty()) {
            $this->command->warn('No contributings found. Please run ContributingSeeder first.');
            return;
        }

        if ($actions->isEmpty()) {
            $this->command->warn('No actions found. Please run ActionSeeder first.');
            return;
        }

        if ($locations->isEmpty()) {
            $this->command->warn('No locations found. Please run LocationSeeder first.');
            return;
        }

        if ($projects->isEmpty()) {
            $this->command->warn('No open projects found. Please run ProjectSeeder first.');
            return;
        }

        // Create sample observations with different users
        $usersList = $users->shuffle()->take(5);

        $observations = [
            [
                'user_id' => $usersList->get(0)->id,
                'waktu_observasi' => '08:00:00',
                'at_risk_behavior' => 2,
                'nearmiss_incident' => 1,
                'informal_risk_mgmt' => 3,
                'sim_k3' => 1,
                'waktu_mulai' => '08:00:00',
                'waktu_selesai' => '08:30:00',
                'status' => 'submitted',
                'notes' => 'Observasi rutin pagi hari di area produksi',
                'created_at' => Carbon::now()->subDays(4),
            ],
            [
                'user_id' => $usersList->get(1)->id,
                'waktu_observasi' => '14:00:00',
                'at_risk_behavior' => 1,
                'nearmiss_incident' => 0,
                'informal_risk_mgmt' => 2,
                'sim_k3' => 2,
                'waktu_mulai' => '14:00:00',
                'waktu_selesai' => '14:20:00',
                'status' => 'submitted',
                'notes' => 'Observasi sore hari, kondisi umumnya baik',
                'created_at' => Carbon::now()->subDays(3),
            ],
            [
                'user_id' => $usersList->get(2)->id,
                'waktu_observasi' => '10:30:00',
                'at_risk_behavior' => 3,
                'nearmiss_incident' => 2,
                'informal_risk_mgmt' => 1,
                'sim_k3' => 0,
                'waktu_mulai' => '10:30:00',
                'waktu_selesai' => '11:00:00',
                'status' => 'submitted',
                'notes' => 'Observasi tengah pagi, perlu perhatian lebih pada perilaku berisiko',
                'created_at' => Carbon::now()->subDays(2),
            ],
            [
                'user_id' => $usersList->get(3)->id,
                'waktu_observasi' => '09:15:00',
                'at_risk_behavior' => 1,
                'nearmiss_incident' => 3,
                'informal_risk_mgmt' => 2,
                'sim_k3' => 1,
                'waktu_mulai' => '09:15:00',
                'waktu_selesai' => '09:45:00',
                'status' => 'submitted',
                'notes' => 'Observasi pagi di area warehouse, ditemukan beberapa near miss',
                'created_at' => Carbon::now()->subDays(1),
            ],
            [
                'user_id' => $usersList->get(4)->id,
                'waktu_observasi' => '16:00:00',
                'at_risk_behavior' => 0,
                'nearmiss_incident' => 1,
                'informal_risk_mgmt' => 4,
                'sim_k3' => 2,
                'waktu_mulai' => '16:00:00',
                'waktu_selesai' => '16:30:00',
                'status' => 'submitted',
                'notes' => 'Observasi sore di area loading dock, banyak aktivitas informal risk management',
                'created_at' => Carbon::now()->subHours(6),
            ],
        ];

        // Get John Doe user specifically for additional observations
        $johnDoe = User::where('name', 'John Doe')->first();

        if ($johnDoe) {
            // Additional observations for John Doe with different project locations
            $johnDoeObservations = [
                [
                    'user_id' => $johnDoe->id,
                    'waktu_observasi' => '07:30:00',
                    'at_risk_behavior' => 1,
                    'nearmiss_incident' => 0,
                    'informal_risk_mgmt' => 2,
                    'sim_k3' => 1,
                    'waktu_mulai' => '07:30:00',
                    'waktu_selesai' => '08:00:00',
                    'status' => 'submitted',
                    'notes' => 'Observasi pagi di proyek konstruksi gedung baru - Jakarta',
                    'created_at' => Carbon::now()->subDays(7),
                ],
                [
                    'user_id' => $johnDoe->id,
                    'waktu_observasi' => '13:45:00',
                    'at_risk_behavior' => 2,
                    'nearmiss_incident' => 1,
                    'informal_risk_mgmt' => 1,
                    'sim_k3' => 2,
                    'waktu_mulai' => '13:45:00',
                    'waktu_selesai' => '14:15:00',
                    'status' => 'submitted',
                    'notes' => 'Observasi siang di proyek renovasi pabrik - Bekasi',
                    'created_at' => Carbon::now()->subDays(6),
                ],
                [
                    'user_id' => $johnDoe->id,
                    'waktu_observasi' => '15:20:00',
                    'at_risk_behavior' => 0,
                    'nearmiss_incident' => 2,
                    'informal_risk_mgmt' => 3,
                    'sim_k3' => 0,
                    'waktu_mulai' => '15:20:00',
                    'waktu_selesai' => '15:50:00',
                    'status' => 'submitted',
                    'notes' => 'Observasi sore di proyek instalasi warehouse management system - Tangerang',
                    'created_at' => Carbon::now()->subDays(5),
                ],
                [
                    'user_id' => $johnDoe->id,
                    'waktu_observasi' => '11:10:00',
                    'at_risk_behavior' => 3,
                    'nearmiss_incident' => 0,
                    'informal_risk_mgmt' => 2,
                    'sim_k3' => 1,
                    'waktu_mulai' => '11:10:00',
                    'waktu_selesai' => '11:40:00',
                    'status' => 'submitted',
                    'notes' => 'Observasi tengah hari di proyek pengembangan sistem keamanan - Surabaya',
                    'created_at' => Carbon::now()->subDays(4),
                ],
                [
                    'user_id' => $johnDoe->id,
                    'waktu_observasi' => '09:30:00',
                    'at_risk_behavior' => 1,
                    'nearmiss_incident' => 1,
                    'informal_risk_mgmt' => 4,
                    'sim_k3' => 2,
                    'waktu_mulai' => '09:30:00',
                    'waktu_selesai' => '10:00:00',
                    'status' => 'submitted',
                    'notes' => 'Observasi pagi di proyek modernisasi fasilitas produksi - Balikpapan',
                    'created_at' => Carbon::now()->subDays(3),
                ],
                [
                    'user_id' => $johnDoe->id,
                    'waktu_observasi' => '14:15:00',
                    'at_risk_behavior' => 2,
                    'nearmiss_incident' => 2,
                    'informal_risk_mgmt' => 1,
                    'sim_k3' => 3,
                    'waktu_mulai' => '14:15:00',
                    'waktu_selesai' => '14:45:00',
                    'status' => 'submitted',
                    'notes' => 'Observasi siang di proyek implementasi IoT monitoring system - Medan',
                    'created_at' => Carbon::now()->subDays(2),
                ],
                [
                    'user_id' => $johnDoe->id,
                    'waktu_observasi' => '16:45:00',
                    'at_risk_behavior' => 0,
                    'nearmiss_incident' => 0,
                    'informal_risk_mgmt' => 5,
                    'sim_k3' => 1,
                    'waktu_mulai' => '16:45:00',
                    'waktu_selesai' => '17:15:00',
                    'status' => 'submitted',
                    'notes' => 'Observasi sore di proyek upgrade sistem ventilasi - Jakarta',
                    'created_at' => Carbon::now()->subDays(1),
                ],
            ];

            // Specific observations for John Doe - Pengembangan Sistem Keamanan Surabaya at Gudang Tangerang
            $specificProject = Project::where('project_name', 'Pengembangan Sistem Keamanan Surabaya')->first();
            $specificLocation = Location::where('name', 'Gudang Tangerang')->first();

            if ($specificProject && $specificLocation) {
                $specificObservations = [
                    [
                        'user_id' => $johnDoe->id,
                        'waktu_observasi' => '08:30:00',
                        'at_risk_behavior' => 2,
                        'nearmiss_incident' => 1,
                        'informal_risk_mgmt' => 3,
                        'sim_k3' => 1,
                        'waktu_mulai' => '08:30:00',
                        'waktu_selesai' => '09:00:00',
                        'status' => 'submitted',
                        'notes' => 'Observasi pagi di Gudang Tangerang - pemeriksaan sistem keamanan CCTV',
                        'created_at' => Carbon::now()->subDays(10),
                    ],
                    [
                        'user_id' => $johnDoe->id,
                        'waktu_observasi' => '14:20:00',
                        'at_risk_behavior' => 1,
                        'nearmiss_incident' => 0,
                        'informal_risk_mgmt' => 2,
                        'sim_k3' => 2,
                        'waktu_mulai' => '14:20:00',
                        'waktu_selesai' => '14:50:00',
                        'status' => 'submitted',
                        'notes' => 'Observasi siang di Gudang Tangerang - instalasi sensor keamanan',
                        'created_at' => Carbon::now()->subDays(9),
                    ],
                    [
                        'user_id' => $johnDoe->id,
                        'waktu_observasi' => '10:15:00',
                        'at_risk_behavior' => 3,
                        'nearmiss_incident' => 2,
                        'informal_risk_mgmt' => 1,
                        'sim_k3' => 0,
                        'waktu_mulai' => '10:15:00',
                        'waktu_selesai' => '10:45:00',
                        'status' => 'submitted',
                        'notes' => 'Observasi pagi di Gudang Tangerang - pengujian sistem alarm keamanan',
                        'created_at' => Carbon::now()->subDays(8),
                    ],
                    [
                        'user_id' => $johnDoe->id,
                        'waktu_observasi' => '15:45:00',
                        'at_risk_behavior' => 0,
                        'nearmiss_incident' => 1,
                        'informal_risk_mgmt' => 4,
                        'sim_k3' => 3,
                        'waktu_mulai' => '15:45:00',
                        'waktu_selesai' => '16:15:00',
                        'status' => 'submitted',
                        'notes' => 'Observasi sore di Gudang Tangerang - konfigurasi access control system',
                        'created_at' => Carbon::now()->subDays(7),
                    ],
                    [
                        'user_id' => $johnDoe->id,
                        'waktu_observasi' => '11:30:00',
                        'at_risk_behavior' => 1,
                        'nearmiss_incident' => 3,
                        'informal_risk_mgmt' => 2,
                        'sim_k3' => 1,
                        'waktu_mulai' => '11:30:00',
                        'waktu_selesai' => '12:00:00',
                        'status' => 'submitted',
                        'notes' => 'Observasi siang di Gudang Tangerang - integrasi sistem keamanan dengan database utama',
                        'created_at' => Carbon::now()->subDays(6),
                    ],
                ];

                // Process specific observations
                foreach ($specificObservations as $observationData) {
                    $observation = Observation::create($observationData);

                    // Create details with specific project and location
                    $this->createSpecificObservationDetails($observation, 'at_risk_behavior', $observation->at_risk_behavior, $categories, $activators, $contributings, $actions, $specificLocation, $specificProject);
                    $this->createSpecificObservationDetails($observation, 'nearmiss_incident', $observation->nearmiss_incident, $categories, $activators, $contributings, $actions, $specificLocation, $specificProject);
                    $this->createSpecificObservationDetails($observation, 'informal_risk_mgmt', $observation->informal_risk_mgmt, $categories, $activators, $contributings, $actions, $specificLocation, $specificProject);
                    $this->createSpecificObservationDetails($observation, 'sim_k3', $observation->sim_k3, $categories, $activators, $contributings, $actions, $specificLocation, $specificProject);
                }
            }

            // Process John Doe's additional observations
            foreach ($johnDoeObservations as $observationData) {
                $observation = Observation::create($observationData);

                // Create details for at_risk_behavior
                $this->createObservationDetails($observation, 'at_risk_behavior', $observation->at_risk_behavior, $categories, $activators, $contributings, $actions, $locations, $projects);

                // Create details for nearmiss_incident
                $this->createObservationDetails($observation, 'nearmiss_incident', $observation->nearmiss_incident, $categories, $activators, $contributings, $actions, $locations, $projects);

                // Create details for informal_risk_mgmt
                $this->createObservationDetails($observation, 'informal_risk_mgmt', $observation->informal_risk_mgmt, $categories, $activators, $contributings, $actions, $locations, $projects);

                // Create details for sim_k3
                $this->createObservationDetails($observation, 'sim_k3', $observation->sim_k3, $categories, $activators, $contributings, $actions, $locations, $projects);
            }
        }

        foreach ($observations as $observationData) {
            $observation = Observation::create($observationData);

            // Create details for at_risk_behavior
            $this->createObservationDetails($observation, 'at_risk_behavior', $observation->at_risk_behavior, $categories, $activators, $contributings, $actions, $locations, $projects);

            // Create details for nearmiss_incident
            $this->createObservationDetails($observation, 'nearmiss_incident', $observation->nearmiss_incident, $categories, $activators, $contributings, $actions, $locations, $projects);

            // Create details for informal_risk_mgmt
            $this->createObservationDetails($observation, 'informal_risk_mgmt', $observation->informal_risk_mgmt, $categories, $activators, $contributings, $actions, $locations, $projects);

            // Create details for sim_k3
            $this->createObservationDetails($observation, 'sim_k3', $observation->sim_k3, $categories, $activators, $contributings, $actions, $locations, $projects);
        }

        $this->command->info('ObservationSeeder completed successfully!');
    }

    private function createObservationDetails(Observation $observation, string $type, int $count, $categories, $activators = null, $contributings = null, $actions = null, $locations = null, $projects = null)
    {
        $sampleDescriptions = [
            'at_risk_behavior' => [
                'Pekerja tidak menggunakan helm safety saat bekerja di area konstruksi',
                'Pekerja mengangkat beban melebihi kapasitas yang direkomendasikan',
                'Pekerja tidak menggunakan sarung tangan saat menangani bahan kimia',
                'Pekerja bekerja di ketinggian tanpa pengaman safety harness',
                'Pekerja menggunakan alat yang rusak tanpa melaporkan ke supervisor'
            ],
            'nearmiss_incident' => [
                'Material hampir jatuh dari ketinggian karena tidak diamankan dengan baik',
                'Pekerja hampir terpeleset karena lantai licin yang belum diberi warning sign',
                'Alat berat hampir menabrak pekerja karena area kerja tidak diberi batas yang jelas',
                'Kebocoran kecil pada pipa gas yang hampir menyebabkan insiden serius',
                'Listrik hampir short circuit karena kabel yang terkelupas'
            ],
            'informal_risk_mgmt' => [
                'Diskusi spontan dengan tim tentang cara kerja yang lebih aman',
                'Sharing pengalaman safety dengan rekan kerja baru',
                'Memberikan reminder kepada tim tentang penggunaan APD',
                'Melakukan pengecekan kondisi alat kerja sebelum digunakan',
                'Koordinasi informal dengan supervisor tentang kondisi area kerja'
            ],
            'sim_k3' => [
                'Melaporkan kondisi tidak aman melalui sistem SIM K3',
                'Update status perbaikan fasilitas keselamatan di sistem',
                'Input data observasi keselamatan harian',
                'Melaporkan insiden minor melalui aplikasi SIM K3',
                'Update training record karyawan di sistem'
            ]
        ];

        $sampleActions = [
            'Memberikan edukasi tentang pentingnya penggunaan APD',
            'Melakukan perbaikan fasilitas yang rusak',
            'Memberikan peringatan dan counseling kepada pekerja',
            'Memasang tanda peringatan di area berbahaya',
            'Melakukan inspeksi ulang pada area kerja',
            'Mengadakan safety briefing untuk seluruh tim',
            'Mengganti alat yang rusak dengan yang baru',
            'Melakukan maintenance preventif pada peralatan'
        ];

        for ($i = 0; $i < $count; $i++) {
            $detailData = [
                'observation_id' => $observation->id,
                'observation_type' => $type,
                'category_id' => $categories->random()->id,
                'contributing_id' => $contributings && $contributings->count() > 0 ? $contributings->random()->id : null,
                'action_id' => $actions && $actions->count() > 0 ? $actions->random()->id : null,
                'location_id' => $locations && $locations->count() > 0 ? $locations->random()->id : null,
                'project_id' => $projects && $projects->count() > 0 ? $projects->random()->id : null,
                'description' => $sampleDescriptions[$type][array_rand($sampleDescriptions[$type])],
                'severity' => ['low', 'medium', 'high', 'critical'][array_rand(['low', 'medium', 'high', 'critical'])],
                'action_taken' => $sampleActions[array_rand($sampleActions)],
                'report_date' => $observation->created_at->copy()->addMinutes(rand(5, 180)),
            ];

            // Add activator_id only for at_risk_behavior type
            if ($type === 'at_risk_behavior' && $activators && $activators->count() > 0) {
                $detailData['activator_id'] = $activators->random()->id;
            }

            ObservationDetail::create($detailData);
        }
    }

    private function createSpecificObservationDetails(Observation $observation, string $type, int $count, $categories, $activators = null, $contributings = null, $actions = null, $location = null, $project = null)
    {
        $sampleDescriptions = [
            'at_risk_behavior' => [
                'Pekerja tidak menggunakan helm safety saat instalasi sistem keamanan',
                'Teknisi tidak menggunakan sepatu anti-listrik saat bekerja dengan perangkat elektronik',
                'Pekerja menggunakan tangga yang tidak stabil untuk instalasi CCTV',
                'Teknisi tidak mematikan power supply sebelum maintenance perangkat',
                'Pekerja tidak menggunakan sarung tangan isolasi saat handling kabel listrik'
            ],
            'nearmiss_incident' => [
                'Kabel data hampir putus karena tertarik saat instalasi',
                'Perangkat CCTV hampir jatuh karena bracket tidak terpasang dengan baik',
                'Server hampir overheating karena ventilasi tertutup',
                'Sensor keamanan hampir rusak karena terkena air',
                'Access control panel hampir short circuit karena kabel terkelupas'
            ],
            'informal_risk_mgmt' => [
                'Diskusi dengan tim tentang prosedur instalasi yang aman',
                'Briefing safety sebelum mulai pekerjaan instalasi sistem',
                'Koordinasi dengan supervisor tentang area kerja yang aman',
                'Sharing best practice maintenance perangkat keamanan',
                'Review checklist keselamatan kerja dengan tim'
            ],
            'sim_k3' => [
                'Input data instalasi sistem keamanan ke SIM K3',
                'Update progress pengembangan sistem di aplikasi SIM K3',
                'Laporan maintenance rutin perangkat melalui SIM K3',
                'Upload dokumentasi testing sistem keamanan',
                'Submit report incident kecil melalui SIM K3'
            ]
        ];

        $sampleActions = [
            'Memberikan training penggunaan peralatan safety untuk teknisi',
            'Melakukan pengecekan berkala kondisi perangkat keamanan',
            'Memasang warning sign di area instalasi sistem',
            'Memberikan briefing safety khusus instalasi elektronik',
            'Mengganti peralatan yang tidak memenuhi standar keselamatan',
            'Melakukan maintenance preventif pada sistem keamanan',
            'Update SOP instalasi dan maintenance sistem keamanan',
            'Koordinasi dengan vendor untuk training peralatan baru'
        ];

        for ($i = 0; $i < $count; $i++) {
            $detailData = [
                'observation_id' => $observation->id,
                'observation_type' => $type,
                'category_id' => $categories->random()->id,
                'contributing_id' => $contributings && $contributings->count() > 0 ? $contributings->random()->id : null,
                'action_id' => $actions && $actions->count() > 0 ? $actions->random()->id : null,
                'location_id' => $location ? $location->id : null,
                'project_id' => $project ? $project->id : null,
                'description' => $sampleDescriptions[$type][array_rand($sampleDescriptions[$type])],
                'severity' => ['low', 'medium', 'high', 'critical'][array_rand(['low', 'medium', 'high', 'critical'])],
                'action_taken' => $sampleActions[array_rand($sampleActions)],
                'report_date' => $observation->created_at->copy()->addMinutes(rand(5, 180)),
            ];

            // Add activator_id only for at_risk_behavior type
            if ($type === 'at_risk_behavior' && $activators && $activators->count() > 0) {
                $detailData['activator_id'] = $activators->random()->id;
            }

            ObservationDetail::create($detailData);
        }
    }
}
