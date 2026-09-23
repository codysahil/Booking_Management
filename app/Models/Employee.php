<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;

class Employee extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'employee_code',
        'name',
        'role',
        'phone',
        'address',
        'photo_path',
        'proof_path',
        'branch_id'
    ];

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }
}
