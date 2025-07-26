<?php
// app/Http/Requests/UpdateReportRequest.php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateReportRequest extends FormRequest
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

            // Report fields
            'severity_rating' => 'required|in:low,medium,high,critical',
            'action_taken' => 'nullable|string|max:1000',
            'description' => 'required|string|max:1000',
            'location' => 'required|string|max:255',
            'images.*' => 'nullable|image|mimes:jpeg,png,jpg|max:5120'
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

            // Report fields validation messages
            'severity_rating.required' => 'Tingkat keparahan harus dipilih',
            'severity_rating.in' => 'Tingkat keparahan harus berupa: low, medium, high, atau critical',
            'action_taken.max' => 'Aksi yang diambil maksimal 1000 karakter',
            'description.required' => 'Deskripsi tidak boleh kosong',
            'description.max' => 'Deskripsi maksimal 1000 karakter',
            'location.required' => 'Lokasi tidak boleh kosong',
            'location.max' => 'Lokasi maksimal 255 karakter',
            'images.*.image' => 'File harus berupa gambar',
            'images.*.mimes' => 'File gambar harus berformat: jpeg, png, jpg',
            'images.*.max' => 'Ukuran gambar maksimal 5MB'
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
            'images.*' => 'gambar'
        ];
    }
}
