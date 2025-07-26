<?php
// app/Console/Commands/CleanupImages.php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use Carbon\Carbon;

class CleanupImages extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'images:cleanup 
                            {--dry-run : Show what would be deleted without actually deleting}
                            {--force : Force cleanup without confirmation}
                            {--temp-only : Only cleanup temporary files}
                            {--orphaned-only : Only cleanup orphaned files}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Cleanup unused and temporary images from storage';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $isDryRun = $this->option('dry-run');
        $isForce = $this->option('force');
        $tempOnly = $this->option('temp-only');
        $orphanedOnly = $this->option('orphaned-only');

        $this->info('Starting image cleanup process...');
        $this->newLine();

        $stats = [
            'temp_files_deleted' => 0,
            'orphaned_files_deleted' => 0,
            'space_freed' => 0,
        ];

        // Cleanup temporary files if not orphaned-only
        if (!$orphanedOnly) {
            $tempStats = $this->cleanupTempFiles($isDryRun);
            $stats['temp_files_deleted'] = $tempStats['count'];
            $stats['space_freed'] += $tempStats['size'];
        }

        // Cleanup orphaned files if not temp-only
        if (!$tempOnly) {
            $orphanedStats = $this->cleanupOrphanedFiles($isDryRun);
            $stats['orphaned_files_deleted'] = $orphanedStats['count'];
            $stats['space_freed'] += $orphanedStats['size'];
        }

        $this->newLine();
        $this->displayStats($stats, $isDryRun);

        if (!$isDryRun && !$isForce && ($stats['temp_files_deleted'] > 0 || $stats['orphaned_files_deleted'] > 0)) {
            if (!$this->confirm('Do you want to proceed with the cleanup?')) {
                $this->info('Cleanup cancelled.');
                return;
            }
        }

        if ($isDryRun) {
            $this->info('Dry run completed. No files were actually deleted.');
        } else {
            $this->info('Image cleanup completed successfully!');
        }
    }

    /**
     * Cleanup temporary files older than 24 hours
     */
    private function cleanupTempFiles(bool $isDryRun): array
    {
        $this->info('🧹 Checking temporary files...');

        $tempPath = config('image.paths.temp', 'temp');
        $maxAge = config('image.cleanup.temp_files_after', 24); // hours
        $cutoffTime = Carbon::now()->subHours($maxAge);

        $count = 0;
        $totalSize = 0;

        if (!Storage::disk('public')->exists($tempPath)) {
            $this->info('   No temporary directory found.');
            return ['count' => 0, 'size' => 0];
        }

        $files = Storage::disk('public')->allFiles($tempPath);

        foreach ($files as $file) {
            $lastModified = Carbon::createFromTimestamp(
                Storage::disk('public')->lastModified($file)
            );

            if ($lastModified->lt($cutoffTime)) {
                $fileSize = Storage::disk('public')->size($file);
                $totalSize += $fileSize;

                if (!$isDryRun) {
                    Storage::disk('public')->delete($file);
                }

                $count++;
                $this->line("   - {$file} (" . $this->formatBytes($fileSize) . ")");
            }
        }

        if ($count === 0) {
            $this->info('   No old temporary files found.');
        } else {
            $this->info("   Found {$count} temporary files to cleanup.");
        }

        return ['count' => $count, 'size' => $totalSize];
    }

    /**
     * Cleanup orphaned image files
     */
    private function cleanupOrphanedFiles(bool $isDryRun): array
    {
        $this->info('🔍 Checking orphaned files...');

        $count = 0;
        $totalSize = 0;

        // Get all profile images from storage
        $profileImagesPath = config('image.paths.profile_images', 'profile_images');

        if (!Storage::disk('public')->exists($profileImagesPath)) {
            $this->info('   No profile images directory found.');
            return ['count' => 0, 'size' => 0];
        }

        $allFiles = collect(Storage::disk('public')->allFiles($profileImagesPath));

        // Get all profile images referenced in database
        $usedImages = User::whereNotNull('profile_image')
            ->pluck('profile_image')
            ->toArray();

        // Find orphaned files
        $orphanedFiles = $allFiles->filter(function ($file) use ($usedImages) {
            return !in_array($file, $usedImages);
        });

        foreach ($orphanedFiles as $file) {
            $fileSize = Storage::disk('public')->size($file);
            $totalSize += $fileSize;

            if (!$isDryRun) {
                Storage::disk('public')->delete($file);

                // Also delete thumbnail if exists
                $thumbnailPath = $this->getThumbnailPath($file);
                if (Storage::disk('public')->exists($thumbnailPath)) {
                    Storage::disk('public')->delete($thumbnailPath);
                }
            }

            $count++;
            $this->line("   - {$file} (" . $this->formatBytes($fileSize) . ")");
        }

        if ($count === 0) {
            $this->info('   No orphaned files found.');
        } else {
            $this->info("   Found {$count} orphaned files to cleanup.");
        }

        return ['count' => $count, 'size' => $totalSize];
    }

    /**
     * Get thumbnail path for an image
     */
    private function getThumbnailPath(string $imagePath): string
    {
        $pathInfo = pathinfo($imagePath);
        return $pathInfo['dirname'] . '/thumb_' . $pathInfo['basename'];
    }

    /**
     * Display cleanup statistics
     */
    private function displayStats(array $stats, bool $isDryRun): void
    {
        $this->info('📊 Cleanup Summary:');
        $this->table(
            ['Category', 'Files', 'Space'],
            [
                ['Temporary Files', $stats['temp_files_deleted'], $this->formatBytes($stats['space_freed'])],
                ['Orphaned Files', $stats['orphaned_files_deleted'], ''],
                ['Total', $stats['temp_files_deleted'] + $stats['orphaned_files_deleted'], $this->formatBytes($stats['space_freed'])],
            ]
        );

        if ($isDryRun) {
            $this->warn('This was a dry run. No files were actually deleted.');
        }
    }

    /**
     * Format bytes to human readable format
     */
    private function formatBytes(int $bytes, int $precision = 2): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];

        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }

        return round($bytes, $precision) . ' ' . $units[$i];
    }

    /**
     * Get storage disk info
     */
    private function getStorageInfo(): array
    {
        $disk = Storage::disk('public');
        $path = $disk->path('');

        $totalSpace = disk_total_space($path);
        $freeSpace = disk_free_space($path);
        $usedSpace = $totalSpace - $freeSpace;

        return [
            'total' => $totalSpace,
            'used' => $usedSpace,
            'free' => $freeSpace,
            'usage_percentage' => round(($usedSpace / $totalSpace) * 100, 2),
        ];
    }
}
