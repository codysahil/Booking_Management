<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Room extends Model
{
    protected $fillable = ['branch_id', 'room_number', 'capacity', 'type', 'gender_allowed'];

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function beds()
    {
        return $this->hasMany(Bed::class);
    }
}
