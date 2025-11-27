<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Customer extends Authenticatable
{
    protected $fillable = [
        'customer_code',
        'name',
        'email',
        'phone',
        'password',
        'dob',
        'address',
        'guardian_phone',
        'work_details',
        'photo_path',
        'id_proof_path',
        'is_active'
    ];

    protected $hidden = ['password'];

    protected $casts = [
        'dob' => 'date',
        'is_active' => 'boolean',
    ];

    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function requests()
    {
        return $this->hasMany(Request::class);
    }

    public function monthlyCharges()
    {
        return $this->hasMany(MonthlyCharge::class);
    }

    public function dues()
    {
        return $this->hasMany(Due::class);
    }

    public function getPendingChargesAmount()
    {
        return $this->monthlyCharges()->where('status', 'pending')->sum('total_amount');
    }

    public function getPendingDuesAmount()
    {
        return $this->dues()->where('status', 'pending')->sum('amount');
    }

    public function getTotalPendingAmount()
    {
        return $this->getPendingChargesAmount() + $this->getPendingDuesAmount();
    }
}
