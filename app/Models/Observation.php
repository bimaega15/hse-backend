<?php
// app/Models/Observation.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Observation extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'waktu_observasi',
        'at_risk_behavior',
        'nearmiss_incident',
        'informal_risk_mgmt',
        'sim_k3',
        'waktu_mulai',
        'waktu_selesai',
        'status',
        'notes'
    ];

    protected $casts = [
        'id' => 'integer',                    // TAMBAHKAN
        'user_id' => 'integer',               // TAMBAHKAN
        'at_risk_behavior' => 'integer',      // TAMBAHKAN
        'nearmiss_incident' => 'integer',     // TAMBAHKAN
        'informal_risk_mgmt' => 'integer',    // TAMBAHKAN
        'sim_k3' => 'integer',                // TAMBAHKAN
        'waktu_observasi' => 'datetime:H:i',
        'waktu_mulai' => 'datetime:H:i',
        'waktu_selesai' => 'datetime:H:i',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    protected $attributes = [
        'status' => 'draft',
        'at_risk_behavior' => 0,
        'nearmiss_incident' => 0,
        'informal_risk_mgmt' => 0,
        'sim_k3' => 0,
    ];

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function details()
    {
        return $this->hasMany(ObservationDetail::class);
    }

    public function atRiskBehaviorDetails()
    {
        return $this->hasMany(ObservationDetail::class)->where('observation_type', 'at_risk_behavior');
    }

    public function nearmissIncidentDetails()
    {
        return $this->hasMany(ObservationDetail::class)->where('observation_type', 'nearmiss_incident');
    }

    public function informalRiskMgmtDetails()
    {
        return $this->hasMany(ObservationDetail::class)->where('observation_type', 'informal_risk_mgmt');
    }

    public function simK3Details()
    {
        return $this->hasMany(ObservationDetail::class)->where('observation_type', 'sim_k3');
    }

    // Scopes
    public function scopeDraft($query)
    {
        return $query->where('status', 'draft');
    }

    public function scopeSubmitted($query)
    {
        return $query->where('status', 'submitted');
    }

    public function scopeReviewed($query)
    {
        return $query->where('status', 'reviewed');
    }

    public function scopeByUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeToday($query)
    {
        return $query->whereDate('created_at', today());
    }

    public function scopeThisWeek($query)
    {
        return $query->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()]);
    }

    public function scopeThisMonth($query)
    {
        return $query->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year);
    }

    // Accessors
    public function getTotalObservationsAttribute()
    {
        return $this->at_risk_behavior + $this->nearmiss_incident + $this->informal_risk_mgmt + $this->sim_k3;
    }

    public function getDurationInMinutesAttribute()
    {
        if (!$this->waktu_mulai || !$this->waktu_selesai) {
            return null;
        }

        $start = strtotime($this->waktu_mulai);
        $end = strtotime($this->waktu_selesai);

        return round(($end - $start) / 60);
    }

    // Methods
    public function updateCounters()
    {
        $this->at_risk_behavior = $this->atRiskBehaviorDetails()->count();
        $this->nearmiss_incident = $this->nearmissIncidentDetails()->count();
        $this->informal_risk_mgmt = $this->informalRiskMgmtDetails()->count();
        $this->sim_k3 = $this->simK3Details()->count();
        $this->save();
    }

    public function canBeEdited()
    {
        return $this->status === 'draft';
    }

    public function canBeSubmitted()
    {
        return $this->status === 'draft' && $this->total_observations > 0;
    }
}
