
<?php
// app/Http/Requests/CompleteReportRequest.php (Updated - Removed ObservationForm fields)



use Illuminate\Foundation\Http\FormRequest;

class CompleteReportRequest extends FormRequest
{
    public function authorize()
    {
        return $this->user()->role === 'hse_staff';
    }

    public function rules()
    {
        return [
            'action_taken' => 'required|string|max:1000',
        ];
    }

    public function messages()
    {
        return [
            'action_taken.required' => 'Aksi yang diambil wajib diisi',
            'action_taken.max' => 'Aksi yang diambil maksimal 1000 karakter',
        ];
    }

    public function attributes()
    {
        return [
            'action_taken' => 'aksi yang diambil',
        ];
    }
}
