<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use Illuminate\Http\Request;

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
            'status' => 'required|in:pending,confirmed,checked_in,cancelled',
        ]);

        $booking->update(['status' => $request->status]);

        // Update bed status based on booking status
        if ($request->status === 'confirmed' || $request->status === 'checked_in') {
            $booking->bed->update(['status' => 'occupied']);
        } elseif ($request->status === 'cancelled') {
            $booking->bed->update(['status' => 'vacant']);
        }

        return back()->with('success', 'Booking status updated successfully!');
    }
}
