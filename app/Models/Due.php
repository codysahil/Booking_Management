<?php

namespace App\Models;

use App\Models\Scopes\TenantViaRelationScope;
use Illuminate\Database\Eloquent\Model;

class Due extends Model
{
    protected static function booted(): void
    {
        static::addGlobalScope(new TenantViaRelationScope('customer'));
    }

    public const TYPES = [
        'fine' => 'Fine',
        'eb' => 'Electricity (EB)',
        'late_fee' => 'Late fee',
        'damage' => 'Damage',
        'maintenance' => 'Maintenance',
        'other' => 'Other',
    ];

    protected $fillable = [
        'customer_id',
        'due_type',
        'title',
        'description',
        'amount',
        'status',
        'due_date',
        'paid_date',
        'payment_method',
        'transaction_id',
    ];

    protected $casts = [
        'due_date' => 'date',
        'paid_date' => 'date',
        'amount' => 'decimal:2',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function getTypeLabelAttribute(): string
    {
        return self::TYPES[$this->due_type] ?? ucfirst(str_replace('_', ' ', (string) $this->due_type));
    }

    public function isPending()
    {
        return $this->status === 'pending';
    }

    public function isPaid()
    {
        return $this->status === 'paid';
    }

}
