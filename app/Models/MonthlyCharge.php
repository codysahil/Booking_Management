<?php

namespace App\Models;

use App\Models\Scopes\TenantViaRelationScope;
use Illuminate\Database\Eloquent\Model;

class MonthlyCharge extends Model
{
    /**
     * A resident's rent for their check-in month should only cover the days
     * they actually stayed, not the full month. Every later month is a full
     * month by definition (they're only removed from billing by vacating,
     * which doesn't generate new charges) — so this only ever prorates the
     * one month that matches the booking's own check-in date.
     */
    public static function isProratedMonth(Booking $booking, string $month): bool
    {
        $checkIn = $booking->check_in_date;

        return $checkIn && $checkIn->format('Y-m') === $month && $checkIn->day > 1;
    }

    public static function proratedRentAmount(Booking $booking, string $month, float $fullRent): float
    {
        if (! self::isProratedMonth($booking, $month)) {
            return $fullRent;
        }

        $checkIn = $booking->check_in_date;
        $daysInMonth = $checkIn->daysInMonth;
        $daysStayed = $daysInMonth - $checkIn->day + 1;

        return round($fullRent * $daysStayed / $daysInMonth, 2);
    }

    protected static function booted(): void
    {
        static::addGlobalScope(new TenantViaRelationScope('customer'));
    }

    protected $fillable = [
        'customer_id',
        'booking_id',
        'month_year',
        'rent_amount',
        'is_prorated',
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
        'is_prorated' => 'boolean',
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

    public function scopeUnpaid($query)
    {
        return $query->whereIn('status', ['pending', 'overdue']);
    }

    public function getPeriodLabelAttribute(): string
    {
        return \Carbon\Carbon::parse($this->month_year . '-01')->format('F Y');
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
        return $this->status === 'overdue'
            || ($this->status === 'pending' && $this->due_date && $this->due_date->endOfDay()->isPast());
    }

}
