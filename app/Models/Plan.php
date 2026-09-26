<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * A billing plan the super admin offers to tenants. Price/interval here are
 * the source of truth; razorpay_plan_id links to the matching Plan on
 * Razorpay's side (created via the Subscriptions API) once one exists — a
 * plan can be defined locally before it has one, but a real Subscription
 * can't be started against it until it does.
 */
class Plan extends Model
{
    use HasFactory;

    public const INTERVAL_MONTHLY = 'monthly';
    public const INTERVAL_YEARLY = 'yearly';

    public const INTERVALS = [
        self::INTERVAL_MONTHLY => 'Monthly',
        self::INTERVAL_YEARLY => 'Yearly',
    ];

    protected $fillable = [
        'name',
        'slug',
        'price',
        'billing_interval',
        'razorpay_plan_id',
        'max_branches',
        'max_beds',
        'max_staff',
        'is_active',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function subscriptions()
    {
        return $this->hasMany(Subscription::class);
    }

    public function hasRazorpayPlan(): bool
    {
        return filled($this->razorpay_plan_id);
    }
}
