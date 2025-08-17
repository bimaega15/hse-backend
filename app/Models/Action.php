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
        'id' => 'integer',
        'contributing_id' => 'integer',
        'is_active' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    // Relationships
    public function contributing()
    {
        return $this->belongsTo(Contributing::class);
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

    // Accessors
    public function getFullNameAttribute()
    {
        return $this->contributing->name . ' - ' . $this->name;
    }

    public function getContributingNameAttribute()
    {
        return $this->contributing->name;
    }
}
