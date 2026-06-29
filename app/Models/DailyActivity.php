<?php
// app/Models/DailyActivity.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DailyActivity extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'daily_activities';

    protected $fillable = [
        'user_id',
        'datetime_activity',
        'project_id',
        'location_id',
        'description',
    ];

    protected $casts = [
        'id' => 'integer',
        'user_id' => 'integer',
        'project_id' => 'integer',
        'location_id' => 'integer',
        'datetime_activity' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function project()
    {
        return $this->belongsTo(Project::class, 'project_id');
    }

    public function location()
    {
        return $this->belongsTo(Location::class, 'location_id');
    }

    public function details()
    {
        return $this->hasMany(DailyActivityDetail::class, 'daily_activity_id');
    }

    /*
    |--------------------------------------------------------------------------
    | Scopes
    |--------------------------------------------------------------------------
    */
    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeThisMonth($query)
    {
        return $query->whereYear('datetime_activity', now()->year)
            ->whereMonth('datetime_activity', now()->month);
    }
}
