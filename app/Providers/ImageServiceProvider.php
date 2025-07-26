<?php
// app/Providers/ImageServiceProvider.php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\ImageUploadService;
use Illuminate\Support\Facades\Storage;
use Illuminate\Console\Scheduling\Schedule;

class ImageServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // Register ImageUploadService as singleton
        $this->app->singleton(ImageUploadService::class, function ($app) {
            return new ImageUploadService();
        });

        // Register image configuration
        $this->mergeConfigFrom(
            __DIR__ . '/../../config/image.php',
            'image'
        );
    }

    /**
     * Bootstrap services.
     */
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

        // Create storage directories if they don't exist
        $this->createStorageDirectories();

        // Schedule automatic image cleanup
        $this->scheduleImageCleanup();

        // Set up image processing limits
        $this->configureImageProcessing();
    }

    /**
     * Create required storage directories
     */
    private function createStorageDirectories(): void
    {
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
    }

    /**
     * Schedule automatic image cleanup
     */
    private function scheduleImageCleanup(): void
    {
        $this->app->booted(function () {
            $schedule = $this->app->make(Schedule::class);

            if (config('image.cleanup.auto_cleanup', true)) {
                // Run cleanup daily at 2 AM
                $schedule->command('images:cleanup --force --temp-only')
                    ->dailyAt('02:00')
                    ->withoutOverlapping()
                    ->runInBackground();

                // Run orphaned files cleanup weekly
                $schedule->command('images:cleanup --force --orphaned-only')
                    ->weeklyOn(1, '03:00') // Monday at 3 AM
                    ->withoutOverlapping()
                    ->runInBackground();
            }
        });
    }

    /**
     * Configure image processing limits
     */
    private function configureImageProcessing(): void
    {
        // Set memory limit for image processing
        $memoryLimit = config('image.performance.memory_limit', '256M');
        if ($memoryLimit) {
            ini_set('memory_limit', $memoryLimit);
        }

        // Set max execution time for image processing
        $maxExecutionTime = config('image.performance.max_execution_time', 120);
        if ($maxExecutionTime) {
            ini_set('max_execution_time', $maxExecutionTime);
        }
    }

    /**
     * Get the services provided by the provider.
     */
    public function provides(): array
    {
        return [
            ImageUploadService::class,
        ];
    }
}
