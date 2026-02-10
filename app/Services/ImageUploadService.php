<?php
// app/Services/ImageUploadService.php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Intervention\Image\Laravel\Facades\Image;
use Exception;

class ImageUploadService
{
    /**
     * Upload and process image
     */
    public function uploadImage(UploadedFile $file, string $directory = 'images', ?int $userId = null): string
    {
        try {
            // Validate file is uploaded correctly
            if (!$file->isValid()) {
                throw new Exception('File upload error: ' . $file->getErrorMessage());
            }

            // Validate file exists and is readable
            if (!$file->isReadable()) {
                throw new Exception('File is not readable');
            }

            // Validate file size
            if ($file->getSize() === false || $file->getSize() === 0) {
                throw new Exception('File size is invalid or zero');
            }

            // Generate unique filename
            $filename = $this->generateUniqueFilename($file, $directory, $userId);

            // Validate generated filename
            if (empty($filename)) {
                throw new Exception('Generated filename is empty');
            }

            // Validate directory
            if (empty($directory)) {
                throw new Exception('Directory parameter is empty');
            }

            // Create directory if not exists
            $fullDirectory = $directory;
            if (!Storage::disk('public')->exists($fullDirectory)) {
                try {
                    Storage::disk('public')->makeDirectory($fullDirectory);
                } catch (Exception $e) {
                    throw new Exception('Gagal membuat direktori: ' . $e->getMessage());
                }
            }

            // Try multiple methods to get temp file path (Windows compatibility)
            $realPath = $file->getRealPath();
            $useDirectMove = false;

            if ($realPath === false || empty($realPath)) {
                // getRealPath() failed - need to use direct move
                $useDirectMove = true;
                $tempPath = $file->path();
                Log::warning('getRealPath() failed, trying path()', ['path' => $tempPath]);

                if ($tempPath === false || empty($tempPath)) {
                    $tempPath = $file->getPathname();
                    Log::warning('path() failed, trying getPathname()', ['pathname' => $tempPath]);
                }
            } else {
                $tempPath = $realPath;
            }

            Log::info('Attempting to store image', [
                'directory' => $fullDirectory,
                'filename' => $filename,
                'file_size' => $file->getSize(),
                'mime_type' => $file->getMimeType(),
                'real_path' => $realPath,
                'temp_path' => $tempPath,
                'path_exists' => !empty($tempPath) ? file_exists($tempPath) : false,
                'will_use_direct_move' => $useDirectMove,
                'file_class' => get_class($file),
            ]);

            // Save file info before move (temp file will be gone after move)
            $originalName = $file->getClientOriginalName();
            $fileSize = $file->getSize();
            $mimeType = $file->getMimeType();

            // Use direct move if getRealPath() failed or temp file doesn't exist
            if ($useDirectMove || empty($tempPath) || !file_exists($tempPath)) {
                Log::info('Using direct move() method for Windows compatibility');

                try {
                    // Use move() method directly - this works better on Windows
                    $destinationPath = Storage::disk('public')->path($fullDirectory);

                    // Ensure directory exists
                    if (!is_dir($destinationPath)) {
                        mkdir($destinationPath, 0755, true);
                    }

                    $fullFilePath = $destinationPath . DIRECTORY_SEPARATOR . $filename;

                    Log::info('Moving file', [
                        'from' => $tempPath,
                        'to' => $fullFilePath,
                    ]);

                    // Move uploaded file
                    $file->move($destinationPath, $filename);

                    if (!file_exists($fullFilePath)) {
                        throw new Exception('File move succeeded but file not found at destination');
                    }

                    $imagePath = $fullDirectory . '/' . $filename;

                    Log::info('File moved successfully using direct move()', [
                        'destination' => $fullFilePath,
                        'relative_path' => $imagePath,
                    ]);
                } catch (Exception $e) {
                    Log::error('Direct move failed', [
                        'error' => $e->getMessage(),
                        'trace' => $e->getTraceAsString(),
                    ]);
                    throw new Exception('Failed to move uploaded file: ' . $e->getMessage());
                }
            } else {
                // Normal storeAs method (only if getRealPath() worked)
                Log::info('Using Laravel storeAs() method');
                $imagePath = $file->storeAs($fullDirectory, $filename, 'public');
            }

            if (!$imagePath) {
                throw new Exception('Gagal menyimpan file ke storage');
            }

            // Verify file was stored
            if (!Storage::disk('public')->exists($imagePath)) {
                throw new Exception('File berhasil diupload tapi tidak ditemukan di storage');
            }

            Log::info('Image uploaded successfully', [
                'path' => $imagePath,
                'original_name' => $originalName,
                'size' => $fileSize,
                'mime_type' => $mimeType,
            ]);

            // Optimize image if it's a profile image (non-blocking)
            if ($directory === 'profile_images') {
                try {
                    $this->optimizeProfileImage($imagePath);
                } catch (Exception $e) {
                    Log::warning('Image optimization failed but upload succeeded', [
                        'path' => $imagePath,
                        'error' => $e->getMessage(),
                    ]);
                    // Don't throw, optimization is optional
                }
            }

            return $imagePath;
        } catch (Exception $e) {
            Log::error('Image upload failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'file_name' => $file->getClientOriginalName() ?? 'unknown',
                'directory' => $directory,
            ]);
            throw new Exception('Gagal mengupload gambar: ' . $e->getMessage());
        }
    }

    /**
     * Delete image from storage
     */
    public function deleteImage(string $imagePath): bool
    {
        try {
            if (Storage::disk('public')->exists($imagePath)) {
                Storage::disk('public')->delete($imagePath);

                // Also delete thumbnail if exists
                $thumbnailPath = $this->getThumbnailPath($imagePath);
                if (Storage::disk('public')->exists($thumbnailPath)) {
                    Storage::disk('public')->delete($thumbnailPath);
                }

                Log::info('Image deleted successfully', ['path' => $imagePath]);
                return true;
            }
            return false;
        } catch (Exception $e) {
            Log::error('Image deletion failed', [
                'error' => $e->getMessage(),
                'path' => $imagePath,
            ]);
            return false;
        }
    }

    /**
     * Get image URL
     */
    public function getImageUrl(string $imagePath): string
    {
        return url('storage/' . $imagePath);
    }

    /**
     * Generate unique filename
     */
    private function generateUniqueFilename(UploadedFile $file, string $directory, ?int $userId = null): string
    {
        $extension = strtolower($file->getClientOriginalExtension());
        $timestamp = now()->format('YmdHis');
        $random = substr(md5(uniqid()), 0, 8);

        $prefix = $directory === 'profile_images' ? 'profile' : 'img';
        $userPart = $userId ? "_{$userId}" : '';

        return "{$prefix}{$userPart}_{$timestamp}_{$random}.{$extension}";
    }

    /**
     * Optimize profile image
     */
    private function optimizeProfileImage(string $imagePath): void
    {
        try {
            // Check if file exists first
            if (!Storage::disk('public')->exists($imagePath)) {
                Log::warning('Image file not found for optimization', ['path' => $imagePath]);
                return;
            }

            $fullPath = Storage::disk('public')->path($imagePath);

            // Check if Intervention Image is available
            if (!class_exists('Intervention\Image\Laravel\Facades\Image')) {
                Log::info('Intervention Image not available, skipping optimization');
                return;
            }

            // Check if file is readable
            if (!is_readable($fullPath)) {
                Log::warning('Image file not readable, skipping optimization', ['path' => $fullPath]);
                return;
            }

            // Create optimized version
            $image = Image::read($fullPath);

            // Resize if too large (max 800x800 for profile images)
            if ($image->width() > 800 || $image->height() > 800) {
                $image->scale(width: 800, height: 800);
            }

            // Compress and save
            $image->toJpeg(85)->save($fullPath);

            // Create thumbnail (150x150)
            $thumbnailPath = $this->getThumbnailPath($imagePath);
            $fullThumbnailPath = Storage::disk('public')->path($thumbnailPath);

            $thumbnail = Image::read($fullPath);
            $thumbnail->cover(150, 150)->save($fullThumbnailPath);

            Log::info('Profile image optimized', [
                'original_path' => $imagePath,
                'thumbnail_path' => $thumbnailPath,
            ]);
        } catch (Exception $e) {
            Log::warning('Image optimization failed (non-critical)', [
                'path' => $imagePath,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            // Don't throw exception, optimization is optional
            // Image was already uploaded, optimization failure should not break the upload
        }
    }

    /**
     * Get thumbnail path
     */
    private function getThumbnailPath(string $imagePath): string
    {
        $pathInfo = pathinfo($imagePath);
        return $pathInfo['dirname'] . '/thumb_' . $pathInfo['basename'];
    }

    /**
     * Get image info
     */
    public function getImageInfo(string $imagePath): ?array
    {
        try {
            if (!Storage::disk('public')->exists($imagePath)) {
                return null;
            }

            $fullPath = Storage::disk('public')->path($imagePath);
            $imageInfo = getimagesize($fullPath);

            if (!$imageInfo) {
                return null;
            }

            return [
                'width' => $imageInfo[0],
                'height' => $imageInfo[1],
                'mime_type' => $imageInfo['mime'],
                'size' => Storage::disk('public')->size($imagePath),
                'url' => $this->getImageUrl($imagePath),
                'thumbnail_url' => $this->getThumbnailUrl($imagePath),
                'created_at' => Storage::disk('public')->lastModified($imagePath)
            ];
        } catch (Exception $e) {
            Log::error('Failed to get image info', [
                'path' => $imagePath,
                'error' => $e->getMessage(),
            ]);
            return null;
        }
    }

    /**
     * Get thumbnail URL
     */
    public function getThumbnailUrl(string $imagePath): ?string
    {
        $thumbnailPath = $this->getThumbnailPath($imagePath);

        if (Storage::disk('public')->exists($thumbnailPath)) {
            return url('storage/' . $thumbnailPath);
        }

        return $this->getImageUrl($imagePath); // Fallback to original
    }

    /**
     * Validate image file
     */
    public function validateImage(UploadedFile $file, array $rules = []): array
    {
        $defaultRules = [
            'max_size' => 5120, // 5MB in KB
            'allowed_types' => ['jpeg', 'jpg', 'png', 'webp'],
            'min_width' => 100,
            'min_height' => 100,
            'max_width' => 2000,
            'max_height' => 2000,
        ];

        $rules = array_merge($defaultRules, $rules);
        $errors = [];

        // Check file size
        if ($file->getSize() > ($rules['max_size'] * 1024)) {
            $errors[] = "File size exceeds {$rules['max_size']}KB limit";
        }

        // Check file type
        $extension = strtolower($file->getClientOriginalExtension());
        if (!in_array($extension, $rules['allowed_types'])) {
            $errors[] = "File type '{$extension}' is not allowed";
        }

        // Check image dimensions
        try {
            $imageInfo = getimagesize($file->getRealPath());
            if ($imageInfo) {
                $width = $imageInfo[0];
                $height = $imageInfo[1];

                if ($width < $rules['min_width'] || $height < $rules['min_height']) {
                    $errors[] = "Image dimensions too small (min: {$rules['min_width']}x{$rules['min_height']})";
                }

                if ($width > $rules['max_width'] || $height > $rules['max_height']) {
                    $errors[] = "Image dimensions too large (max: {$rules['max_width']}x{$rules['max_height']})";
                }
            }
        } catch (Exception $e) {
            $errors[] = "Cannot read image dimensions";
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors,
        ];
    }

    /**
     * Get storage info
     */
    public function getStorageInfo(): array
    {
        $disk = Storage::disk('public');
        $totalSpace = disk_total_space($disk->path(''));
        $freeSpace = disk_free_space($disk->path(''));
        $usedSpace = $totalSpace - $freeSpace;

        return [
            'total_space' => $totalSpace,
            'used_space' => $usedSpace,
            'free_space' => $freeSpace,
            'usage_percentage' => round(($usedSpace / $totalSpace) * 100, 2),
        ];
    }
}
