<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Banner extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'icon',
        'image',
        'background_color',
        'text_color',
        'is_active',
        'sort_order'
    ];

    protected $casts = [
        'id' => 'integer',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
        'created_at' => 'datetime',  // TAMBAHKAN
        'updated_at' => 'datetime',  // TAMBAHKAN
    ];

    // Scope for active banners
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    // Scope for ordered banners
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order', 'asc')->orderBy('created_at', 'asc');
    }

    // Get image URL accessor
    public function getImageUrlAttribute()
    {
        if ($this->image) {
            // Check if it's a full URL
            if (filter_var($this->image, FILTER_VALIDATE_URL)) {
                return $this->image;
            }
            // Return storage URL
            return Storage::url($this->image);
        }
        return null;
    }

    // Get full icon class (if using FontAwesome or similar)
    public function getIconClassAttribute()
    {
        if ($this->icon) {
            // If already includes class prefix, return as is
            if (str_contains($this->icon, 'fa-') || str_contains($this->icon, 'lucide-')) {
                return $this->icon;
            }
            // Default to FontAwesome if just icon name
            return 'fas fa-' . $this->icon;
        }
        return 'fas fa-info-circle'; // Default icon
    }

    // Status label for admin interface
    public function getStatusLabelAttribute()
    {
        return $this->is_active ? 'Aktif' : 'Tidak Aktif';
    }

    // Status color for UI
    public function getStatusColorAttribute()
    {
        return $this->is_active ? 'success' : 'secondary';
    }
}
