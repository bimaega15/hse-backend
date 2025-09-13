<?php
// database/seeders/LocationSeeder.php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Location;

class LocationSeeder extends Seeder
{
    public function run()
    {
        $locations = [
            [
                'name' => 'Kantor Pusat Jakarta',
                'description' => 'Kantor pusat perusahaan di Jakarta',
                'address' => 'Jl. Sudirman No. 123',
                'city' => 'Jakarta',
                'province' => 'DKI Jakarta',
                'postal_code' => '12345',
                'latitude' => -6.2088,
                'longitude' => 106.8456,
                'is_active' => true
            ],
            [
                'name' => 'Pabrik Bekasi',
                'description' => 'Fasilitas produksi utama di Bekasi',
                'address' => 'Kawasan Industri MM2100, Blok A-1',
                'city' => 'Bekasi',
                'province' => 'Jawa Barat',
                'postal_code' => '17520',
                'latitude' => -6.2751,
                'longitude' => 107.1543,
                'is_active' => true
            ],
            [
                'name' => 'Gudang Tangerang',
                'description' => 'Pusat distribusi dan gudang',
                'address' => 'Jl. Raya Serpong No. 456',
                'city' => 'Tangerang',
                'province' => 'Banten',
                'postal_code' => '15310',
                'latitude' => -6.2975,
                'longitude' => 106.6753,
                'is_active' => true
            ],
            [
                'name' => 'Kantor Cabang Surabaya',
                'description' => 'Kantor cabang wilayah Jawa Timur',
                'address' => 'Jl. Tunjungan No. 789',
                'city' => 'Surabaya',
                'province' => 'Jawa Timur',
                'postal_code' => '60261',
                'latitude' => -7.2575,
                'longitude' => 112.7521,
                'is_active' => true
            ],
            [
                'name' => 'Site Balikpapan',
                'description' => 'Site operasional di Kalimantan Timur',
                'address' => 'Jl. Jenderal Sudirman KM 5.5',
                'city' => 'Balikpapan',
                'province' => 'Kalimantan Timur',
                'postal_code' => '76114',
                'latitude' => -1.2379,
                'longitude' => 116.8529,
                'is_active' => true
            ],
            [
                'name' => 'Kantor Regional Medan',
                'description' => 'Kantor regional wilayah Sumatera Utara',
                'address' => 'Jl. Imam Bonjol No. 321',
                'city' => 'Medan',
                'province' => 'Sumatera Utara',
                'postal_code' => '20112',
                'latitude' => 3.5952,
                'longitude' => 98.6722,
                'is_active' => true
            ]
        ];

        foreach ($locations as $locationData) {
            Location::create($locationData);
        }
    }
}