<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Storage;
use Illuminate\Console\Scheduling\Schedule;

class ImageServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../../config/image.php',
            'image'
        );
    }

    public function boot(): void
    {
        // Publish configuration file
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../../config/image.php' => config_path('image.php'),
            ], 'image-config');
        }

        // Register commands
        if ($this->app->runningInConsole()) {
            $this->commands([
                \App\Console\Commands\CleanupImages::class,
            ]);
        }

        // Only run these in web/API requests, not console commands
        if (!$this->app->runningInConsole()) {
            $this->createStorageDirectories();
            $this->configureImageProcessing();
        }

        // Schedule automatic image cleanup only in production
        if ($this->app->environment('production')) {
            $this->scheduleImageCleanup();
        }
    }

    private function createStorageDirectories(): void
    {
        try {
            $directories = [
                config('image.paths.profile_images', 'profile_images'),
                config('image.paths.report_images', 'report_images'),
                config('image.paths.thumbnails', 'thumbnails'),
                config('image.paths.temp', 'temp'),
            ];

            foreach ($directories as $directory) {
                if (!Storage::disk('public')->exists($directory)) {
                    Storage::disk('public')->makeDirectory($directory);
                }
            }
        } catch (\Exception $e) {
            // Log error but don't break the application
            \Log::warning('Failed to create image directories: ' . $e->getMessage());
        }
    }

    private function scheduleImageCleanup(): void
    {
        $this->app->booted(function () {
            $schedule = $this->app->make(Schedule::class);

            if (config('image.cleanup.auto_cleanup', true)) {
                $schedule->command('images:cleanup --force --temp-only')
                    ->dailyAt('02:00')
                    ->withoutOverlapping()
                    ->runInBackground();

                $schedule->command('images:cleanup --force --orphaned-only')
                    ->weeklyOn(1, '03:00')
                    ->withoutOverlapping()
                    ->runInBackground();
            }
        });
    }

    private function configureImageProcessing(): void
    {
        // Set memory limit for image processing
        $memoryLimit = config('image.performance.memory_limit', '512M');
        $currentLimit = ini_get('memory_limit');

        // Only increase memory limit if current is lower
        if ($memoryLimit && $this->compareMemoryLimits($currentLimit, $memoryLimit) < 0) {
            ini_set('memory_limit', $memoryLimit);
        }

        // Set max execution time for image processing (but not for serve command)
        if (php_sapi_name() !== 'cli-server') {
            $maxExecutionTime = config('image.performance.max_execution_time', 300);
            if ($maxExecutionTime && $maxExecutionTime > ini_get('max_execution_time')) {
                ini_set('max_execution_time', $maxExecutionTime);
            }
        }
    }

    private function compareMemoryLimits($limit1, $limit2): int
    {
        $convert = function ($limit) {
            $limit = strtolower(trim($limit));
            $value = (int)$limit;

            switch (substr($limit, -1)) {
                case 'g':
                    $value *= 1024;
                case 'm':
                    $value *= 1024;
                case 'k':
                    $value *= 1024;
            }

            return $value;
        };

        return $convert($limit1) <=> $convert($limit2);
    }

    public function provides(): array
    {
        return [];
    }
}
