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
            // Generate unique filename
            $filename = $this->generateUniqueFilename($file, $directory, $userId);

            // Create directory if not exists
            $fullDirectory = $directory;
            if (!Storage::disk('public')->exists($fullDirectory)) {
                Storage::disk('public')->makeDirectory($fullDirectory);
            }

            // Store original image
            $imagePath = $file->storeAs($fullDirectory, $filename, 'public');

            // Optimize image if it's a profile image
            if ($directory === 'profile_images') {
                $this->optimizeProfileImage($imagePath);
            }

            Log::info('Image uploaded successfully', [
                'path' => $imagePath,
                'original_name' => $file->getClientOriginalName(),
                'size' => $file->getSize(),
                'mime_type' => $file->getMimeType(),
            ]);

            return $imagePath;
        } catch (Exception $e) {
            Log::error('Image upload failed', [
                'error' => $e->getMessage(),
                'file_name' => $file->getClientOriginalName(),
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
            $fullPath = Storage::disk('public')->path($imagePath);

            // Check if Intervention Image is available
            if (!class_exists('Intervention\Image\Laravel\Facades\Image')) {
                Log::info('Intervention Image not available, skipping optimization');
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
            Log::warning('Image optimization failed', [
                'path' => $imagePath,
                'error' => $e->getMessage(),
            ]);
            // Don't throw exception, optimization is optional
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
