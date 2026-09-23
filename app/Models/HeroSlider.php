<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class HeroSlider extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
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

        if (str_starts_with($this->image_path, 'http://') || str_starts_with($this->image_path, 'https://')) {
            return $this->image_path;
        }

        try {
            return Storage::url($this->image_path);
        } catch (\Exception $e) {
            \Log::warning("Failed to get URL for slider image {$this->id}: " . $e->getMessage());
            return 'https://placehold.co/1920x600?text=Image+Not+Found';
        }
    }
}
