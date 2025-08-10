<?php
// app/Http/Requests/ChangePasswordRequest.php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Hash;

class ChangePasswordRequest extends FormRequest
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
            'current_password' => [
                'required',
                'string',
                function ($attribute, $value, $fail) {
                    if (!Hash::check($value, auth()->user()->password)) {
                        $fail('Password saat ini tidak sesuai.');
                    }
                },
            ],
            'new_password' => [
                'required',
                'string',
                'min:6',
                'confirmed',
                'different:current_password',
                'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]/'
            ],
            'new_password_confirmation' => [
                'required',
                'string',
                'min:6'
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
            'current_password.required' => 'Password saat ini wajib diisi.',
            'new_password.required' => 'Password baru wajib diisi.',
            'new_password.min' => 'Password baru minimal 6 karakter.',
            'new_password.confirmed' => 'Konfirmasi password baru tidak cocok.',
            'new_password.different' => 'Password baru harus berbeda dari password saat ini.',
            'new_password.regex' => 'Password baru harus mengandung minimal 1 huruf kecil, 1 huruf besar, 1 angka, dan 1 karakter khusus.',
            'new_password_confirmation.required' => 'Konfirmasi password baru wajib diisi.',
            'new_password_confirmation.min' => 'Konfirmasi password baru minimal 6 karakter.',
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
            'current_password' => 'password saat ini',
            'new_password' => 'password baru',
            'new_password_confirmation' => 'konfirmasi password baru',
        ];
    }

    /**
     * Configure the validator instance.
     *
     * @param  \Illuminate\Validation\Validator  $validator
     * @return void
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            // Additional security checks
            $newPassword = $this->input('new_password');

            // Check if password is too common
            $commonPasswords = [
                'password',
                '123456',
                '123456789',
                'qwerty',
                'abc123',
                'password123',
                'admin',
                'letmein',
                'welcome',
                'monkey'
            ];

            if (in_array(strtolower($newPassword), $commonPasswords)) {
                $validator->errors()->add('new_password', 'Password terlalu umum. Pilih password yang lebih aman.');
            }

            // Check if password contains user information
            $user = auth()->user();
            $userInfo = [
                strtolower($user->name),
                strtolower($user->email),
                strtolower(explode('@', $user->email)[0])
            ];

            foreach ($userInfo as $info) {
                if (str_contains(strtolower($newPassword), $info)) {
                    $validator->errors()->add('new_password', 'Password tidak boleh mengandung informasi pribadi Anda.');
                    break;
                }
            }
        });
    }
}
