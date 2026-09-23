<?php

namespace App\Models;

use App\Models\Scopes\TenantViaRelationScope;
use Illuminate\Database\Eloquent\Model;

/**
 * One row per amount actually collected (or awaiting collection) from a resident.
 * This table is the income ledger used by reports, receipts and the dashboard.
 */
class Payment extends Model
{
    public const STATUS_PENDING = 'pending';
    public const STATUS_PAID = 'paid';
    public const STATUS_FAILED = 'failed';

    /** Legacy rows may use these spellings for a successful payment. */
    public const SUCCESS_STATUSES = ['paid', 'completed', 'success'];

    protected $fillable = [
        'customer_id',
        'booking_id',
        'monthly_charge_id',
        'due_id',
        'amount',
        'payment_type',
        'payment_method',
        'transaction_ref',
        'razorpay_payment_id',
        'razorpay_order_id',
        'proof_path',
        'status',
        'paid_at',
        'receipt_number',
        'items',
        'notes',
        'recorded_by',
    ];

    protected $casts = [
        'paid_at' => 'datetime',
        'amount' => 'decimal:2',
        'items' => 'array',
    ];

    protected static function booted(): void
    {
        static::addGlobalScope(new TenantViaRelationScope('customer'));

        static::created(function (Payment $payment) {
            if (! $payment->receipt_number && $payment->isPaid()) {
                $payment->assignReceiptNumber();
            }
        });

        static::updated(function (Payment $payment) {
            if (! $payment->receipt_number && $payment->isPaid()) {
                $payment->assignReceiptNumber();
            }
        });
    }

    public function assignReceiptNumber(): void
    {
        $this->receipt_number = sprintf('RCPT-%s-%05d', ($this->paid_at ?? now())->format('Y'), $this->id);
        $this->saveQuietly();
    }

    public function isPaid(): bool
    {
        return in_array($this->status, self::SUCCESS_STATUSES, true);
    }

    public function scopeSuccessful($query)
    {
        return $query->whereIn('status', self::SUCCESS_STATUSES);
    }

    /** Filter by the date money was received (falls back to created_at for old rows). */
    public function scopeReceivedBetween($query, $start, $end)
    {
        return $query->where(function ($q) use ($start, $end) {
            $q->whereBetween('paid_at', [$start, $end])
                ->orWhere(function ($q) use ($start, $end) {
                    $q->whereNull('paid_at')->whereBetween('created_at', [$start, $end]);
                });
        });
    }

    public function scopeForBranch($query, $branchId)
    {
        if (! $branchId) {
            return $query;
        }

        return $query->where(function ($q) use ($branchId) {
            $q->whereHas('booking.bed.room', fn ($r) => $r->where('branch_id', $branchId))
                ->orWhere(function ($q) use ($branchId) {
                    $q->whereNull('booking_id')
                        ->whereHas('customer.bookings.bed.room', fn ($r) => $r->where('branch_id', $branchId));
                });
        });
    }

    /**
     * Line items shown on the receipt. Older rows without `items` get one line.
     *
     * @return array<int, array{description: string, amount: float}>
     */
    public function lineItems(): array
    {
        if (! empty($this->items)) {
            return array_map(fn ($i) => [
                'description' => $i['description'] ?? 'Payment',
                'amount' => (float) ($i['amount'] ?? 0),
            ], $this->items);
        }

        return [[
            'description' => $this->payment_type ?: 'Payment',
            'amount' => (float) $this->amount,
        ]];
    }

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

    public function due()
    {
        return $this->belongsTo(Due::class);
    }

    public function recorder()
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }
}
