<?php
// app/Http/Requests/ProfileImageRequest.php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ProfileImageRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->check();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'profile_image' => [
                'required',
                'image',
                'mimes:jpeg,png,jpg,webp',
                'max:5120', // 5MB
                'dimensions:min_width=100,min_height=100,max_width=2000,max_height=2000'
            ]
        ];
    }

    /**
     * Get custom error messages for validation rules.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'profile_image.required' => 'Foto profil harus dipilih.',
            'profile_image.image' => 'File harus berupa gambar.',
            'profile_image.mimes' => 'Format file harus jpeg, png, jpg, atau webp.',
            'profile_image.max' => 'Ukuran file maksimal 5MB.',
            'profile_image.dimensions' => 'Ukuran gambar minimal 100x100 pixel dan maksimal 2000x2000 pixel.',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'profile_image' => 'foto profil',
        ];
    }
}
