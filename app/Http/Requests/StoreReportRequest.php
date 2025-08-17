<?php
// app/Http/Requests/StoreReportRequest.php (Updated - Base64 Support)

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;

class StoreReportRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            // Master data foreign keys
            'category_id' => 'required|exists:categories,id',
            'contributing_id' => 'required|exists:contributings,id',
            'action_id' => 'required|exists:actions,id',

            // New fields
            'severity_rating' => 'required|in:low,medium,high,critical',
            'action_taken' => 'nullable|string|max:1000',

            // Existing fields
            'description' => 'required|string|max:1000',
            'location' => 'required|string|max:255',

            // Images array validation (basic)
            'images' => 'nullable|array|max:10',
            'images.*' => 'nullable', // We'll do custom validation in after() method
        ];
    }

    public function messages()
    {
        return [
            // Master data validation messages
            'category_id.required' => 'Kategori harus dipilih',
            'category_id.exists' => 'Kategori yang dipilih tidak valid',
            'contributing_id.required' => 'Contributing factor harus dipilih',
            'contributing_id.exists' => 'Contributing factor yang dipilih tidak valid',
            'action_id.required' => 'Action harus dipilih',
            'action_id.exists' => 'Action yang dipilih tidak valid',

            // New fields validation messages
            'severity_rating.required' => 'Tingkat keparahan harus dipilih',
            'severity_rating.in' => 'Tingkat keparahan harus berupa: low, medium, high, atau critical',
            'action_taken.max' => 'Aksi yang diambil maksimal 1000 karakter',

            // Existing fields validation messages
            'description.required' => 'Deskripsi tidak boleh kosong',
            'description.max' => 'Deskripsi maksimal 1000 karakter',
            'location.required' => 'Lokasi tidak boleh kosong',
            'location.max' => 'Lokasi maksimal 255 karakter',

            // Images validation messages
            'images.array' => 'Images harus berupa array',
            'images.max' => 'Maksimal 10 gambar yang dapat diupload',
        ];
    }

    public function attributes()
    {
        return [
            'category_id' => 'kategori',
            'contributing_id' => 'contributing factor',
            'action_id' => 'action',
            'severity_rating' => 'tingkat keparahan',
            'action_taken' => 'aksi yang diambil',
            'description' => 'deskripsi',
            'location' => 'lokasi',
            'images' => 'gambar'
        ];
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator(Validator $validator)
    {
        $validator->after(function ($validator) {
            $this->validateImages($validator);
        });
    }

    /**
     * Custom validation for images (supports both file uploads and base64)
     */
    private function validateImages(Validator $validator)
    {
        $images = $this->input('images', []);

        if (!is_array($images)) {
            return;
        }

        foreach ($images as $index => $image) {
            $fieldName = "images.{$index}";

            if (is_string($image)) {
                // Validate base64 image
                if (!$this->isValidBase64Image($image)) {
                    $validator->errors()->add($fieldName, 'Gambar harus berupa base64 yang valid atau file upload');
                }
            } elseif ($image instanceof \Illuminate\Http\UploadedFile) {
                // Validate uploaded file
                if (!$image->isValid()) {
                    $validator->errors()->add($fieldName, 'File gambar tidak valid');
                    continue;
                }

                // Check file type
                $allowedMimes = ['image/jpeg', 'image/png', 'image/jpg', 'image/gif'];
                if (!in_array($image->getMimeType(), $allowedMimes)) {
                    $validator->errors()->add($fieldName, 'Gambar harus berformat: jpeg, png, jpg, atau gif');
                }

                // Check file size (5MB = 5120KB)
                if ($image->getSize() > 5242880) {
                    $validator->errors()->add($fieldName, 'Ukuran gambar maksimal 5MB');
                }
            } else {
                $validator->errors()->add($fieldName, 'Format gambar tidak valid');
            }
        }
    }

    /**
     * Validate base64 image string
     */
    private function isValidBase64Image(string $data): bool
    {
        try {
            // Remove data:image/...;base64, prefix if present
            if (strpos($data, ',') !== false) {
                $base64Data = explode(',', $data)[1];
            } else {
                $base64Data = $data;
            }

            // Check if valid base64
            $decoded = base64_decode($base64Data, true);
            if ($decoded === false) {
                return false;
            }

            // Check if it's a valid image
            $imageInfo = getimagesizefromstring($decoded);
            if ($imageInfo === false) {
                return false;
            }

            // Check mime type
            $allowedMimes = [
                'image/jpeg',
                'image/png',
                'image/gif',
                'image/jpg'
            ];

            if (!in_array($imageInfo['mime'], $allowedMimes)) {
                return false;
            }

            // Check file size (max 5MB)
            if (strlen($decoded) > 5242880) {
                return false;
            }

            return true;
        } catch (\Exception $e) {
            return false;
        }
    }
}
