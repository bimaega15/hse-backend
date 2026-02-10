<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;

class StorageDiagnostic extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'storage:diagnostic';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Run storage diagnostic to check configuration and permissions';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔍 Running Storage Diagnostic...');
        $this->newLine();

        // Check storage path
        $this->checkStoragePath();

        // Check public disk
        $this->checkPublicDisk();

        // Check symbolic link
        $this->checkSymbolicLink();

        // Check directories
        $this->checkDirectories();

        // Check permissions
        $this->checkPermissions();

        // Test file operations
        $this->testFileOperations();

        $this->newLine();
        $this->info('✅ Diagnostic completed!');

        return Command::SUCCESS;
    }

    private function checkStoragePath()
    {
        $this->info('📁 Checking storage path...');

        $storagePath = storage_path();
        $this->line("  Storage path: {$storagePath}");

        if (File::exists($storagePath)) {
            $this->line("  ✓ Storage directory exists");
        } else {
            $this->error("  ✗ Storage directory does not exist!");
        }

        $this->newLine();
    }

    private function checkPublicDisk()
    {
        $this->info('💾 Checking public disk configuration...');

        try {
            $publicPath = Storage::disk('public')->path('');
            $this->line("  Public disk path: {$publicPath}");

            if (File::exists($publicPath)) {
                $this->line("  ✓ Public disk directory exists");

                // Check if writable
                if (File::isWritable($publicPath)) {
                    $this->line("  ✓ Public disk is writable");
                } else {
                    $this->error("  ✗ Public disk is NOT writable!");
                }
            } else {
                $this->error("  ✗ Public disk directory does not exist!");
                $this->warn("  Run: php artisan storage:link");
            }
        } catch (\Exception $e) {
            $this->error("  ✗ Error: " . $e->getMessage());
        }

        $this->newLine();
    }

    private function checkSymbolicLink()
    {
        $this->info('🔗 Checking symbolic link...');

        $linkPath = public_path('storage');
        $targetPath = storage_path('app/public');

        $this->line("  Link: {$linkPath}");
        $this->line("  Target: {$targetPath}");

        if (File::exists($linkPath)) {
            if (File::isLink($linkPath)) {
                $this->line("  ✓ Symbolic link exists");

                $actualTarget = readlink($linkPath);
                if ($actualTarget === $targetPath) {
                    $this->line("  ✓ Link points to correct target");
                } else {
                    $this->warn("  ⚠ Link points to: {$actualTarget}");
                }
            } else {
                $this->warn("  ⚠ 'storage' exists but is not a symbolic link");
            }
        } else {
            $this->error("  ✗ Symbolic link does not exist!");
            $this->warn("  Run: php artisan storage:link");
        }

        $this->newLine();
    }

    private function checkDirectories()
    {
        $this->info('📂 Checking required directories...');

        $directories = [
            'profile_images',
            'report_images',
            'observation_images',
            'banner_images',
        ];

        foreach ($directories as $dir) {
            $exists = Storage::disk('public')->exists($dir);

            if ($exists) {
                $this->line("  ✓ {$dir}");
            } else {
                $this->warn("  ⚠ {$dir} (will be created on first upload)");
            }
        }

        $this->newLine();
    }

    private function checkPermissions()
    {
        $this->info('🔐 Checking permissions...');

        $storagePath = storage_path('app/public');

        if (File::exists($storagePath)) {
            $perms = substr(sprintf('%o', fileperms($storagePath)), -4);
            $this->line("  Storage permissions: {$perms}");

            if (File::isWritable($storagePath)) {
                $this->line("  ✓ Storage is writable");
            } else {
                $this->error("  ✗ Storage is NOT writable!");
                $this->warn("  Fix with: chmod -R 775 storage");
            }
        }

        $this->newLine();
    }

    private function testFileOperations()
    {
        $this->info('🧪 Testing file operations...');

        try {
            $testFile = 'test_' . time() . '.txt';
            $testContent = 'Test file created at ' . now();

            // Test write
            Storage::disk('public')->put($testFile, $testContent);
            $this->line("  ✓ Write test passed");

            // Test read
            $content = Storage::disk('public')->get($testFile);
            if ($content === $testContent) {
                $this->line("  ✓ Read test passed");
            } else {
                $this->error("  ✗ Read test failed - content mismatch");
            }

            // Test delete
            Storage::disk('public')->delete($testFile);
            if (!Storage::disk('public')->exists($testFile)) {
                $this->line("  ✓ Delete test passed");
            } else {
                $this->error("  ✗ Delete test failed");
            }

            $this->line("  ✓ All file operations successful");
        } catch (\Exception $e) {
            $this->error("  ✗ File operation failed: " . $e->getMessage());
        }

        $this->newLine();
    }
}
