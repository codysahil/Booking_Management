<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;

class Branch extends Model
{
    use BelongsToTenant;

    protected $fillable = ['tenant_id', 'name', 'address', 'google_map_url'];

    public function rooms()
    {
        return $this->hasMany(Room::class);
    }

    public function employees()
    {
        return $this->hasMany(Employee::class);
    }
}
