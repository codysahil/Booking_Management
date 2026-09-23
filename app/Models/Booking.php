<?php

namespace App\Models;

use App\Models\Scopes\TenantViaRelationScope;
use Illuminate\Database\Eloquent\Model;

class Booking extends Model
{
    protected static function booted(): void
    {
        static::addGlobalScope(new TenantViaRelationScope('customer'));
    }

    /** A trusted walk-in — a real, current stay. */
    public const STATUS_ACTIVE = 'active';

    public const STATUS_COMPLETED = 'completed';

    public const STATUS_CANCELLED = 'cancelled';

    public const STATUSES = [
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

    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_ACTIVE => 'bg-green-100 text-green-800',
            self::STATUS_COMPLETED => 'bg-blue-100 text-blue-800',
            self::STATUS_CANCELLED => 'bg-red-100 text-red-800',
            default => 'bg-gray-100 text-gray-800',
        };
    }
}
