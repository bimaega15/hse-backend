<?php
// app/Http/Requests/StoreReportDetailRequest.php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreReportDetailRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Only HSE staff can create report details
        return $this->user() && $this->user()->role === 'hse_staff';
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'correction_action' => [
                'required',
                'string',
                'max:2000',
                'min:10'
            ],
            'due_date' => [
                'required',
                'date',
                'after_or_equal:today',
                'before_or_equal:' . now()->addYear()->format('Y-m-d') // Max 1 year from now
            ],
            'users_id' => [
                'required',
                'integer',
                'exists:users,id',
                function ($attribute, $value, $fail) {
                    $user = \App\Models\User::find($value);
                    if ($user && $user->role !== 'employee') {
                        $fail('PIC harus memiliki role employee.');
                    }
                }
            ],
            'status_car' => [
                'nullable',
                'in:open,in_progress,closed'
            ],
            'evidences' => [
                'nullable',
                'array',
                'max:5' // Maximum 5 evidence files
            ],
            'evidences.*' => [
                'image',
                'mimes:jpeg,png,jpg,gif,webp',
                'max:5120', // 5MB
                'dimensions:max_width=4000,max_height=4000' // Max dimensions
            ]
        ];
    }

    /**
     * Get the error messages for the defined validation rules.
     */
    public function messages(): array
    {
        return [
            'correction_action.required' => 'Koreksi & tindakan korektif wajib diisi.',
            'correction_action.min' => 'Koreksi & tindakan korektif minimal 10 karakter.',
            'correction_action.max' => 'Koreksi & tindakan korektif maksimal 2000 karakter.',

            'due_date.required' => 'Tanggal selesai wajib diisi.',
            'due_date.date' => 'Format tanggal selesai tidak valid.',
            'due_date.after_or_equal' => 'Tanggal selesai tidak boleh kurang dari hari ini.',
            'due_date.before_or_equal' => 'Tanggal selesai maksimal 1 tahun dari sekarang.',

            'users_id.required' => 'PIC wajib dipilih.',
            'users_id.integer' => 'PIC harus berupa ID yang valid.',
            'users_id.exists' => 'PIC yang dipilih tidak ditemukan.',

            'status_car.in' => 'Status CAR harus salah satu dari: open, in_progress, closed.',

            'evidences.array' => 'Bukti harus berupa array file.',
            'evidences.max' => 'Maksimal 5 file bukti dapat diupload.',

            'evidences.*.image' => 'File bukti harus berupa gambar.',
            'evidences.*.mimes' => 'Format file bukti harus: jpeg, png, jpg, gif, atau webp.',
            'evidences.*.max' => 'Ukuran file bukti maksimal 5MB.',
            'evidences.*.dimensions' => 'Dimensi gambar bukti maksimal 4000x4000 pixel.',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'correction_action' => 'koreksi & tindakan korektif',
            'due_date' => 'tanggal selesai',
            'users_id' => 'PIC',
            'status_car' => 'status CAR',
            'evidences' => 'bukti',
            'evidences.*' => 'file bukti',
        ];
    }

    /**
     * Handle a failed validation attempt.
     */
    protected function failedValidation(\Illuminate\Contracts\Validation\Validator $validator)
    {
        throw new \Illuminate\Http\Exceptions\HttpResponseException(
            response()->json([
                'success' => false,
                'message' => 'Validation Error',
                'errors' => $validator->errors(),
            ], 422)
        );
    }
}
