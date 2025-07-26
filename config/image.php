<?php
// config/image.php

return [

    /*
    |--------------------------------------------------------------------------
    | Default Image Settings
    |--------------------------------------------------------------------------
    |
    | These settings control the default behavior for image uploads and
    | processing throughout the application.
    |
    */

    'default_disk' => env('IMAGE_DISK', 'public'),

    /*
    |--------------------------------------------------------------------------
    | File Upload Limits
    |--------------------------------------------------------------------------
    |
    | Configure the maximum file size and allowed formats for image uploads.
    | Sizes are in kilobytes.
    |
    */

    'upload' => [
        'max_size' => env('IMAGE_MAX_SIZE', 5120), // 5MB in KB
        'allowed_formats' => ['jpeg', 'jpg', 'png', 'webp'],
        'quality' => env('IMAGE_QUALITY', 85), // JPEG compression quality
    ],

    /*
    |--------------------------------------------------------------------------
    | Image Dimensions
    |--------------------------------------------------------------------------
    |
    | Define minimum and maximum dimensions for uploaded images.
    |
    */

    'dimensions' => [
        'profile' => [
            'min_width' => 100,
            'min_height' => 100,
            'max_width' => 2000,
            'max_height' => 2000,
            'optimize_width' => 800,
            'optimize_height' => 800,
        ],
        'thumbnail' => [
            'width' => 150,
            'height' => 150,
        ],
        'report' => [
            'min_width' => 200,
            'min_height' => 200,
            'max_width' => 4000,
            'max_height' => 4000,
            'optimize_width' => 1200,
            'optimize_height' => 1200,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Storage Paths
    |--------------------------------------------------------------------------
    |
    | Define where different types of images should be stored.
    |
    */

    'paths' => [
        'profile_images' => 'profile_images',
        'report_images' => 'report_images',
        'thumbnails' => 'thumbnails',
        'temp' => 'temp',
    ],

    /*
    |--------------------------------------------------------------------------
    | Image Processing
    |--------------------------------------------------------------------------
    |
    | Configure image processing options like optimization, thumbnails, etc.
    |
    */

    'processing' => [
        'auto_optimize' => env('IMAGE_AUTO_OPTIMIZE', true),
        'create_thumbnails' => env('IMAGE_CREATE_THUMBNAILS', true),
        'preserve_exif' => env('IMAGE_PRESERVE_EXIF', false),
        'strip_metadata' => env('IMAGE_STRIP_METADATA', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | CDN Settings
    |--------------------------------------------------------------------------
    |
    | If you're using a CDN for image delivery, configure the settings here.
    |
    */

    'cdn' => [
        'enabled' => env('IMAGE_CDN_ENABLED', false),
        'base_url' => env('IMAGE_CDN_URL', ''),
        'cache_duration' => env('IMAGE_CDN_CACHE', 86400), // 24 hours
    ],

    /*
    |--------------------------------------------------------------------------
    | Security Settings
    |--------------------------------------------------------------------------
    |
    | Security-related settings for image uploads.
    |
    */

    'security' => [
        'scan_uploads' => env('IMAGE_SCAN_UPLOADS', true),
        'allowed_mime_types' => [
            'image/jpeg',
            'image/png',
            'image/webp',
        ],
        'check_real_mime' => env('IMAGE_CHECK_REAL_MIME', true),
        'prevent_svg' => env('IMAGE_PREVENT_SVG', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | Cleanup Settings
    |--------------------------------------------------------------------------
    |
    | Configure automatic cleanup of old or unused images.
    |
    */

    'cleanup' => [
        'temp_files_after' => 24, // hours
        'orphaned_files_after' => 7, // days
        'auto_cleanup' => env('IMAGE_AUTO_CLEANUP', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | Error Messages
    |--------------------------------------------------------------------------
    |
    | Custom error messages for image validation.
    |
    */

    'messages' => [
        'invalid_format' => 'Format file tidak didukung. Gunakan JPEG, PNG, atau WebP.',
        'file_too_large' => 'Ukuran file terlalu besar. Maksimal :max MB.',
        'dimensions_too_small' => 'Ukuran gambar terlalu kecil. Minimal :width x :height pixel.',
        'dimensions_too_large' => 'Ukuran gambar terlalu besar. Maksimal :width x :height pixel.',
        'upload_failed' => 'Gagal mengupload gambar. Silakan coba lagi.',
        'processing_failed' => 'Gagal memproses gambar.',
        'not_an_image' => 'File yang dipilih bukan gambar yang valid.',
    ],

    /*
    |--------------------------------------------------------------------------
    | Watermark Settings
    |--------------------------------------------------------------------------
    |
    | Configure watermark settings if needed.
    |
    */

    'watermark' => [
        'enabled' => env('IMAGE_WATERMARK_ENABLED', false),
        'text' => env('IMAGE_WATERMARK_TEXT', ''),
        'position' => env('IMAGE_WATERMARK_POSITION', 'bottom-right'),
        'opacity' => env('IMAGE_WATERMARK_OPACITY', 50),
        'font_size' => env('IMAGE_WATERMARK_FONT_SIZE', 12),
    ],

    /*
    |--------------------------------------------------------------------------
    | Performance Settings
    |--------------------------------------------------------------------------
    |
    | Settings to optimize image processing performance.
    |
    */

    'performance' => [
        'memory_limit' => env('IMAGE_MEMORY_LIMIT', '256M'),
        'max_execution_time' => env('IMAGE_MAX_EXECUTION_TIME', 120),
        'progressive_jpeg' => env('IMAGE_PROGRESSIVE_JPEG', true),
        'optimization_level' => env('IMAGE_OPTIMIZATION_LEVEL', 'medium'), // low, medium, high
    ],

];
