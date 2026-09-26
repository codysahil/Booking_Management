<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * One tenant's current billing state — separate from Tenant.status (a hard
 * kill-switch the super admin controls directly) because this tracks
 * whether the tenant may currently WRITE data, driven by Razorpay's
 * subscription lifecycle via webhooks rather than a manual toggle.
 */
class Subscription extends Model
{
    use HasFactory;

    public const STATUS_TRIALING = 'trialing';
    public const STATUS_ACTIVE = 'active';
    public const STATUS_PAST_DUE = 'past_due';
    public const STATUS_EXPIRED = 'expired';
    public const STATUS_CANCELLED = 'cancelled';
    public const STATUS_INTERNAL = 'internal';

    public const STATUSES = [
        self::STATUS_TRIALING => 'Trialing',
        self::STATUS_ACTIVE => 'Active',
        self::STATUS_PAST_DUE => 'Past due',
        self::STATUS_EXPIRED => 'Expired',
        self::STATUS_CANCELLED => 'Cancelled',
        self::STATUS_INTERNAL => 'Internal',
    ];

    protected $fillable = [
        'tenant_id',
        'plan_id',
        'razorpay_subscription_id',
        'razorpay_customer_id',
        'status',
        'current_period_start',
        'current_period_end',
        'grace_ends_at',
        'cancel_at_period_end',
        'cancelled_at',
        'last_webhook_event',
        'meta',
    ];

    protected $casts = [
        'current_period_start' => 'date',
        'current_period_end' => 'date',
        'grace_ends_at' => 'datetime',
        'cancelled_at' => 'datetime',
        'cancel_at_period_end' => 'boolean',
        'meta' => 'array',
    ];

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    public function plan()
    {
        return $this->belongsTo(Plan::class);
    }

    /** May this tenant currently write data? GET/HEAD requests are never gated by this. */
    public function isWritable(): bool
    {
        return match ($this->status) {
            self::STATUS_INTERNAL, self::STATUS_TRIALING, self::STATUS_ACTIVE => true,
            self::STATUS_PAST_DUE => $this->grace_ends_at !== null && $this->grace_ends_at->isFuture(),
            default => false,
        };
    }

    public function statusLabel(): string
    {
        return self::STATUSES[$this->status] ?? ucfirst($this->status);
    }
}
