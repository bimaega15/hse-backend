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

    // Relationships
    public function report()
    {
        return $this->belongsTo(Report::class);
    }
}
