<?php
// app/Models/User.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = ['name', 'email', 'password', 'role', 'department', 'phone', 'profile_image', 'is_active'];

    protected $hidden = ['password', 'remember_token'];

    protected $casts = [
        'id' => 'integer',                    // TAMBAHKAN
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'is_active' => 'boolean',
        'created_at' => 'datetime',           // TAMBAHKAN
        'updated_at' => 'datetime',
    ];

    // Relationships
    public function observations()
    {
        return $this->hasMany(Observation::class);
    }

    public function reports()
    {
        return $this->hasMany(Report::class, 'employee_id');
    }

    public function assignedReports()
    {
        return $this->hasMany(Report::class, 'hse_staff_id');
    }

    public function notifications()
    {
        return $this->hasMany(Notification::class);
    }

    // Scopes
    public function scopeEmployees($query)
    {
        return $query->where('role', 'employee');
    }

    public function scopeHseStaff($query)
    {
        return $query->where('role', 'hse_staff');
    }

    public function scopeAdmins($query)
    {
        return $query->where('role', 'admin');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeRole($query, $role)
    {
        return $query->where('role', $role);
    }

    // Helper methods for role checking
    public function isAdmin()
    {
        return $this->role === 'admin';
    }

    public function isHseStaff()
    {
        return $this->role === 'hse_staff';
    }

    public function isEmployee()
    {
        return $this->role === 'employee';
    }

    public function getRoleDisplayAttribute(): string
    {
        return match ($this->role) {
            'admin' => 'Administrator',
            'hse_staff' => 'HSE Staff',
            'employee' => 'Employee',
            default => 'Unknown'
        };
    }
}
