<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Expense extends Model
{
    public const CATEGORIES = [
        'Food & Groceries',
        'Staff Salary',
        'Electricity',
        'Water',
        'Rent / Lease',
        'Maintenance & Repairs',
        'Housekeeping & Supplies',
        'Internet & Phone',
        'Taxes & Fees',
        'Marketing',
        'Other',
    ];

    public const PAYMENT_METHODS = [
        'cash' => 'Cash',
        'upi' => 'UPI',
        'bank_transfer' => 'Bank transfer',
        'card' => 'Card',
        'cheque' => 'Cheque',
    ];

    protected $fillable = [
        'branch_id',
        'category',
        'amount',
        'date',
        'description',
        'vendor',
        'payment_method',
        'reference',
        'receipt_path',
        'created_by',
    ];

    protected $casts = [
        'date' => 'date',
        'amount' => 'decimal:2',
    ];

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /** Categories already used plus the built-in list, for filters and suggestions. */
    public static function categoryOptions(): array
    {
        return collect(self::CATEGORIES)
            ->merge(static::query()->distinct()->pluck('category'))
            ->filter()
            ->unique()
            ->sort()
            ->values()
            ->all();
    }

    public function getReceiptUrlAttribute(): ?string
    {
        if (! $this->receipt_path) {
            return null;
        }

        try {
            return \Illuminate\Support\Facades\Storage::url($this->receipt_path);
        } catch (\Throwable $e) {
            return null;
        }
    }
}
