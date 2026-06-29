<?php
// app/Models/DailyActivityDetail.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DailyActivityDetail extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'daily_activity_details';

    protected $fillable = [
        'daily_activity_id',
        'activity_id',
        'todolist',
        'activity_datetime',
        'status',
        'description_status',
        'pictures_activity',
        'realization_datetime',
        'user_id',
    ];

    protected $casts = [
        'id' => 'integer',
        'daily_activity_id' => 'integer',
        'activity_id' => 'integer',
        'user_id' => 'integer',
        'activity_datetime' => 'datetime',
        'realization_datetime' => 'datetime',
        'pictures_activity' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    protected $appends = ['picture_urls', 'status_label'];

    public const STATUSES = [
        'pending'     => 'Pending',
        'in_progress' => 'In Progress',
        'done'        => 'Done',
        'cancel'      => 'Cancel',
        'rejected'    => 'Rejected',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */
    public function dailyActivity()
    {
        return $this->belongsTo(DailyActivity::class, 'daily_activity_id');
    }

    public function activity()
    {
        return $this->belongsTo(Activity::class, 'activity_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /*
    |--------------------------------------------------------------------------
    | Accessors
    |--------------------------------------------------------------------------
    */
    public function getStatusLabelAttribute(): string
    {
        return self::STATUSES[$this->status] ?? $this->status;
    }

    public function getPictureUrlsAttribute(): array
    {
        $images = $this->pictures_activity;

        if (empty($images)) {
            return [];
        }

        if (is_string($images)) {
            $decoded = json_decode($images, true);
            $images = is_array($decoded) ? $decoded : [];
        }

        if (!is_array($images)) {
            return [];
        }

        return array_values(array_filter(array_map(function ($image) {
            if (!is_string($image)) {
                return null;
            }
            if (str_starts_with($image, 'http://') || str_starts_with($image, 'https://')) {
                return $image;
            }
            return asset('storage/' . ltrim($image, '/'));
        }, $images)));
    }

    /*
    |--------------------------------------------------------------------------
    | Scopes
    |--------------------------------------------------------------------------
    */
    public function scopeStatus($query, $status)
    {
        return $query->where('status', $status);
    }
}
