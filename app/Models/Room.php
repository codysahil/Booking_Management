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
}
