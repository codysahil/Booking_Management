<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    protected $fillable = [
        'customer_id',
        'booking_id',
        'monthly_charge_id',
        'amount',
        'payment_type',
        'payment_method',
        'transaction_ref',
        'razorpay_payment_id',
        'razorpay_order_id',
        'proof_path',
        'status',
        'paid_at'
    ];

    protected $casts = [
        'paid_at' => 'datetime',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }

    public function monthlyCharge()
    {
        return $this->belongsTo(MonthlyCharge::class);
    }
}
