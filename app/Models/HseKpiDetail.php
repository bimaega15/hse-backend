<?php
// app/Models/HseKpiDetail.php

namespace App\Models;

use App\Support\KpiScoring;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class HseKpiDetail extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'hse_kpi_detail';

    protected $fillable = [
        'hse_kpi_id',
        'activity_name',
        'type_target',
        'target',
        'realisasi',
        'rumus',
    ];

    protected $casts = [
        'id' => 'integer',
        'hse_kpi_id' => 'integer',
        'target' => 'double',
        'realisasi' => 'double',
        'rumus' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    protected $appends = ['target_display', 'percentage', 'nilai_label', 'nilai_score'];

    public const TYPE_TARGETS = ['%', '<', '>', '<=', '>=', 'x', 'Jam Per Hari'];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */
    public function hseKpi()
    {
        return $this->belongsTo(HseKpi::class, 'hse_kpi_id');
    }

    /*
    |--------------------------------------------------------------------------
    | Scoring accessors
    |--------------------------------------------------------------------------
    */
    public function getIndicatorKeyAttribute(): string
    {
        return optional($this->hseKpi)->indicator_key ?? 'leading_indicator';
    }

    /** Resolve the scoring band results: own rumus first, else parent hse_kpi rumus, else default. */
    public function getRumusResultsAttribute(): array
    {
        $key = $this->indicator_key;

        $rumus = !empty($this->rumus)
            ? $this->rumus
            : (optional($this->hseKpi)->rumus ?? []);

        if (is_array($rumus)) {
            foreach ($rumus as $entry) {
                if (($entry['category'] ?? null) === $key) {
                    return $entry['results'] ?? [];
                }
            }
            // single-category rumus stored without matching key
            if (isset($rumus[0]['results']) && !isset($rumus[0]['category'])) {
                return $rumus[0]['results'];
            }
        }

        return KpiScoring::rumusFor($key)['results'] ?? [];
    }

    public function getTargetDisplayAttribute(): string
    {
        $t = rtrim(rtrim(number_format((float) $this->target, 2, '.', ''), '0'), '.');

        return match ($this->type_target) {
            '%' => $t . '%',
            'x' => $t . ' x',
            'Jam Per Hari' => $t . ' Jam Per Hari',
            default => $this->type_target . ' ' . $t, // <, >, <=, >=
        };
    }

    public function getPercentageAttribute(): ?float
    {
        return KpiScoring::percentage(
            $this->indicator_key,
            (float) $this->target,
            $this->realisasi !== null ? (float) $this->realisasi : null
        );
    }

    public function getNilaiLabelAttribute(): ?string
    {
        return KpiScoring::nilai(
            $this->indicator_key,
            (float) $this->target,
            $this->realisasi !== null ? (float) $this->realisasi : null,
            $this->rumus_results
        );
    }

    public function getNilaiScoreAttribute(): ?int
    {
        $label = $this->nilai_label;
        return $label ? KpiScoring::bandScore($label) : null;
    }
}
