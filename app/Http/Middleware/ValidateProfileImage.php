<?php
// app/Http/Middleware/ValidateProfileImage.php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\Response;

class ValidateProfileImage
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Only validate if profile_image is present
        if ($request->hasFile('profile_image')) {
            $validator = Validator::make($request->all(), [
                'profile_image' => [
                    'required',
                    'image',
                    'mimes:jpeg,png,jpg,webp',
                    'max:' . config('app.max_file_size', 5120), // Default 5MB
                    'dimensions:min_width=100,min_height=100,max_width=2000,max_height=2000'
                ]
            ], [
                'profile_image.required' => 'Foto profil harus dipilih.',
                'profile_image.image' => 'File harus berupa gambar.',
                'profile_image.mimes' => 'Format file harus jpeg, png, jpg, atau webp.',
                'profile_image.max' => 'Ukuran file maksimal ' . (config('app.max_file_size', 5120) / 1024) . 'MB.',
                'profile_image.dimensions' => 'Ukuran gambar minimal 100x100 pixel dan maksimal 2000x2000 pixel.',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation Error',
                    'errors' => $validator->errors(),
                ], 422);
            }

            // Additional custom validation
            $file = $request->file('profile_image');

            // Check file size more strictly
            $maxSizeBytes = config('app.max_file_size', 5120) * 1024;
            if ($file->getSize() > $maxSizeBytes) {
                return response()->json([
                    'success' => false,
                    'message' => 'File terlalu besar',
                    'errors' => [
                        'profile_image' => ['Ukuran file melebihi batas maksimal ' . (config('app.max_file_size', 5120) / 1024) . 'MB']
                    ],
                ], 422);
            }

            // Check if file is actually an image
            try {
                $imageInfo = getimagesize($file->getRealPath());
                if (!$imageInfo) {
                    return response()->json([
                        'success' => false,
                        'message' => 'File bukan gambar yang valid',
                        'errors' => [
                            'profile_image' => ['File yang diupload bukan gambar yang valid']
                        ],
                    ], 422);
                }
            } catch (\Exception $e) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tidak dapat memproses file gambar',
                    'errors' => [
                        'profile_image' => ['File tidak dapat diproses sebagai gambar']
                    ],
                ], 422);
            }

            // Check disk space (optional)
            $freeSpace = disk_free_space(storage_path('app/public'));
            if ($freeSpace < ($file->getSize() * 2)) { // Ensure 2x file size is available
                return response()->json([
                    'success' => false,
                    'message' => 'Tidak cukup ruang penyimpanan',
                    'errors' => [
                        'profile_image' => ['Server tidak memiliki cukup ruang untuk menyimpan file']
                    ],
                ], 507); // Insufficient Storage
            }
        }

        return $next($request);
    }
}
