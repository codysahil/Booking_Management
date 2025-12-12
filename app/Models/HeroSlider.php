<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class HeroSlider extends Model
{
    protected $fillable = [
        'image_path',
        'title',
        'description',
        'order',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Get the full URL for the slider image
     * Handles both local storage and Cloudinary
     */
    public function getImageUrlAttribute(): string
    {
        if (!$this->image_path) {
            return 'https://placehold.co/1920x600?text=Slider+Image';
        }

        try {
            return Storage::url($this->image_path);
        } catch (\Exception $e) {
            \Log::warning("Failed to get URL for slider image {$this->id}: " . $e->getMessage());
            return 'https://placehold.co/1920x600?text=Image+Not+Found';
        }
    }
}
