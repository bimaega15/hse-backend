<?php
// app/Http/Requests/StoreObservationRequest.php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class StoreObservationRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Authorization handled by middleware
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'waktu_observasi' => 'required|date_format:H:i',
            'waktu_mulai' => 'required|date_format:H:i',
            'waktu_selesai' => 'required|date_format:H:i|after:waktu_mulai',
            'notes' => 'nullable|string|max:1000',

            // Details validation
            'details' => 'required|array|min:1',
            'details.*.observation_type' => 'required|in:at_risk_behavior,nearmiss_incident,informal_risk_mgmt,sim_k3',
            'details.*.category_id' => 'required|exists:categories,id',
            'details.*.description' => 'required|string|max:1000',
            'details.*.severity' => 'required|in:low,medium,high,critical',
            'details.*.action_taken' => 'nullable|string|max:500',
        ];
    }

    /**
     * Get custom validation messages.
     */
    public function messages(): array
    {
        return [
            'waktu_observasi.required' => 'Waktu observasi wajib diisi',
            'waktu_observasi.date_format' => 'Format waktu observasi harus HH:MM',
            'waktu_mulai.required' => 'Waktu mulai wajib diisi',
            'waktu_mulai.date_format' => 'Format waktu mulai harus HH:MM',
            'waktu_selesai.required' => 'Waktu selesai wajib diisi',
            'waktu_selesai.date_format' => 'Format waktu selesai harus HH:MM',
            'waktu_selesai.after' => 'Waktu selesai harus setelah waktu mulai',
            'notes.max' => 'Catatan maksimal 1000 karakter',

            'details.required' => 'Detail observasi wajib diisi',
            'details.min' => 'Minimal harus ada 1 detail observasi',
            'details.*.observation_type.required' => 'Tipe observasi wajib diisi',
            'details.*.observation_type.in' => 'Tipe observasi tidak valid',
            'details.*.category_id.required' => 'Kategori wajib dipilih',
            'details.*.category_id.exists' => 'Kategori yang dipilih tidak valid',
            'details.*.description.required' => 'Deskripsi wajib diisi',
            'details.*.description.max' => 'Deskripsi maksimal 1000 karakter',
            'details.*.severity.required' => 'Tingkat keparahan wajib dipilih',
            'details.*.severity.in' => 'Tingkat keparahan tidak valid',
            'details.*.action_taken.max' => 'Tindakan yang diambil maksimal 500 karakter',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'waktu_observasi' => 'waktu observasi',
            'waktu_mulai' => 'waktu mulai',
            'waktu_selesai' => 'waktu selesai',
            'notes' => 'catatan',
            'details' => 'detail observasi',
            'details.*.observation_type' => 'tipe observasi',
            'details.*.category_id' => 'kategori',
            'details.*.description' => 'deskripsi',
            'details.*.severity' => 'tingkat keparahan',
            'details.*.action_taken' => 'tindakan yang diambil',
        ];
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            // Custom validation: Check if observation types match the counts
            $details = $this->input('details', []);
            $typeCounts = [];

            foreach ($details as $detail) {
                $type = $detail['observation_type'] ?? '';
                $typeCounts[$type] = ($typeCounts[$type] ?? 0) + 1;
            }

            // Additional business logic validation can be added here
            // For example, validate that time ranges are reasonable
            $waktuMulai = $this->input('waktu_mulai');
            $waktuSelesai = $this->input('waktu_selesai');

            if ($waktuMulai && $waktuSelesai) {
                $start = strtotime($waktuMulai);
                $end = strtotime($waktuSelesai);
                $diffMinutes = ($end - $start) / 60;

                if ($diffMinutes > 480) { // 8 hours
                    $validator->errors()->add('waktu_selesai', 'Durasi observasi tidak boleh lebih dari 8 jam');
                }

                if ($diffMinutes < 5) { // 5 minutes
                    $validator->errors()->add('waktu_selesai', 'Durasi observasi minimal 5 menit');
                }
            }
        });
    }

    /**
     * Handle a failed validation attempt.
     */
    protected function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(
            response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
                'error_code' => 'VALIDATION_ERROR',
                'timestamp' => now()->toISOString()
            ], 422)
        );
    }
}
