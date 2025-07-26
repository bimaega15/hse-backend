<?php
// app/Models/Contributing.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Contributing extends Model
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

    // Accessors
    public function getActionsCountAttribute()
    {
        return $this->actions()->count();
    }

    public function getActiveActionsCountAttribute()
    {
        return $this->activeActions()->count();
    }
}
