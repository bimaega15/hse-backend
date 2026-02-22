<?php

namespace App\Console\Commands;

use App\Models\ObservationDetail;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class MigrateObservationImages extends Command
{
    protected $signature = 'observations:migrate-images {--dry-run : Show what would be migrated without making changes}';
    protected $description = 'Migrate observation detail images from base64 database storage to disk file storage';

    public function handle()
    {
        $dryRun = $this->option('dry-run');

        if ($dryRun) {
            $this->info('DRY RUN MODE - No changes will be made.');
        }

        $details = ObservationDetail::whereNotNull('images')->get();
        $this->info("Found {$details->count()} observation details with images.");

        $migrated = 0;
        $skipped = 0;
        $failed = 0;

        foreach ($details as $detail) {
            $images = $detail->images;

            if (empty($images)) {
                $skipped++;
                continue;
            }

            // Check if already migrated (contains file paths instead of base64 objects)
            $firstImage = $images[0] ?? null;
            if (is_string($firstImage) && strpos($firstImage, 'observation_images/') === 0) {
                $this->line("  Detail #{$detail->id}: Already migrated, skipping.");
                $skipped++;
                continue;
            }

            // Check if it contains base64 data objects
            if (!is_array($firstImage) || !isset($firstImage['data'])) {
                $this->warn("  Detail #{$detail->id}: Unknown image format, skipping.");
                $skipped++;
                continue;
            }

            $this->line("  Migrating detail #{$detail->id} ({$detail->observation_id})...");

            if ($dryRun) {
                $this->line("    Would migrate " . count($images) . " image(s).");
                $migrated++;
                continue;
            }

            $newPaths = [];
            $detailFailed = false;

            foreach ($images as $index => $imageData) {
                try {
                    if (!isset($imageData['data'])) {
                        $this->warn("    Image #{$index}: No data field, skipping.");
                        continue;
                    }

                    $base64Data = $imageData['data'];

                    // Remove data:image/...;base64, prefix if present
                    if (strpos($base64Data, ',') !== false) {
                        $base64Data = explode(',', $base64Data)[1];
                    }

                    $decoded = base64_decode($base64Data);
                    if ($decoded === false) {
                        $this->error("    Image #{$index}: Invalid base64 data.");
                        $detailFailed = true;
                        continue;
                    }

                    // Determine extension from type or image data
                    $extension = 'jpg';
                    if (isset($imageData['type'])) {
                        $mimeMap = [
                            'image/jpeg' => 'jpg',
                            'image/jpg' => 'jpg',
                            'image/png' => 'png',
                            'image/gif' => 'gif',
                        ];
                        $extension = $mimeMap[$imageData['type']] ?? 'jpg';
                    }

                    $filename = 'observation_' . time() . '_' . uniqid() . '.' . $extension;
                    $path = 'observation_images/' . $filename;

                    if (Storage::disk('public')->put($path, $decoded)) {
                        $newPaths[] = $path;
                        $this->line("    Saved: {$path}");
                    } else {
                        $this->error("    Image #{$index}: Failed to save to storage.");
                        $detailFailed = true;
                    }
                } catch (\Exception $e) {
                    $this->error("    Image #{$index}: {$e->getMessage()}");
                    $detailFailed = true;
                }
            }

            if ($detailFailed && empty($newPaths)) {
                $failed++;
                continue;
            }

            // Update the database record with file paths
            $detail->images = !empty($newPaths) ? $newPaths : null;
            $detail->save();
            $migrated++;
            $this->info("    Updated detail #{$detail->id} with " . count($newPaths) . " file path(s).");
        }

        $this->newLine();
        $this->info("Migration complete:");
        $this->info("  Migrated: {$migrated}");
        $this->info("  Skipped:  {$skipped}");
        $this->info("  Failed:   {$failed}");

        return Command::SUCCESS;
    }
}
