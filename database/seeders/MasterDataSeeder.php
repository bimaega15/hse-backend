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
        // Seed Categories
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
            ]
        ];

        foreach ($categories as $categoryData) {
            $category = Category::create($categoryData);

            if ($category->name === 'UNSAFE CONDITION') {
                $this->seedUnsafeCondition($category);
            } elseif ($category->name === 'UNSAFE BEHAVIOR') {
                $this->seedUnsafeBehavior($category);
            } elseif ($category->name === 'ENVIRONMENTAL HAZARD') {
                $this->seedEnvironmentalHazard($category);
            }
        }
    }

    private function seedUnsafeCondition($category)
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
                    'APD tidak sesuai jenis pekerjaan'
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
                    'Sistem alarm tidak bekerja'
                ]
            ]
        ];

        foreach ($contributings as $contributingData) {
            $contributing = Contributing::create([
                'category_id' => $category->id,
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

    private function seedUnsafeBehavior($category)
    {
        $contributings = [
            [
                'name' => 'Mengoperasikan peralatan tanpa wewenang',
                'description' => 'Menggunakan peralatan tanpa izin atau sertifikasi',
                'actions' => [
                    'Tidak memiliki sertifikat operator',
                    'Menggunakan alat bukan tugasnya',
                    'Mengoperasikan mesin saat maintenance',
                    'Mengabaikan prosedur start-up'
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
                    'Melepas APD saat bekerja'
                ]
            ],
            [
                'name' => 'Mengabaikan prosedur keselamatan',
                'description' => 'Tidak mengikuti SOP yang telah ditetapkan',
                'actions' => [
                    'Skip safety briefing',
                    'Tidak melakukan lock out tag out',
                    'Mengabaikan rambu peringatan',
                    'Tidak melakukan inspeksi pre-use'
                ]
            ]
        ];

        foreach ($contributings as $contributingData) {
            $contributing = Contributing::create([
                'category_id' => $category->id,
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

    private function seedEnvironmentalHazard($category)
    {
        $contributings = [
            [
                'name' => 'Kondisi pencahayaan yang buruk',
                'description' => 'Pencahayaan tidak memadai untuk aktivitas kerja',
                'actions' => [
                    'Lampu mati atau redup',
                    'Silau berlebihan',
                    'Bayangan yang mengganggu',
                    'Kontras warna yang buruk'
                ]
            ],
            [
                'name' => 'Ventilasi yang tidak memadai',
                'description' => 'Sirkulasi udara yang buruk di area kerja',
                'actions' => [
                    'Ventilasi tersumbat',
                    'Kipas angin tidak berfungsi',
                    'AC tidak bekerja optimal',
                    'Kualitas udara buruk'
                ]
            ],
            [
                'name' => 'Kebisingan berlebihan',
                'description' => 'Tingkat kebisingan melebihi batas aman',
                'actions' => [
                    'Mesin berisik tidak di-maintenance',
                    'Tidak ada peredam suara',
                    'Area kerja terlalu berdekatan',
                    'Getaran berlebihan'
                ]
            ]
        ];

        foreach ($contributings as $contributingData) {
            $contributing = Contributing::create([
                'category_id' => $category->id,
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
