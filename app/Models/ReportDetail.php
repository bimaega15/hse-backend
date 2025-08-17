<?php
// app/Models/ReportDetail.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ReportDetail extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'report_id',
        'correction_action',
        'due_date',
        'pic',
        'status_car',
        'evidences',
        'approved_by',
        'created_by'
    ];

    protected $casts = [
        'id' => 'integer',           // TAMBAHKAN
        'report_id' => 'integer',    // TAMBAHKAN
        'approved_by' => 'integer',  // TAMBAHKAN
        'created_by' => 'integer',   // TAMBAHKAN
        'evidences' => 'array',
        'due_date' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    protected $attributes = [
        'status_car' => 'open',
    ];

    // Relationships
    public function report()
    {
        return $this->belongsTo(Report::class);
    }

    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // Scopes
    public function scopeOpen($query)
    {
        return $query->where('status_car', 'open');
    }

    public function scopeInProgress($query)
    {
        return $query->where('status_car', 'in_progress');
    }

    public function scopeClosed($query)
    {
        return $query->where('status_car', 'closed');
    }

    public function scopeOverdue($query)
    {
        return $query->where('due_date', '<', now())->where('status_car', '!=', 'closed');
    }

    public function scopeByApprover($query, $userId)
    {
        return $query->where('approved_by', $userId);
    }

    public function scopeByCreator($query, $userId)
    {
        return $query->where('created_by', $userId);
    }

    // Accessors & Mutators
    public function getIsOverdueAttribute()
    {
        return $this->due_date < now() && $this->status_car !== 'closed';
    }

    public function getStatusLabelAttribute()
    {
        $labels = [
            'open' => 'Terbuka',
            'in_progress' => 'Dalam Proses',
            'closed' => 'Selesai'
        ];

        return $labels[$this->status_car] ?? $this->status_car;
    }

    public function getStatusColorAttribute()
    {
        $colors = [
            'open' => 'red',
            'in_progress' => 'yellow',
            'closed' => 'green'
        ];

        return $colors[$this->status_car] ?? 'gray';
    }

    // Methods
    public function canBeUpdated()
    {
        return $this->status_car !== 'closed';
    }

    public function markAsCompleted()
    {
        $this->update(['status_car' => 'closed']);
    }

    public function markAsInProgress()
    {
        $this->update(['status_car' => 'in_progress']);
    }
}
