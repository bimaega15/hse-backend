<?php
// app/Models/CategoryKpi.php

namespace App\Models;

use App\Support\KpiScoring;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CategoryKpi extends Model
{
    use HasFactory;

    protected $table = 'category_kpi';

    protected $fillable = [
        'category_name',
        'status',
    ];

    protected $casts = [
        'id' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public const STATUSES = ['active', 'not active'];

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    // Relationships
    public function hseKpis()
    {
        return $this->hasMany(HseKpi::class, 'category_kpi_id');
    }

    // Accessors
    /** lagging_indicator | leading_indicator derived from the name. */
    public function getIndicatorKeyAttribute(): string
    {
        return KpiScoring::keyFromName($this->category_name);
    }
}
