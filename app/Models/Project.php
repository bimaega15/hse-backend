<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Project extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'project_name',
        'start_date',
        'end_date',
        'durasi',
        'status'
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'durasi' => 'int',
    ];

    public function getStatusBadgeAttribute()
    {
        return $this->status === 'open'
            ? '<span class="badge bg-success">Open</span>'
            : '<span class="badge bg-secondary">Closed</span>';
    }

    public function getDurationInDaysAttribute()
    {
        if ($this->start_date && $this->end_date) {
            return Carbon::parse($this->start_date)->diffInDays(Carbon::parse($this->end_date)) + 1;
        }
        return $this->durasi;
    }

    public function reports()
    {
        return $this->hasMany(Report::class, 'project_id');
    }

    public function observationDetails()
    {
        return $this->hasMany(ObservationDetail::class);
    }
}