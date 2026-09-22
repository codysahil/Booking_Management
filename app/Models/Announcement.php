<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** A notice from the office shown on residents' dashboards. */
class Announcement extends Model
{
    protected $fillable = ['title', 'body', 'branch_id', 'is_pinned', 'expires_on', 'created_by'];

    protected $casts = [
        'is_pinned' => 'boolean',
        'expires_on' => 'date',
    ];

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function scopeCurrent($query)
    {
        return $query->where(function ($q) {
            $q->whereNull('expires_on')->orWhereDate('expires_on', '>=', today());
        });
    }

    /** Notices for everyone plus those for the resident's current branch. */
    public function scopeVisibleTo($query, Customer $customer)
    {
        $branchId = $customer->bookings()
            ->where('status', 'active')
            ->with('bed.room')
            ->get()
            ->pluck('bed.room.branch_id')
            ->filter()
            ->all();

        return $query->current()->where(function ($q) use ($branchId) {
            $q->whereNull('branch_id')->orWhereIn('branch_id', $branchId);
        })->orderByDesc('is_pinned')->latest();
    }
}
