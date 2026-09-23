<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class BookingController extends Controller
{
    public function index(Request $request)
    {
        $query = Booking::with(['customer', 'bed.room.branch']);

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('booking_reference', 'like', "%{$search}%")
                    ->orWhereHas('customer', function ($q) use ($search) {
                        $q->where('name', 'like', "%{$search}%")
                            ->orWhere('phone', 'like', "%{$search}%")
                            ->orWhere('customer_code', 'like', "%{$search}%");
                    });
            });
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by branch
        if ($request->filled('branch_id')) {
            $query->whereHas('bed.room.branch', function ($q) use ($request) {
                $q->where('id', $request->branch_id);
            });
        }

        $bookings = $query->latest()->paginate(15);
        $branches = \App\Models\Branch::all();

        return view('admin.bookings.index', compact('bookings', 'branches'));
    }

    public function updateStatus(Request $request, Booking $booking)
    {
        $request->validate([
            'status' => ['required', Rule::in(array_keys(Booking::STATUSES))],
        ]);

        $booking->update(['status' => $request->status]);

        // Update bed status based on booking status
        if ($request->status === Booking::STATUS_COMPLETED) {
            $booking->update(['check_out_date' => $booking->check_out_date ?? now()]);
            $booking->bed->update(['status' => 'vacant', 'reserved_until' => null]);
        } elseif ($request->status === Booking::STATUS_CANCELLED) {
            $booking->bed->update(['status' => 'vacant', 'reserved_until' => null]);
        } elseif ($request->status === Booking::STATUS_ACTIVE) {
            // Moving out of pending_payment (e.g. staff confirming a cash/manual
            // payment through this dropdown instead of the check-in flow) must also
            // clear the payment hold, or the bed stays stuck at "reserved" forever —
            // release-expired only looks at bookings still in pending_payment.
            $booking->bed?->update(['reserved_until' => null]);
        }

        return back()->with('success', 'Booking status updated successfully!');
    }
}
