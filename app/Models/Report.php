<?php
// app/Models/Report.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Report extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['employee_id', 'hse_staff_id', 'category', 'equipment_type', 'contributing_factor', 'description', 'location', 'images', 'status', 'start_process_at', 'completed_at'];

    protected $casts = [
        'images' => 'array',
        'start_process_at' => 'datetime',
        'completed_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    protected $attributes = [
        'status' => 'waiting',
    ];

    // Relationships
    public function employee()
    {
        return $this->belongsTo(User::class, 'employee_id');
    }

    public function hseStaff()
    {
        return $this->belongsTo(User::class, 'hse_staff_id');
    }

    public function observationForm()
    {
        return $this->hasOne(ObservationForm::class);
    }

    // Scopes
    public function scopeWaiting($query)
    {
        return $query->where('status', 'waiting');
    }

    public function scopeInProgress($query)
    {
        return $query->where('status', 'in-progress');
    }

    public function scopeDone($query)
    {
        return $query->where('status', 'done');
    }

    public function scopeByEmployee($query, $employeeId)
    {
        return $query->where('employee_id', $employeeId);
    }

    public function scopeByHseStaff($query, $hseStaffId)
    {
        return $query->where('hse_staff_id', $hseStaffId);
    }

    // Accessors
    public function getImageUrlsAttribute()
    {
        if (!$this->images) {
            return [];
        }

        return array_map(function ($imagePath) {
            return url('storage/' . $imagePath);
        }, $this->images);
    }

    public function getProcessingTimeAttribute()
    {
        if (!$this->start_process_at || !$this->completed_at) {
            return null;
        }

        return $this->start_process_at->diffInHours($this->completed_at);
    }

    // Methods
    public function canBeModified()
    {
        return $this->status === 'waiting';
    }

    public function canBeProcessed()
    {
        return $this->status === 'waiting';
    }

    public function canBeCompleted()
    {
        return $this->status === 'in-progress';
    }

    public function isOwnedBy(User $user)
    {
        return $this->employee_id === $user->id;
    }

    public function isAssignedTo(User $user)
    {
        return $this->hse_staff_id === $user->id;
    }

    public function getStatusColorAttribute()
    {
        return match ($this->status) {
            'waiting' => 'warning',
            'in-progress' => 'info',
            'done' => 'success',
            default => 'secondary',
        };
    }

    public function getStatusLabelAttribute()
    {
        return match ($this->status) {
            'waiting' => 'Menunggu',
            'in-progress' => 'Diproses',
            'done' => 'Selesai',
            default => 'Unknown',
        };
    }
}
