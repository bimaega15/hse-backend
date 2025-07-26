<?php
// app/Models/ObservationForm.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ObservationForm extends Model
{
    use HasFactory;

    protected $fillable = [
        'report_id',
        'at_risk_behavior',
        'nearmiss_incident',
        'informasi_risk_mgmt',
        'sim_k3',
        'notes'
    ];

    protected $casts = [
        'at_risk_behavior' => 'integer',
        'nearmiss_incident' => 'integer',
        'informasi_risk_mgmt' => 'integer',
        'sim_k3' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    // Relationships
    public function report()
    {
        return $this->belongsTo(Report::class);
    }

    // Accessors
    public function getTotalScoreAttribute()
    {
        return $this->at_risk_behavior + $this->nearmiss_incident + $this->informasi_risk_mgmt + $this->sim_k3;
    }

    public function getRiskLevelAttribute()
    {
        $total = $this->total_score;

        if ($total <= 4) {
            return 'Low';
        } elseif ($total <= 8) {
            return 'Medium';
        } elseif ($total <= 12) {
            return 'High';
        } else {
            return 'Critical';
        }
    }

    public function getRiskColorAttribute()
    {
        return match ($this->risk_level) {
            'Low' => 'success',
            'Medium' => 'warning',
            'High' => 'danger',
            'Critical' => 'dark',
            default => 'secondary'
        };
    }

    // Methods
    public function getScoreBreakdown()
    {
        return [
            'at_risk_behavior' => $this->at_risk_behavior,
            'nearmiss_incident' => $this->nearmiss_incident,
            'informasi_risk_mgmt' => $this->informasi_risk_mgmt,
            'sim_k3' => $this->sim_k3,
            'total' => $this->total_score,
            'risk_level' => $this->risk_level
        ];
    }
}
