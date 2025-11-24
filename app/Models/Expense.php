<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Branch;
use App\Models\User;

class Expense extends Model
{
    protected $fillable = [
        'branch_id',
        'category',
        'amount',
        'date',
        'description',
        'created_by'
    ];

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
