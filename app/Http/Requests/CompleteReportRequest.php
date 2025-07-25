<?php
// app/Http/Requests/CompleteReportRequest.php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CompleteReportRequest extends FormRequest
{
    public function authorize()
    {
        return auth()->user()->role === 'hse_staff';
    }

    public function rules()
    {
        return [
            'at_risk_behavior' => 'required|integer|min:0|max:100',
            'nearmiss_incident' => 'required|integer|min:0|max:100',
            'informasi_risk_mgmt' => 'required|integer|min:0|max:100',
            'sim_k3' => 'required|integer|min:0|max:100',
            'notes' => 'nullable|string|max:500',
        ];
    }

    public function messages()
    {
        return [
            'at_risk_behavior.required' => 'At Risk Behavior harus diisi',
            'nearmiss_incident.required' => 'Nearmiss Incident harus diisi',
            'informasi_risk_mgmt.required' => 'Informasi Risk Management harus diisi',
            'sim_k3.required' => 'SIM K3 harus diisi',
            '*.min' => 'Nilai minimal adalah 0',
            '*.max' => 'Nilai maksimal adalah 100',
        ];
    }
}
