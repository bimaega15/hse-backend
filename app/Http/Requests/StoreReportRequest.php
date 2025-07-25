<?php
// app/Http/Requests/StoreReportRequest.php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreReportRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'category' => 'required|string|in:Life Safety Equipment,Emergency Equipment,Electrical Equipment,Mechanical Equipment,Others',
            'equipment_type' => 'required|string|in:Fire Extinguisher,Emergency Light,Smoke Detector,Fire Alarm,Others',
            'contributing_factor' => 'required|string|in:Defective machinery/equipment,Life Safety Equipment,Improper procedure,Lack of maintenance,Others',
            'description' => 'required|string|max:1000',
            'location' => 'required|string|max:255',
            'images.*' => 'nullable|image|mimes:jpeg,png,jpg|max:5120'
        ];
    }

    public function messages()
    {
        return [
            'category.required' => 'Kategori harus dipilih',
            'equipment_type.required' => 'Jenis peralatan harus dipilih',
            'contributing_factor.required' => 'Faktor penyebab harus dipilih',
            'description.required' => 'Deskripsi tidak boleh kosong',
            'location.required' => 'Lokasi tidak boleh kosong',
            'images.*.image' => 'File harus berupa gambar',
            'images.*.max' => 'Ukuran gambar maksimal 5MB'
        ];
    }
}
