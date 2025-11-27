<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RoomImage extends Model
{
    protected $fillable = ['room_id', 'image_path', 'order'];

    public function room()
    {
        return $this->belongsTo(Room::class);
    }

    public function getSafeUrlAttribute()
    {
        try {
            if (!$this->image_path) {
                return 'https://placehold.co/600x400?text=No+Image';
            }
            return \Illuminate\Support\Facades\Storage::url($this->image_path);
        } catch (\Exception $e) {
            \Log::warning("Failed to get Cloudinary URL for image {$this->id}: " . $e->getMessage());
            return 'https://placehold.co/600x400?text=Image+Not+Found';
        }
    }
}
