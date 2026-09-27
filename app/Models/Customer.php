<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Customer extends Authenticatable
{
    use Notifiable, BelongsToTenant;

    public const VERIFICATION_PENDING = 'pending';

    public const VERIFICATION_SUBMITTED = 'submitted';

    public const VERIFICATION_VERIFIED = 'verified';

    public const VERIFICATION_REJECTED = 'rejected';

    public const VERIFICATION_STATUSES = [
        self::VERIFICATION_PENDING => 'Not submitted',
        self::VERIFICATION_SUBMITTED => 'Submitted to police',
        self::VERIFICATION_VERIFIED => 'Verified',
        self::VERIFICATION_REJECTED => 'Rejected',
    ];

    public const ID_PROOF_TYPES = ['Aadhaar Card', 'Passport', 'Voter ID', 'Driving Licence'];

    protected $fillable = [
        'tenant_id',
        'customer_code',
        'name',
        'email',
        'phone',
        'password',
        'dob',
        'address',
        'guardian_phone',
        'work_details',
        'photo_path',
        'id_proof_path',
        'id_proof_type',
        'id_proof_number',
        'police_verification_status',
        'police_verification_submitted_at',
        'police_verification_verified_at',
        'police_verification_notes',
        'is_active'
    ];

    protected $hidden = ['password'];

    protected $casts = [
        'dob' => 'date',
        'is_active' => 'boolean',
        'police_verification_submitted_at' => 'datetime',
        'police_verification_verified_at' => 'datetime',
    ];

    public function getVerificationStatusLabelAttribute(): string
    {
        return self::VERIFICATION_STATUSES[$this->police_verification_status] ?? ucfirst((string) $this->police_verification_status);
    }

    public function getVerificationStatusColorAttribute(): string
    {
        return match ($this->police_verification_status) {
            self::VERIFICATION_SUBMITTED => 'bg-blue-100 text-blue-800',
            self::VERIFICATION_VERIFIED => 'bg-green-100 text-green-800',
            self::VERIFICATION_REJECTED => 'bg-red-100 text-red-800',
            default => 'bg-gray-100 text-gray-800',
        };
    }

    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function requests()
    {
        return $this->hasMany(Request::class);
    }

    public function monthlyCharges()
    {
        return $this->hasMany(MonthlyCharge::class);
    }

    public function dues()
    {
        return $this->hasMany(Due::class);
    }

    public function activeBooking()
    {
        return $this->hasOne(Booking::class)->where('status', 'active')->latestOfMany();
    }

    public function getPendingChargesAmount()
    {
        return $this->monthlyCharges()->whereIn('status', ['pending', 'overdue'])->sum('total_amount');
    }

    public function getPendingDuesAmount()
    {
        return $this->dues()->where('status', 'pending')->sum('amount');
    }

    public function getTotalPendingAmount()
    {
        return $this->getPendingChargesAmount() + $this->getPendingDuesAmount();
    }

    public function getSafePhotoUrlAttribute()
    {
        try {
            if (!$this->photo_path) {
                return 'https://placehold.co/200x200?text=No+Photo';
            }
            return \Illuminate\Support\Facades\Storage::url($this->photo_path);
        } catch (\Exception $e) {
            \Log::warning("Failed to get Cloudinary URL for customer photo {$this->id}: " . $e->getMessage());
            return 'https://placehold.co/200x200?text=Photo+Not+Found';
        }
    }

    public function getSafeIdProofUrlAttribute()
    {
        try {
            if (!$this->id_proof_path) {
                return '#';
            }
            return \Illuminate\Support\Facades\Storage::url($this->id_proof_path);
        } catch (\Exception $e) {
            \Log::warning("Failed to get Cloudinary URL for customer proof {$this->id}: " . $e->getMessage());
            return '#';
        }
    }
}
