<?php
// app/Services/ImageUploadService.php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Facades\Image;
use Exception;

class ImageUploadService
{
    const MAX_FILE_SIZE = 5120; // 5MB in KB
    const ALLOWED_EXTENSIONS = ['jpg', 'jpeg', 'png'];
    const PROFILE_IMAGE_SIZE = 300; // pixels
    const REPORT_IMAGE_MAX_SIZE = 1920; // pixels

    /**
     * Upload profile image
     */
    public function uploadProfileImage(UploadedFile $file, int $userId): string
    {
        $this->validateImage($file);

        $filename = $this->generateFilename('profile', $userId, $file->getClientOriginalExtension());
        $path = "profile_images/{$filename}";

        // Resize and optimize image
        $image = Image::make($file)
            ->fit(self::PROFILE_IMAGE_SIZE, self::PROFILE_IMAGE_SIZE)
            ->encode('jpg', 85);

        Storage::disk('public')->put($path, $image);

        return $path;
    }

    /**
     * Upload report images
     */
    public function uploadReportImages(array $files): array
    {
        $uploadedPaths = [];

        foreach ($files as $file) {
            if ($file instanceof UploadedFile) {
                $this->validateImage($file);

                $filename = $this->generateFilename('report', null, $file->getClientOriginalExtension());
                $path = "report_images/{$filename}";

                // Resize if image is too large
                $image = Image::make($file);

                if ($image->width() > self::REPORT_IMAGE_MAX_SIZE || $image->height() > self::REPORT_IMAGE_MAX_SIZE) {
                    $image->resize(self::REPORT_IMAGE_MAX_SIZE, self::REPORT_IMAGE_MAX_SIZE, function ($constraint) {
                        $constraint->aspectRatio();
                        $constraint->upsize();
                    });
                }

                $image->encode('jpg', 90);
                Storage::disk('public')->put($path, $image);

                $uploadedPaths[] = $path;
            }
        }

        return $uploadedPaths;
    }

    /**
     * Delete image
     */
    public function deleteImage(string $path): bool
    {
        if (Storage::disk('public')->exists($path)) {
            return Storage::disk('public')->delete($path);
        }

        return false;
    }

    /**
     * Delete multiple images
     */
    public function deleteImages(array $paths): array
    {
        $results = [];

        foreach ($paths as $path) {
            $results[$path] = $this->deleteImage($path);
        }

        return $results;
    }

    /**
     * Get image URL
     */
    public function getImageUrl(string $path): string
    {
        return Storage::disk('public')->url($path);
    }

    /**
     * Validate uploaded image
     */
    private function validateImage(UploadedFile $file): void
    {
        // Check if file is valid
        if (!$file->isValid()) {
            throw new Exception('Invalid file upload');
        }

        // Check file size
        if ($file->getSize() > (self::MAX_FILE_SIZE * 1024)) {
            throw new Exception('File size exceeds maximum allowed size of ' . self::MAX_FILE_SIZE . 'KB');
        }

        // Check file extension
        $extension = strtolower($file->getClientOriginalExtension());
        if (!in_array($extension, self::ALLOWED_EXTENSIONS)) {
            throw new Exception('File type not allowed. Allowed types: ' . implode(', ', self::ALLOWED_EXTENSIONS));
        }

        // Check if file is actually an image
        $imageInfo = getimagesize($file->getPathname());
        if (!$imageInfo) {
            throw new Exception('File is not a valid image');
        }

        // Check image dimensions (minimum)
        if ($imageInfo[0] < 100 || $imageInfo[1] < 100) {
            throw new Exception('Image dimensions too small. Minimum 100x100 pixels required');
        }

        // Check MIME type
        $allowedMimes = ['image/jpeg', 'image/png', 'image/jpg'];
        if (!in_array($imageInfo['mime'], $allowedMimes)) {
            throw new Exception('Invalid image MIME type');
        }
    }

    /**
     * Generate unique filename
     */
    private function generateFilename(string $prefix, ?int $userId = null, string $extension = 'jpg'): string
    {
        $timestamp = time();
        $random = uniqid();
        $userPart = $userId ? "_{$userId}" : '';

        return "{$prefix}{$userPart}_{$timestamp}_{$random}.{$extension}";
    }

    /**
     * Get image info
     */
    public function getImageInfo(string $path): ?array
    {
        if (!Storage::disk('public')->exists($path)) {
            return null;
        }

        $fullPath = Storage::disk('public')->path($path);
        $imageInfo = getimagesize($fullPath);

        if (!$imageInfo) {
            return null;
        }

        return [
            'width' => $imageInfo[0],
            'height' => $imageInfo[1],
            'mime_type' => $imageInfo['mime'],
            'size' => Storage::disk('public')->size($path),
            'url' => $this->getImageUrl($path),
            'created_at' => Storage::disk('public')->lastModified($path)
        ];
    }

    /**
     * Create thumbnail
     */
    public function createThumbnail(string $imagePath, int $width = 150, int $height = 150): string
    {
        if (!Storage::disk('public')->exists($imagePath)) {
            throw new Exception('Original image not found');
        }

        $pathInfo = pathinfo($imagePath);
        $thumbnailPath = $pathInfo['dirname'] . '/thumb_' . $pathInfo['basename'];

        $image = Image::make(Storage::disk('public')->path($imagePath))
            ->fit($width, $height)
            ->encode('jpg', 85);

        Storage::disk('public')->put($thumbnailPath, $image);

        return $thumbnailPath;
    }

    /**
     * Bulk resize images
     */
    public function resizeImages(array $imagePaths, int $maxWidth = 1920, int $maxHeight = 1920): array
    {
        $resizedPaths = [];

        foreach ($imagePaths as $path) {
            if (Storage::disk('public')->exists($path)) {
                $image = Image::make(Storage::disk('public')->path($path));

                if ($image->width() > $maxWidth || $image->height() > $maxHeight) {
                    $image->resize($maxWidth, $maxHeight, function ($constraint) {
                        $constraint->aspectRatio();
                        $constraint->upsize();
                    });

                    $image->encode('jpg', 90);
                    Storage::disk('public')->put($path, $image);
                }

                $resizedPaths[] = $path;
            }
        }

        return $resizedPaths;
    }

    /**
     * Clean up old images (older than specified days)
     */
    public function cleanupOldImages(int $days = 30): int
    {
        $deletedCount = 0;
        $cutoffTime = now()->subDays($days)->timestamp;

        $directories = ['profile_images', 'report_images'];

        foreach ($directories as $directory) {
            $files = Storage::disk('public')->files($directory);

            foreach ($files as $file) {
                $lastModified = Storage::disk('public')->lastModified($file);

                if ($lastModified < $cutoffTime) {
                    if (Storage::disk('public')->delete($file)) {
                        $deletedCount++;
                    }
                }
            }
        }

        return $deletedCount;
    }
}
