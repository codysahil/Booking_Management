<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Request extends Model
{
    protected $fillable = [
        'customer_id',
        'type',
        'subject',
        'description',
        'status',
        'attachment_path'
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }
}
