<?php

namespace App\Models;

use App\Models\Scopes\TenantViaRelationScope;
use Illuminate\Database\Eloquent\Model;

/**
 * A resident's request to the office: room swap, vacation notice, refund,
 * maintenance/service or a general complaint.
 */
class Request extends Model
{
    protected static function booted(): void
    {
        static::addGlobalScope(new TenantViaRelationScope('customer'));
    }

    public const TYPES = [
        'swap' => 'Room swap',
        'vacation' => 'Vacation notice',
        'refund' => 'Refund',
        'service' => 'Maintenance / service',
        'complaint' => 'Complaint',
    ];

    public const STATUSES = [
        'pending' => 'Pending',
        'approved' => 'Approved',
        'rejected' => 'Rejected',
        'completed' => 'Completed',
    ];

    protected $fillable = [
        'customer_id',
        'type',
        'subject',
        'description',
        'status',
        'attachment_path',
        'preferred_date',
        'admin_response',
        'handled_by',
        'resolved_at',
    ];

    protected $casts = [
        'preferred_date' => 'date',
        'resolved_at' => 'datetime',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function handler()
    {
        return $this->belongsTo(User::class, 'handled_by');
    }

    public function scopeOpen($query)
    {
        return $query->where('status', 'pending');
    }

    /** Old rows stored the type capitalised ("Swap", "Service"); normalise for display. */
    public function getTypeKeyAttribute(): string
    {
        return strtolower((string) $this->type);
    }

    public function getTypeLabelAttribute(): string
    {
        return self::TYPES[$this->type_key] ?? ucfirst((string) $this->type);
    }

    public function getStatusLabelAttribute(): string
    {
        return self::STATUSES[$this->status] ?? ucfirst((string) $this->status);
    }

    public function getAttachmentUrlAttribute(): ?string
    {
        if (! $this->attachment_path) {
            return null;
        }

        try {
            return \Illuminate\Support\Facades\Storage::url($this->attachment_path);
        } catch (\Throwable $e) {
            return null;
        }
    }

}
