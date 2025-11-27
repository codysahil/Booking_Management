<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MonthlyCharge extends Model
{
    protected $fillable = [
        'customer_id',
        'booking_id',
        'month_year',
        'rent_amount',
        'eb_amount',
        'other_charges',
        'other_charges_description',
        'total_amount',
        'status',
        'due_date',
        'paid_date',
        'payment_method',
        'transaction_id',
    ];

    protected $casts = [
        'due_date' => 'date',
        'paid_date' => 'date',
        'rent_amount' => 'decimal:2',
        'eb_amount' => 'decimal:2',
        'other_charges' => 'decimal:2',
        'total_amount' => 'decimal:2',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }

    public function isPending()
    {
        return $this->status === 'pending';
    }

    public function isPaid()
    {
        return $this->status === 'paid';
    }

    public function isOverdue()
    {
        return $this->status === 'overdue' || ($this->status === 'pending' && $this->due_date->isPast());
    }
}
