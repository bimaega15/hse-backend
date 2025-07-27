<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Banner;

class BannerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $banners = [
            [
                'title' => 'Response Time Cepat',
                'description' => 'Tim HSE siap menangani laporan Anda 24/7',
                'icon' => 'bolt', // FontAwesome bolt icon (lightning)
                'image' => null, // Will use background_color instead
                'background_color' => '#ff9500', // Orange color like in the image
                'text_color' => '#ffffff',
                'is_active' => true,
                'sort_order' => 1,
            ],
            [
                'title' => 'Safety First',
                'description' => 'Keselamatan adalah prioritas utama di tempat kerja',
                'icon' => 'shield-alt',
                'image' => null,
                'background_color' => '#28a745', // Green color
                'text_color' => '#ffffff',
                'is_active' => true,
                'sort_order' => 2,
            ],
            [
                'title' => 'Reporting Made Easy',
                'description' => 'Laporkan incident dengan mudah melalui aplikasi mobile',
                'icon' => 'mobile-alt',
                'image' => null,
                'background_color' => '#007bff', // Blue color
                'text_color' => '#ffffff',
                'is_active' => true,
                'sort_order' => 3,
            ],
            [
                'title' => 'Expert Analysis',
                'description' => 'Analisis mendalam dari tim ahli HSE berpengalaman',
                'icon' => 'chart-line',
                'image' => null,
                'background_color' => '#6f42c1', // Purple color
                'text_color' => '#ffffff',
                'is_active' => true,
                'sort_order' => 4,
            ],
            [
                'title' => 'Continuous Improvement',
                'description' => 'Komitmen perbaikan berkelanjutan untuk lingkungan kerja yang aman',
                'icon' => 'arrow-up',
                'image' => null,
                'background_color' => '#fd7e14', // Orange variant
                'text_color' => '#ffffff',
                'is_active' => true,
                'sort_order' => 5,
            ],
            [
                'title' => 'Emergency Response',
                'description' => 'Tanggap darurat 24 jam untuk situasi kritis',
                'icon' => 'exclamation-triangle',
                'image' => null,
                'background_color' => '#dc3545', // Red color
                'text_color' => '#ffffff',
                'is_active' => false, // Inactive example
                'sort_order' => 6,
            ]
        ];

        foreach ($banners as $bannerData) {
            Banner::create($bannerData);
        }

        $this->command->info('Banner seeder completed. Created ' . count($banners) . ' banners.');
    }
}
