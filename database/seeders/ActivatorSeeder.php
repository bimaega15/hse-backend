<?php
// database/seeders/ActivatorSeeder.php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Activator;

class ActivatorSeeder extends Seeder
{
    public function run()
    {
        $activators = [
            ['name' => 'Tidak Adanya Sanksi K3'],
            ['name' => 'Tidak Adanya Reward K3 Kerja Baik'],
            ['name' => 'Tidak ada rambu / petunjuk / peringatan / batasan / jalur K3'],
            ['name' => 'Tidak ada Pelatihan K3 yang didapatkan pekerja'],
            ['name' => 'Tidak ada Pengawas K3 / Leader'],
            ['name' => 'APD tidak tersedia'],
            ['name' => 'APD tidak nyaman atau tidak sesuai'],
            ['name' => 'Kondisi kurang fit (Sakit / Lelah)'],
            ['name' => 'Pengaruh Kebiasaan Prilaku rokan yang tidak Safety dan biasa dilakukan pelanggaran tersebut'],
            ['name' => 'Pengaruh dari Pimpinan yang Tidak Sadar'],
            ['name' => 'Tidak Tahu SOP / Peraturan K3 tempat kerja'],
            ['name' => 'Peralatan kerja yg tidak memadai (Bising, Panas, debu, radiasi, sempit, dll)'],
            ['name' => 'Lupa dan Tidak konsentrasi'],
            ['name' => 'Pekerja sudah tahu tetapi melakukan Shortcut / Jalan Pintas karena mau cepat'],
            ['name' => 'Tidak familiar di area tersebut'],
            ['name' => 'Keterbatasan kemampuan fisik (berat, kecil, pendengaran, penglihatan, dll)'],
            ['name' => 'Phobia sesuatu']
        ];

        foreach ($activators as $activator) {
            Activator::create([
                'name' => $activator['name'],
                'description' => 'Aktivator K3 - ' . $activator['name'],
                'is_active' => true
            ]);
        }
    }
}