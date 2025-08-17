<?php
// app/Models/ObservationDetail.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ObservationDetail extends Model
{
    use HasFactory;

    protected $fillable = [
        'observation_id',
        'observation_type',
        'category_id',
        'description',
        'severity',
        'action_taken'
    ];

    protected $casts = [
        'id' => 'integer',           // TAMBAHKAN
        'observation_id' => 'integer', // TAMBAHKAN
        'category_id' => 'integer',  // TAMBAHKAN
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Constants for observation types
    const OBSERVATION_TYPES = [
        'at_risk_behavior' => 'At Risk Behavior',
        'nearmiss_incident' => 'Nearmiss Incident',
        'informal_risk_mgmt' => 'Informal Risk Management',
        'sim_k3' => 'SIM K3'
    ];

    const SEVERITY_LEVELS = [
        'low' => 'Low',
        'medium' => 'Medium',
        'high' => 'High',
        'critical' => 'Critical'
    ];

    // Relationships
    public function observation()
    {
        return $this->belongsTo(Observation::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    // Scopes
    public function scopeByObservationType($query, $type)
    {
        return $query->where('observation_type', $type);
    }

    public function scopeByCategory($query, $categoryId)
    {
        return $query->where('category_id', $categoryId);
    }

    public function scopeBySeverity($query, $severity)
    {
        return $query->where('severity', $severity);
    }

    public function scopeHighSeverity($query)
    {
        return $query->whereIn('severity', ['high', 'critical']);
    }

    public function scopeLowSeverity($query)
    {
        return $query->whereIn('severity', ['low', 'medium']);
    }

    public function scopeAtRiskBehavior($query)
    {
        return $query->where('observation_type', 'at_risk_behavior');
    }

    public function scopeNearmissIncident($query)
    {
        return $query->where('observation_type', 'nearmiss_incident');
    }

    public function scopeInformalRiskMgmt($query)
    {
        return $query->where('observation_type', 'informal_risk_mgmt');
    }

    public function scopeSimK3($query)
    {
        return $query->where('observation_type', 'sim_k3');
    }

    // Accessors
    public function getObservationTypeNameAttribute()
    {
        return self::OBSERVATION_TYPES[$this->observation_type] ?? $this->observation_type;
    }

    public function getSeverityNameAttribute()
    {
        return self::SEVERITY_LEVELS[$this->severity] ?? $this->severity;
    }

    public function getCategoryNameAttribute()
    {
        return $this->category ? $this->category->name : null;
    }

    public function getSeverityColorAttribute()
    {
        return match ($this->severity) {
            'low' => '#10B981',      // Green
            'medium' => '#F59E0B',   // Yellow
            'high' => '#EF4444',     // Red
            'critical' => '#7C2D12', // Dark Red
            default => '#6B7280'     // Gray
        };
    }

    public function getSeverityBadgeAttribute()
    {
        return [
            'text' => $this->severity_name,
            'color' => $this->severity_color,
            'class' => match ($this->severity) {
                'low' => 'bg-green-100 text-green-800',
                'medium' => 'bg-yellow-100 text-yellow-800',
                'high' => 'bg-red-100 text-red-800',
                'critical' => 'bg-red-200 text-red-900',
                default => 'bg-gray-100 text-gray-800'
            }
        ];
    }

    // Static methods
    public static function getObservationTypes()
    {
        return self::OBSERVATION_TYPES;
    }

    public static function getSeverityLevels()
    {
        return self::SEVERITY_LEVELS;
    }
}
