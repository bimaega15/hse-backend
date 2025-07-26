<?php
// app/Models/Contributing.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Contributing extends Model
{
    use HasFactory;

    protected $fillable = [
        'category_id',
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
    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function actions()
    {
        return $this->hasMany(Action::class, 'contributing_id');
    }

    public function activeActions()
    {
        return $this->hasMany(Action::class, 'contributing_id')->where('is_active', true);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByCategory($query, $categoryId)
    {
        return $query->where('category_id', $categoryId);
    }

    // Accessors
    public function getActionsCountAttribute()
    {
        return $this->actions()->count();
    }

    public function getActiveActionsCountAttribute()
    {
        return $this->activeActions()->count();
    }

    public function getFullNameAttribute()
    {
        return $this->category->name . ' - ' . $this->name;
    }
}
