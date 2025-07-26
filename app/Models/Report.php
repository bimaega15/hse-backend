<?php
// app/Models/Report.php (Updated with master data relations)

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
        'category',             // Keep for backward compatibility
        'equipment_type',
        'contributing_factor',  // Keep for backward compatibility
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

    public function observationForm()
    {
        return $this->hasOne(ObservationForm::class);
    }

    // New relationships for master data
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

    // Get category name (prioritize master data over string field)
    public function getCategoryNameAttribute()
    {
        if ($this->categoryMaster) {
            return $this->categoryMaster->name;
        }
        return $this->category; // Fallback to string field
    }

    // Get contributing name (prioritize master data over string field)
    public function getContributingNameAttribute()
    {
        if ($this->contributingMaster) {
            return $this->contributingMaster->name;
        }
        return $this->contributing_factor; // Fallback to string field
    }

    // Get action name
    public function getActionNameAttribute()
    {
        if ($this->actionMaster) {
            return $this->actionMaster->name;
        }
        return null;
    }

    // Get contributing → action hierarchy
    public function getContributingActionHierarchyAttribute()
    {
        if ($this->contributingMaster && $this->actionMaster) {
            return $this->contributingMaster->name . ' → ' . $this->actionMaster->name;
        }
        return $this->contributing_factor;
    }
}
