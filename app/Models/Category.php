<?php
// app/Models/Category.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    // Relationships
    public function contributings()
    {
        return $this->hasMany(Contributing::class);
    }

    public function activeContributings()
    {
        return $this->hasMany(Contributing::class)->where('is_active', true);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    // Accessors
    public function getContributingsCountAttribute()
    {
        return $this->contributings()->count();
    }

    public function getActiveContributingsCountAttribute()
    {
        return $this->activeContributings()->count();
    }
}
