<?php
// app/Models/Report.php (Updated - Removed ObservationForm)

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
        'severity_rating',      // enum low, medium, high, critical
        'action_taken',         // text field for action taken
        'description',
        'location',
        'images',
        'status',
        'start_process_at',
        'completed_at'
    ];

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

    // New scopes for master data
    public function scopeByCategory($query, $categoryId)
    {
        return $query->where('category_id', $categoryId);
    }

    public function scopeByContributing($query, $contributingId)
    {
        return $query->where('contributing_id', $contributingId);
    }

    public function scopeByAction($query, $actionId)
    {
        return $query->where('action_id', $actionId);
    }

    // New scopes for severity
    public function scopeBySeverity($query, $severity)
    {
        return $query->where('severity_rating', $severity);
    }

    public function scopeHighSeverity($query)
    {
        return $query->whereIn('severity_rating', ['high', 'critical']);
    }

    public function scopeLowSeverity($query)
    {
        return $query->whereIn('severity_rating', ['low', 'medium']);
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

    // Get category name from master data
    public function getCategoryNameAttribute()
    {
        return $this->categoryMaster ? $this->categoryMaster->name : null;
    }

    // Get contributing name from master data
    public function getContributingNameAttribute()
    {
        return $this->contributingMaster ? $this->contributingMaster->name : null;
    }

    // Get action name from master data
    public function getActionNameAttribute()
    {
        return $this->actionMaster ? $this->actionMaster->name : null;
    }

    // Get contributing → action hierarchy
    public function getContributingActionHierarchyAttribute()
    {
        if ($this->contributingMaster && $this->actionMaster) {
            return $this->contributingMaster->name . ' → ' . $this->actionMaster->name;
        }
        return null;
    }

    // Get severity badge color
    public function getSeverityColorAttribute()
    {
        return match ($this->severity_rating) {
            'low' => 'success',
            'medium' => 'warning',
            'high' => 'danger',
            'critical' => 'dark',
            default => 'secondary'
        };
    }

    // Get severity label
    public function getSeverityLabelAttribute()
    {
        return match ($this->severity_rating) {
            'low' => 'Rendah',
            'medium' => 'Sedang',
            'high' => 'Tinggi',
            'critical' => 'Kritis',
            default => ucfirst($this->severity_rating)
        };
    }

    // Check if action has been taken
    public function getHasActionTakenAttribute()
    {
        return !empty($this->action_taken);
    }

    // Get full report summary
    public function getReportSummaryAttribute()
    {
        $summary = [];

        if ($this->category_name) {
            $summary[] = "Kategori: {$this->category_name}";
        }

        if ($this->contributing_action_hierarchy) {
            $summary[] = "Detail: {$this->contributing_action_hierarchy}";
        }

        $summary[] = "Tingkat Keparahan: {$this->severity_label}";

        return implode(' | ', $summary);
    }

    // Get processing time in hours
    public function getProcessingTimeHoursAttribute()
    {
        if ($this->start_process_at && $this->completed_at) {
            return $this->start_process_at->diffInHours($this->completed_at);
        }
        return null;
    }

    // Check if report is completed
    public function getIsCompletedAttribute()
    {
        return $this->status === 'done';
    }

    // Check if report is in progress
    public function getIsInProgressAttribute()
    {
        return $this->status === 'in-progress';
    }

    // Check if report is waiting
    public function getIsWaitingAttribute()
    {
        return $this->status === 'waiting';
    }
}
