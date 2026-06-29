<?php
// app/Models/Tbm.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Tbm extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'tbms';

    protected $fillable = [
        'date_time_tbm',
        'speaker',
        'project',
        'location',
        'participant_count',
        'summary_topic',
        'activity_pictures',
    ];

    protected $casts = [
        'id' => 'integer',
        'speaker' => 'integer',
        'project' => 'integer',
        'location' => 'integer',
        'participant_count' => 'integer',
        'date_time_tbm' => 'datetime',
        'activity_pictures' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    protected $appends = ['activity_picture_urls'];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    | Note: the FK columns are literally named `speaker`, `project`, `location`,
    | so the relationship methods use distinct names to avoid colliding with the
    | integer attributes of the same name.
    */
    public function speakerUser()
    {
        return $this->belongsTo(User::class, 'speaker');
    }

    public function projectData()
    {
        return $this->belongsTo(Project::class, 'project');
    }

    public function locationData()
    {
        return $this->belongsTo(Location::class, 'location');
    }

    /*
    |--------------------------------------------------------------------------
    | Accessors
    |--------------------------------------------------------------------------
    */
    public function getActivityPictureUrlsAttribute(): array
    {
        $images = $this->activity_pictures;

        if (empty($images)) {
            return [];
        }

        // Handle double-encoded JSON (string instead of array)
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
            // Already a full URL
            if (str_starts_with($image, 'http://') || str_starts_with($image, 'https://')) {
                return $image;
            }
            // Stored relative path on the public disk
            return asset('storage/' . ltrim($image, '/'));
        }, $images)));
    }

    /*
    |--------------------------------------------------------------------------
    | Scopes
    |--------------------------------------------------------------------------
    */
    public function scopeForMonth($query, $year, $month)
    {
        return $query->whereYear('date_time_tbm', $year)
            ->whereMonth('date_time_tbm', $month);
    }

    public function scopeThisMonth($query)
    {
        return $query->whereYear('date_time_tbm', now()->year)
            ->whereMonth('date_time_tbm', now()->month);
    }
}
