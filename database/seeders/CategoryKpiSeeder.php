<?php
// database/seeders/CategoryKpiSeeder.php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\CategoryKpi;

class CategoryKpiSeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            'Lagging Indicator',
            'Leading Indicator',
        ];

        foreach ($categories as $name) {
            CategoryKpi::firstOrCreate(['category_name' => $name], ['status' => 'active']);
        }

        $this->command?->info('CategoryKpiSeeder: ' . count($categories) . ' categories seeded.');
    }
}
