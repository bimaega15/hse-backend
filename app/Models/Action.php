<?php
// app/Models/Action.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Action extends Model
{
    use HasFactory;

    protected $fillable = [
        'contributing_id',
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
    public function contributing()
    {
        return $this->belongsTo(Contributing::class);
    }

    public function category()
    {
        return $this->hasOneThrough(Category::class, Contributing::class, 'id', 'id', 'contributing_id', 'category_id');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByContributing($query, $contributingId)
    {
        return $query->where('contributing_id', $contributingId);
    }

    public function scopeByCategory($query, $categoryId)
    {
        return $query->whereHas('contributing', function ($q) use ($categoryId) {
            $q->where('category_id', $categoryId);
        });
    }

    // Accessors
    public function getFullNameAttribute()
    {
        return $this->contributing->category->name . ' - ' . $this->contributing->name . ' - ' . $this->name;
    }

    public function getCategoryNameAttribute()
    {
        return $this->contributing->category->name;
    }

    public function getContributingNameAttribute()
    {
        return $this->contributing->name;
    }
}
