<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Booking extends Model
{
    protected $fillable = [
        'customer_id',
        'bed_id',
        'booking_reference',
        'check_in_date',
        'check_out_date',
        'status',
        'advance_paid'
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function bed()
    {
        return $this->belongsTo(Bed::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }
}
