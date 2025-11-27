<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Room extends Model
{
    protected $fillable = ['branch_id', 'room_number', 'capacity', 'type', 'gender_allowed', 'image_path'];

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function beds()
    {
        return $this->hasMany(Bed::class);
    }

    public function images()
    {
        return $this->hasMany(RoomImage::class)->orderBy('order');
    }

    public function getSafeUrlAttribute()
    {
        try {
            if (!$this->image_path) {
                // Try to get the first image from the relation if main image is missing
                $firstImage = $this->images->first();
                if ($firstImage) {
                    return $firstImage->safe_url;
                }
                return 'https://placehold.co/600x400?text=No+Image';
            }
            return \Illuminate\Support\Facades\Storage::url($this->image_path);
        } catch (\Exception $e) {
            \Log::warning("Failed to get Cloudinary URL for room {$this->id}: " . $e->getMessage());
            return 'https://placehold.co/600x400?text=Image+Not+Found';
        }
    }
}
