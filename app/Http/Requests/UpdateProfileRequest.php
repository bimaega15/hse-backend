<?php
// app/Http/Requests/UpdateProfileRequest.php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProfileRequest extends FormRequest
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
            'name' => [
                'required',
                'string',
                'max:255',
                'min:2',
                'regex:/^[a-zA-Z\s]+$/'
            ],
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('users')->ignore(auth()->id()),
            ],
            'phone' => [
                'nullable',
                'string',
                'max:20',
                'regex:/^[\+]?[0-9\-\(\)\s]+$/'
            ],
            'department' => [
                'nullable',
                'string',
                'max:100',
                'min:2'
            ],
        ];
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Nama lengkap wajib diisi.',
            'name.min' => 'Nama lengkap minimal 2 karakter.',
            'name.max' => 'Nama lengkap maksimal 255 karakter.',
            'name.regex' => 'Nama lengkap hanya boleh berisi huruf dan spasi.',

            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'email.max' => 'Email maksimal 255 karakter.',
            'email.unique' => 'Email sudah digunakan oleh user lain.',

            'phone.max' => 'Nomor telepon maksimal 20 karakter.',
            'phone.regex' => 'Format nomor telepon tidak valid.',

            'department.min' => 'Departemen minimal 2 karakter.',
            'department.max' => 'Departemen maksimal 100 karakter.',
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
            'name' => 'nama lengkap',
            'email' => 'email',
            'phone' => 'nomor telepon',
            'department' => 'departemen',
        ];
    }
}
