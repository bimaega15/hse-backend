<?php
// app/Helpers/ImageHelper.php

namespace App\Helpers;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Exception;

class ImageHelper
{
    /**
     * Validate image file
     */
    public static function validateImage(UploadedFile $file, array $rules = []): array
    {
        $defaultRules = [
            'max_size' => config('image.upload.max_size', 5120), // KB
            'allowed_formats' => config('image.upload.allowed_formats', ['jpeg', 'jpg', 'png', 'webp']),
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

        // Check file extension
        $extension = strtolower($file->getClientOriginalExtension());
        if (!in_array($extension, $rules['allowed_formats'])) {
            $errors[] = "File type '{$extension}' is not allowed";
        }

        // Check MIME type
        $allowedMimes = config('image.security.allowed_mime_types', [
            'image/jpeg',
            'image/png',
            'image/webp'
        ]);

        if (!in_array($file->getMimeType(), $allowedMimes)) {
            $errors[] = "MIME type '{$file->getMimeType()}' is not allowed";
        }

        // Check if file is actually an image
        try {
            $imageInfo = getimagesize($file->getRealPath());
            if (!$imageInfo) {
                $errors[] = "File is not a valid image";
            } else {
                $width = $imageInfo[0];
                $height = $imageInfo[1];

                // Check dimensions
                if ($width < $rules['min_width'] || $height < $rules['min_height']) {
                    $errors[] = "Image dimensions too small (min: {$rules['min_width']}x{$rules['min_height']})";
                }

                if ($width > $rules['max_width'] || $height > $rules['max_height']) {
                    $errors[] = "Image dimensions too large (max: {$rules['max_width']}x{$rules['max_height']})";
                }
            }
        } catch (Exception $e) {
            $errors[] = "Cannot read image properties";
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors,
            'info' => $imageInfo ?? null,
        ];
    }

    /**
     * Generate unique filename
     */
    public static function generateFilename(string $prefix = 'img', string $extension = 'jpg', ?int $userId = null): string
    {
        $timestamp = now()->format('YmdHis');
        $random = substr(md5(uniqid()), 0, 8);
        $userPart = $userId ? "_{$userId}" : '';

        return "{$prefix}{$userPart}_{$timestamp}_{$random}.{$extension}";
    }

    /**
     * Get image dimensions
     */
    public static function getImageDimensions(string $imagePath): ?array
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
                'type' => $imageInfo[2],
                'mime' => $imageInfo['mime'],
                'bits' => $imageInfo['bits'] ?? null,
                'channels' => $imageInfo['channels'] ?? null,
            ];
        } catch (Exception $e) {
            Log::error('Failed to get image dimensions', [
                'path' => $imagePath,
                'error' => $e->getMessage(),
            ]);
            return null;
        }
    }

    /**
     * Calculate image file size in human readable format
     */
    public static function formatFileSize(int $bytes, int $precision = 2): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];

        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }

        return round($bytes, $precision) . ' ' . $units[$i];
    }

    /**
     * Get storage disk usage information
     */
    public static function getStorageInfo(): array
    {
        $disk = Storage::disk('public');
        $path = $disk->path('');

        $totalSpace = disk_total_space($path);
        $freeSpace = disk_free_space($path);
        $usedSpace = $totalSpace - $freeSpace;

        return [
            'total_space' => $totalSpace,
            'used_space' => $usedSpace,
            'free_space' => $freeSpace,
            'total_formatted' => self::formatFileSize($totalSpace),
            'used_formatted' => self::formatFileSize($usedSpace),
            'free_formatted' => self::formatFileSize($freeSpace),
            'usage_percentage' => round(($usedSpace / $totalSpace) * 100, 2),
        ];
    }

    /**
     * Check if storage has enough space for upload
     */
    public static function hasEnoughSpace(int $requiredBytes, float $bufferMultiplier = 2.0): bool
    {
        $storageInfo = self::getStorageInfo();
        $requiredSpaceWithBuffer = $requiredBytes * $bufferMultiplier;

        return $storageInfo['free_space'] >= $requiredSpaceWithBuffer;
    }

    /**
     * Get image URL with fallback
     */
    public static function getImageUrl(?string $imagePath, ?string $fallbackUrl = null): ?string
    {
        if (!$imagePath) {
            return $fallbackUrl;
        }

        // Check if CDN is enabled
        if (config('image.cdn.enabled', false)) {
            $cdnUrl = config('image.cdn.base_url');
            if ($cdnUrl) {
                return rtrim($cdnUrl, '/') . '/' . ltrim($imagePath, '/');
            }
        }

        // Check if file exists
        if (!Storage::disk('public')->exists($imagePath)) {
            return $fallbackUrl;
        }

        return url('storage/' . $imagePath);
    }

    /**
     * Get thumbnail URL
     */
    public static function getThumbnailUrl(string $imagePath): ?string
    {
        $pathInfo = pathinfo($imagePath);
        $thumbnailPath = $pathInfo['dirname'] . '/thumb_' . $pathInfo['basename'];

        return self::getImageUrl($thumbnailPath, self::getImageUrl($imagePath));
    }

    /**
     * Clean filename from special characters
     */
    public static function sanitizeFilename(string $filename): string
    {
        // Remove special characters
        $filename = preg_replace('/[^a-zA-Z0-9._-]/', '_', $filename);

        // Remove multiple underscores
        $filename = preg_replace('/_+/', '_', $filename);

        // Remove leading/trailing underscores
        $filename = trim($filename, '_');

        return $filename;
    }

    /**
     * Check if file is an image by content
     */
    public static function isImageByContent(string $filePath): bool
    {
        try {
            $imageInfo = getimagesize($filePath);
            return $imageInfo !== false;
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Get EXIF data from image
     */
    public static function getExifData(string $imagePath): ?array
    {
        try {
            if (!Storage::disk('public')->exists($imagePath)) {
                return null;
            }

            $fullPath = Storage::disk('public')->path($imagePath);

            if (!function_exists('exif_read_data')) {
                return null;
            }

            $exif = exif_read_data($fullPath);
            return $exif ?: null;
        } catch (Exception $e) {
            Log::warning('Failed to read EXIF data', [
                'path' => $imagePath,
                'error' => $e->getMessage(),
            ]);
            return null;
        }
    }

    /**
     * Strip EXIF data from image
     */
    public static function stripExifData(string $imagePath): bool
    {
        try {
            if (!Storage::disk('public')->exists($imagePath)) {
                return false;
            }

            $fullPath = Storage::disk('public')->path($imagePath);
            $imageInfo = getimagesize($fullPath);

            if (!$imageInfo) {
                return false;
            }

            // Create image resource
            switch ($imageInfo[2]) {
                case IMAGETYPE_JPEG:
                    $image = imagecreatefromjpeg($fullPath);
                    if ($image) {
                        imagejpeg($image, $fullPath, config('image.upload.quality', 85));
                        imagedestroy($image);
                        return true;
                    }
                    break;

                case IMAGETYPE_PNG:
                    $image = imagecreatefrompng($fullPath);
                    if ($image) {
                        imagepng($image, $fullPath);
                        imagedestroy($image);
                        return true;
                    }
                    break;
            }

            return false;
        } catch (Exception $e) {
            Log::error('Failed to strip EXIF data', [
                'path' => $imagePath,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * Detect if image has transparency
     */
    public static function hasTransparency(string $imagePath): bool
    {
        try {
            if (!Storage::disk('public')->exists($imagePath)) {
                return false;
            }

            $fullPath = Storage::disk('public')->path($imagePath);
            $imageInfo = getimagesize($fullPath);

            if (!$imageInfo) {
                return false;
            }

            // PNG can have transparency
            if ($imageInfo[2] === IMAGETYPE_PNG) {
                $image = imagecreatefrompng($fullPath);
                if ($image) {
                    $hasTransparency = (imagecolortransparent($image) >= 0) ||
                        (imageistruecolor($image) &&
                            (imagecolorat($image, 0, 0) & 0x7F000000) >> 24);
                    imagedestroy($image);
                    return $hasTransparency;
                }
            }

            return false;
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Get image aspect ratio
     */
    public static function getAspectRatio(string $imagePath): ?float
    {
        $dimensions = self::getImageDimensions($imagePath);

        if (!$dimensions || $dimensions['height'] == 0) {
            return null;
        }

        return round($dimensions['width'] / $dimensions['height'], 2);
    }

    /**
     * Check if image is square
     */
    public static function isSquare(string $imagePath): bool
    {
        $dimensions = self::getImageDimensions($imagePath);

        if (!$dimensions) {
            return false;
        }

        return $dimensions['width'] === $dimensions['height'];
    }

    /**
     * Generate cache key for image
     */
    public static function generateCacheKey(string $imagePath, array $params = []): string
    {
        $key = 'image_' . md5($imagePath . serialize($params));
        return $key;
    }
}
