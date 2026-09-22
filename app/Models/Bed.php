<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Bed extends Model
{
    protected $fillable = ['room_id', 'bed_number', 'monthly_rent', 'status', 'reserved_until'];

    protected $casts = [
        'reserved_until' => 'datetime',
    ];

    public function room()
    {
        return $this->belongsTo(Room::class);
    }

    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }
}
