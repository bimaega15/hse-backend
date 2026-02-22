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
        'contributing_id',
        'action_id',
        'location_id',
        'project_id',
        'activator_id',
        'report_date',
        'description',
        'severity',
        'action_taken',
        'images'
    ];

    protected $casts = [
        'id' => 'integer',
        'observation_id' => 'integer',
        'category_id' => 'integer',
        'contributing_id' => 'integer',
        'action_id' => 'integer',
        'location_id' => 'integer',
        'project_id' => 'integer',
        'activator_id' => 'integer',
        'report_date' => 'datetime',
        'images' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    protected $appends = ['image_urls'];
    protected $hidden = ['images'];

    /**
     * Get full URLs for stored image paths
     */
    public function getImageUrlsAttribute(): array
    {
        $images = $this->images;

        if (empty($images)) {
            return [];
        }

        // Handle double-encoded JSON from old data (string instead of array)
        if (is_string($images)) {
            $decoded = json_decode($images, true);
            $images = is_array($decoded) ? $decoded : [];
        }

        if (!is_array($images)) {
            return [];
        }

        return array_values(array_filter(array_map(function ($image) {
            if (is_string($image) && strpos($image, 'observation_images/') === 0) {
                return asset('storage/' . $image);
            }
            // Old base64 data objects - skip
            if (is_array($image)) {
                return null;
            }
            return null;
        }, $images)));
    }

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

    public function activator()
    {
        return $this->belongsTo(Activator::class);
    }

    public function contributing()
    {
        return $this->belongsTo(Contributing::class);
    }

    public function action()
    {
        return $this->belongsTo(Action::class);
    }

    public function location()
    {
        return $this->belongsTo(Location::class);
    }

    public function project()
    {
        return $this->belongsTo(Project::class);
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

    public function getActivatorNameAttribute()
    {
        return $this->activator ? $this->activator->name : null;
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
