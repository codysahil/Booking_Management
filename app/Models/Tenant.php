<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * One hostel/PG business subscribing to the platform. Every hostel-owned
 * record belongs to exactly one tenant; nothing is shared across tenants.
 */
class Tenant extends Model
{
    use HasFactory;

    public const STATUS_ACTIVE = 'active';
    public const STATUS_SUSPENDED = 'suspended';
    public const STATUS_CANCELLED = 'cancelled';

    protected $fillable = [
        'name',
        'slug',
        'status',
        'owner_name',
        'owner_email',
        'owner_phone',
        'notes',
        'trial_ends_at',
    ];

    protected $casts = [
        'trial_ends_at' => 'datetime',
    ];

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function customers()
    {
        return $this->hasMany(Customer::class);
    }

    public function branches()
    {
        return $this->hasMany(Branch::class);
    }

    public function settings()
    {
        return $this->hasMany(Setting::class);
    }

    public function heroSliders()
    {
        return $this->hasMany(HeroSlider::class);
    }

    public function employees()
    {
        return $this->hasMany(Employee::class);
    }

    public function expenses()
    {
        return $this->hasMany(Expense::class);
    }

    public function announcements()
    {
        return $this->hasMany(Announcement::class);
    }

    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE;
    }

    public function onTrial(): bool
    {
        return $this->trial_ends_at !== null && $this->trial_ends_at->isFuture();
    }
}
