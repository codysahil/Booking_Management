<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Booking extends Model
{
    /** Created online, held pending the customer completing payment. Auto-cancelled if the hold expires. */
    public const STATUS_PENDING_PAYMENT = 'pending_payment';

    /** Paid (or a trusted walk-in) — a real, current stay. */
    public const STATUS_ACTIVE = 'active';

    public const STATUS_COMPLETED = 'completed';

    public const STATUS_CANCELLED = 'cancelled';

    public const STATUSES = [
        self::STATUS_PENDING_PAYMENT => 'Awaiting Payment',
        self::STATUS_ACTIVE => 'Active',
        self::STATUS_COMPLETED => 'Completed',
        self::STATUS_CANCELLED => 'Cancelled',
    ];

    protected $fillable = [
        'customer_id',
        'bed_id',
        'booking_reference',
        'check_in_date',
        'check_out_date',
        'status',
        'advance_paid'
    ];

    protected $casts = [
        'check_in_date' => 'date',
        'check_out_date' => 'date',
        'advance_paid' => 'decimal:2',
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

    public function getStatusLabelAttribute(): string
    {
        return self::STATUSES[$this->status] ?? ucfirst((string) $this->status);
    }

    public function scopeAwaitingPayment($query)
    {
        return $query->where('status', self::STATUS_PENDING_PAYMENT);
    }
}
