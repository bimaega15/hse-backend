<?php
// app/Http/Requests/StartProcessRequest.php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StartProcessRequest extends FormRequest
{
    public function authorize()
    {
        return $this->user()->role === 'hse_staff';
    }

    public function rules()
    {
        return [
            'action_taken' => 'nullable|string|max:1000'
        ];
    }

    public function messages()
    {
        return [
            'action_taken.max' => 'Aksi yang diambil maksimal 1000 karakter'
        ];
    }
}
