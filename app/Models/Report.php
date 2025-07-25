<?php
// app/Models/Report.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Report extends Model
{
    use HasFactory;

    protected $fillable = ['employee_id', 'category', 'equipment_type', 'contributing_factor', 'description', 'location', 'status', 'images', 'start_process_at', 'completed_at', 'hse_staff_id'];

    protected $casts = [
        'images' => 'array',
        'start_process_at' => 'datetime',
        'completed_at' => 'datetime',
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
    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    public function scopeByEmployee($query, $employeeId)
    {
        return $query->where('employee_id', $employeeId);
    }

    public function scopeByHseStaff($query, $hseStaffId)
    {
        return $query->where('hse_staff_id', $hseStaffId);
    }

    public function scopeRecent($query)
    {
        return $query->orderBy('created_at', 'desc');
    }
}
