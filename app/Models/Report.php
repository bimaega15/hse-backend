<?php
// app/Models/Report.php (Updated with ReportDetail relationship)

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Report extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'employee_id',
        'hse_staff_id',
        'category_id',           // Foreign key to categories (standalone)
        'contributing_id',       // Foreign key to contributings
        'action_id',            // Foreign key to actions
        'location_id',          // Foreign key to locations
        'project_id',           // Foreign key to projects
        'severity_rating',      // enum low, medium, high, critical
        'action_taken',         // text field for action taken
        'description',
        'images',
        'status',
        'start_process_at',
        'completed_at',
        'created_at'
    ];

    protected $casts = [
        'id' => 'integer',                    // TAMBAHKAN
        'employee_id' => 'integer',           // TAMBAHKAN
        'hse_staff_id' => 'integer',          // TAMBAHKAN
        'category_id' => 'integer',           // TAMBAHKAN
        'contributing_id' => 'integer',       // TAMBAHKAN
        'action_id' => 'integer',             // TAMBAHKAN
        'location_id' => 'integer',           // TAMBAHKAN
        'project_id' => 'integer',            // TAMBAHKAN
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

    // Existing relationships
    public function employee()
    {
        return $this->belongsTo(User::class, 'employee_id');
    }

    public function hseStaff()
    {
        return $this->belongsTo(User::class, 'hse_staff_id');
    }

    // Master data relationships
    public function categoryMaster()
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    public function contributingMaster()
    {
        return $this->belongsTo(Contributing::class, 'contributing_id');
    }

    public function actionMaster()
    {
        return $this->belongsTo(Action::class, 'action_id');
    }

    public function locationMaster()
    {
        return $this->belongsTo(Location::class, 'location_id');
    }

    public function project()
    {
        return $this->belongsTo(Project::class, 'project_id');
    }

    // NEW: Report Details relationship
    public function reportDetails()
    {
        return $this->hasMany(ReportDetail::class);
    }

    public function openReportDetails()
    {
        return $this->hasMany(ReportDetail::class)->where('status_car', 'open');
    }

    public function inProgressReportDetails()
    {
        return $this->hasMany(ReportDetail::class)->where('status_car', 'in_progress');
    }

    public function closedReportDetails()
    {
        return $this->hasMany(ReportDetail::class)->where('status_car', 'closed');
    }

    public function overdueReportDetails()
    {
        return $this->hasMany(ReportDetail::class)
            ->where('due_date', '<', now())
            ->where('status_car', '!=', 'closed');
    }

    // Existing scopes
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

    // NEW: Methods related to report details
    public function canHaveReportDetails()
    {
        return $this->status === 'in-progress' || $this->status === 'done';
    }

    public function hasOpenReportDetails()
    {
        return $this->reportDetails()->where('status_car', 'open')->exists();
    }

    public function hasOverdueReportDetails()
    {
        return $this->reportDetails()
            ->where('due_date', '<', now())
            ->where('status_car', '!=', 'closed')
            ->exists();
    }

    public function getReportDetailsCountAttribute()
    {
        return $this->reportDetails()->count();
    }

    public function getOpenReportDetailsCountAttribute()
    {
        return $this->reportDetails()->where('status_car', 'open')->count();
    }

    public function getInProgressReportDetailsCountAttribute()
    {
        return $this->reportDetails()->where('status_car', 'in_progress')->count();
    }

    public function getClosedReportDetailsCountAttribute()
    {
        return $this->reportDetails()->where('status_car', 'closed')->count();
    }

    public function getCompletionPercentageAttribute()
    {
        $total = $this->reportDetails()->count();
        if ($total === 0) return 0;

        $closed = $this->reportDetails()->where('status_car', 'closed')->count();
        return round(($closed / $total) * 100, 2);
    }
}
