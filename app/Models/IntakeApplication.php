<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;

class IntakeApplication extends Model
{
    use BelongsToTenant;

    public const STATUS_PENDING = 'pending';

    public const STATUS_CONVERTED = 'converted';

    public const STATUS_REJECTED = 'rejected';

    public const STATUSES = [
        self::STATUS_PENDING => 'Pending review',
        self::STATUS_CONVERTED => 'Converted to resident',
        self::STATUS_REJECTED => 'Rejected',
    ];

    protected $fillable = [
        'tenant_id',
        'branch_id',
        'name',
        'phone',
        'email',
        'dob',
        'address',
        'guardian_phone',
        'work_details',
        'photo_path',
        'id_proof_path',
        'preferred_move_in_date',
        'status',
        'admin_notes',
        'customer_id',
        'reviewed_at',
    ];

    protected $casts = [
        'dob' => 'date',
        'preferred_move_in_date' => 'date',
        'reviewed_at' => 'datetime',
    ];

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    public function getStatusLabelAttribute(): string
    {
        return self::STATUSES[$this->status] ?? ucfirst((string) $this->status);
    }

    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_CONVERTED => 'bg-green-100 text-green-800',
            self::STATUS_REJECTED => 'bg-red-100 text-red-800',
            default => 'bg-yellow-100 text-yellow-800',
        };
    }

    public function getSafePhotoUrlAttribute()
    {
        if (! $this->photo_path) {
            return null;
        }

        try {
            return \Illuminate\Support\Facades\Storage::url($this->photo_path);
        } catch (\Exception $e) {
            return null;
        }
    }

    public function getSafeIdProofUrlAttribute()
    {
        if (! $this->id_proof_path) {
            return null;
        }

        try {
            return \Illuminate\Support\Facades\Storage::url($this->id_proof_path);
        } catch (\Exception $e) {
            return null;
        }
    }
}
