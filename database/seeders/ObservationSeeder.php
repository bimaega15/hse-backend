<?php
// database/seeders/ObservationSeeder.php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Observation;
use App\Models\ObservationDetail;
use App\Models\User;
use App\Models\Category;
use Carbon\Carbon;

class ObservationSeeder extends Seeder
{
    public function run()
    {
        $users = User::whereIn('role', ['employee', 'hse_staff'])->get();
        $categories = Category::active()->get();

        if ($users->isEmpty()) {
            $this->command->warn('No users found. Please run UserSeeder first.');
            return;
        }

        if ($categories->isEmpty()) {
            $this->command->warn('No categories found. Please run CategorySeeder first.');
            return;
        }

        // Create sample observations
        $observations = [
            [
                'user_id' => $users->random()->id,
                'waktu_observasi' => '08:00:00',
                'at_risk_behavior' => 2,
                'nearmiss_incident' => 1,
                'informal_risk_mgmt' => 3,
                'sim_k3' => 1,
                'waktu_mulai' => '08:00:00',
                'waktu_selesai' => '08:30:00',
                'status' => 'submitted',
                'notes' => 'Observasi rutin pagi hari di area produksi',
                'created_at' => Carbon::now()->subDays(2),
            ],
            [
                'user_id' => $users->random()->id,
                'waktu_observasi' => '14:00:00',
                'at_risk_behavior' => 1,
                'nearmiss_incident' => 0,
                'informal_risk_mgmt' => 2,
                'sim_k3' => 2,
                'waktu_mulai' => '14:00:00',
                'waktu_selesai' => '14:20:00',
                'status' => 'submitted',
                'notes' => 'Observasi sore hari, kondisi umumnya baik',
                'created_at' => Carbon::now()->subDays(1),
            ],
            [
                'user_id' => $users->random()->id,
                'waktu_observasi' => '10:30:00',
                'at_risk_behavior' => 3,
                'nearmiss_incident' => 2,
                'informal_risk_mgmt' => 1,
                'sim_k3' => 0,
                'waktu_mulai' => '10:30:00',
                'waktu_selesai' => '11:00:00',
                'status' => 'submitted',
                'notes' => 'Observasi tengah pagi, perlu perhatian lebih pada perilaku berisiko',
                'created_at' => Carbon::now()->subHours(3),
            ],
        ];

        foreach ($observations as $observationData) {
            $observation = Observation::create($observationData);

            // Create details for at_risk_behavior
            $this->createObservationDetails($observation, 'at_risk_behavior', $observation->at_risk_behavior, $categories);

            // Create details for nearmiss_incident
            $this->createObservationDetails($observation, 'nearmiss_incident', $observation->nearmiss_incident, $categories);

            // Create details for informal_risk_mgmt
            $this->createObservationDetails($observation, 'informal_risk_mgmt', $observation->informal_risk_mgmt, $categories);

            // Create details for sim_k3
            $this->createObservationDetails($observation, 'sim_k3', $observation->sim_k3, $categories);
        }

        $this->command->info('ObservationSeeder completed successfully!');
    }

    private function createObservationDetails(Observation $observation, string $type, int $count, $categories)
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
            ObservationDetail::create([
                'observation_id' => $observation->id,
                'observation_type' => $type,
                'category_id' => $categories->random()->id,
                'description' => $sampleDescriptions[$type][array_rand($sampleDescriptions[$type])],
                'severity' => ['low', 'medium', 'high', 'critical'][array_rand(['low', 'medium', 'high', 'critical'])],
                'action_taken' => $sampleActions[array_rand($sampleActions)],
            ]);
        }
    }
}
