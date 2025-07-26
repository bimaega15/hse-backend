<?php
// database/seeders/MasterDataSeeder.php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Category;
use App\Models\Contributing;
use App\Models\Action;

class MasterDataSeeder extends Seeder
{
    public function run()
    {
        // Seed Categories (standalone)
        $this->seedCategories();

        // Seed Contributings with their Actions
        $this->seedContributingsWithActions();
    }

    private function seedCategories()
    {
        $categories = [
            [
                'name' => 'UNSAFE CONDITION',
                'description' => 'Kondisi tidak aman yang dapat menyebabkan kecelakaan kerja'
            ],
            [
                'name' => 'UNSAFE BEHAVIOR',
                'description' => 'Perilaku tidak aman yang dapat menyebabkan kecelakaan kerja'
            ],
            [
                'name' => 'ENVIRONMENTAL HAZARD',
                'description' => 'Bahaya lingkungan yang dapat mempengaruhi keselamatan kerja'
            ],
            [
                'name' => 'EQUIPMENT FAILURE',
                'description' => 'Kegagalan peralatan dan mesin'
            ],
            [
                'name' => 'EMERGENCY SITUATION',
                'description' => 'Situasi darurat yang memerlukan penanganan khusus'
            ]
        ];

        foreach ($categories as $categoryData) {
            Category::create($categoryData);
        }
    }

    private function seedContributingsWithActions()
    {
        $contributings = [
            [
                'name' => 'Peralatan (hand tool atau alat) yang cacat',
                'description' => 'Alat kerja yang mengalami kerusakan atau cacat',
                'actions' => [
                    'Rancangannya tidak sesuai',
                    'Campuran material yang tidak sesuai',
                    'Penggabungan atau perakitan yang tidak tepat',
                    'Tumpul',
                    'Kasar',
                    'Tajam',
                    'Licin',
                    'Aus',
                    'Terurai',
                    'Retak',
                    'Patah'
                ]
            ],
            [
                'name' => 'Peralatan pelindung diri yang tidak memadai',
                'description' => 'APD yang tidak sesuai standar atau rusak',
                'actions' => [
                    'APD tidak disediakan',
                    'APD tidak sesuai ukuran',
                    'APD rusak atau cacat',
                    'APD kadaluarsa',
                    'APD tidak sesuai jenis pekerjaan',
                    'APD tidak nyaman digunakan',
                    'APD tidak tersedia dalam jumlah cukup'
                ]
            ],
            [
                'name' => 'Pengamanan/pengaman yang tidak memadai',
                'description' => 'Sistem pengaman yang kurang memadai',
                'actions' => [
                    'Pengaman tidak terpasang',
                    'Pengaman tidak berfungsi',
                    'Pengaman mudah dilepas',
                    'Pengaman tidak standar',
                    'Sistem alarm tidak bekerja',
                    'Guard tidak terpasang dengan benar',
                    'Emergency stop tidak berfungsi'
                ]
            ],
            [
                'name' => 'Mengoperasikan peralatan tanpa wewenang',
                'description' => 'Menggunakan peralatan tanpa izin atau sertifikasi',
                'actions' => [
                    'Tidak memiliki sertifikat operator',
                    'Menggunakan alat bukan tugasnya',
                    'Mengoperasikan mesin saat maintenance',
                    'Mengabaikan prosedur start-up',
                    'Tidak mendapat training yang cukup',
                    'Melanggar work permit system'
                ]
            ],
            [
                'name' => 'Gagal menggunakan alat pelindung diri',
                'description' => 'Tidak menggunakan APD sesuai ketentuan',
                'actions' => [
                    'Tidak memakai helm keselamatan',
                    'Tidak memakai safety shoes',
                    'Tidak memakai sarung tangan',
                    'Tidak memakai kacamata pelindung',
                    'Melepas APD saat bekerja',
                    'Tidak memakai ear plug',
                    'Tidak menggunakan safety harness'
                ]
            ],
            [
                'name' => 'Mengabaikan prosedur keselamatan',
                'description' => 'Tidak mengikuti SOP yang telah ditetapkan',
                'actions' => [
                    'Skip safety briefing',
                    'Tidak melakukan lock out tag out',
                    'Mengabaikan rambu peringatan',
                    'Tidak melakukan inspeksi pre-use',
                    'Tidak mengikuti JSA (Job Safety Analysis)',
                    'Mengabaikan work permit requirements'
                ]
            ],
            [
                'name' => 'Kondisi pencahayaan yang buruk',
                'description' => 'Pencahayaan tidak memadai untuk aktivitas kerja',
                'actions' => [
                    'Lampu mati atau redup',
                    'Silau berlebihan',
                    'Bayangan yang mengganggu',
                    'Kontras warna yang buruk',
                    'Pencahayaan tidak merata',
                    'Emergency lighting tidak berfungsi'
                ]
            ],
            [
                'name' => 'Ventilasi yang tidak memadai',
                'description' => 'Sirkulasi udara yang buruk di area kerja',
                'actions' => [
                    'Ventilasi tersumbat',
                    'Kipas angin tidak berfungsi',
                    'AC tidak bekerja optimal',
                    'Kualitas udara buruk',
                    'Kadar oksigen rendah',
                    'Gas beracun terjebak di area kerja'
                ]
            ],
            [
                'name' => 'Kebisingan berlebihan',
                'description' => 'Tingkat kebisingan melebihi batas aman',
                'actions' => [
                    'Mesin berisik tidak di-maintenance',
                    'Tidak ada peredam suara',
                    'Area kerja terlalu berdekatan',
                    'Getaran berlebihan',
                    'Suara peralatan melebihi 85 dB',
                    'Tidak ada warning sign untuk noise area'
                ]
            ],
            [
                'name' => 'Kegagalan sistem mekanik',
                'description' => 'Kerusakan pada sistem mekanik peralatan',
                'actions' => [
                    'Bearing rusak',
                    'Belt putus atau kendor',
                    'Coupling tidak alignment',
                    'Overheating pada motor',
                    'Kebocoran oli hydraulik',
                    'Pressure relief valve tidak berfungsi'
                ]
            ]
        ];

        foreach ($contributings as $contributingData) {
            $contributing = Contributing::create([
                'name' => $contributingData['name'],
                'description' => $contributingData['description']
            ]);

            foreach ($contributingData['actions'] as $actionName) {
                Action::create([
                    'contributing_id' => $contributing->id,
                    'name' => $actionName,
                    'description' => 'Aksi terkait ' . $actionName
                ]);
            }
        }
    }
}
