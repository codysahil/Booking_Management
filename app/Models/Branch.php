<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Branch extends Model
{
    protected $fillable = ['name', 'address', 'google_map_url'];

    public function rooms()
    {
        return $this->hasMany(Room::class);
    }

    public function employees()
    {
        return $this->hasMany(Employee::class);
    }
}
