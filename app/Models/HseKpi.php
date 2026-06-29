<?php
// app/Models/HseKpi.php

namespace App\Models;

use App\Support\KpiScoring;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class HseKpi extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'hse_kpi';

    protected $fillable = [
        'category_kpi_id',
        'project_id',
        'users_id',
        'report_date',
        'description',
        'average',
        'rumus',
    ];

    protected $casts = [
        'id' => 'integer',
        'category_kpi_id' => 'integer',
        'project_id' => 'integer',
        'users_id' => 'array',
        'rumus' => 'array',
        'report_date' => 'date',
        'average' => 'double',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    protected $appends = ['indicator_key', 'overall_nilai'];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */
    public function categoryKpi()
    {
        return $this->belongsTo(CategoryKpi::class, 'category_kpi_id');
    }

    public function project()
    {
        return $this->belongsTo(Project::class, 'project_id');
    }

    public function details()
    {
        return $this->hasMany(HseKpiDetail::class, 'hse_kpi_id');
    }

    /*
    |--------------------------------------------------------------------------
    | Accessors
    |--------------------------------------------------------------------------
    */
    /** lagging_indicator | leading_indicator — from stored rumus, else category name. */
    public function getIndicatorKeyAttribute(): string
    {
        if (is_array($this->rumus) && isset($this->rumus[0]['category'])) {
            return $this->rumus[0]['category'];
        }
        return KpiScoring::keyFromName(optional($this->categoryKpi)->category_name);
    }

    /** Assigned hse_staff users resolved from the users_id array. */
    public function getAssignedUsersAttribute()
    {
        $ids = is_array($this->users_id) ? $this->users_id : [];
        if (empty($ids)) {
            return collect();
        }
        return User::whereIn('id', $ids)->get(['id', 'name', 'department']);
    }

    public function getOverallNilaiAttribute(): ?string
    {
        return KpiScoring::overallBand($this->average !== null ? (float) $this->average : null);
    }

    /*
    |--------------------------------------------------------------------------
    | Methods
    |--------------------------------------------------------------------------
    */
    /** Recompute the average % pencapaian across details and persist it. */
    public function recalculateAverage(): void
    {
        $details = $this->relationLoaded('details') ? $this->details : $this->details()->get();

        $percentages = $details
            ->map(fn(HseKpiDetail $d) => $d->percentage)
            ->filter(fn($p) => $p !== null);

        $this->average = $percentages->isNotEmpty() ? round($percentages->avg(), 1) : null;
        $this->saveQuietly();
    }
}
