<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            // Seed master data first
            MasterDataSeeder::class,

            // Seed banners (new)
            BannerSeeder::class,

            // Then seed users
            UserSeeder::class,

            // Finally seed reports (depends on users and master data)
            ReportSeeder::class,

            ObservationSeeder::class,
        ]);
    }
}
