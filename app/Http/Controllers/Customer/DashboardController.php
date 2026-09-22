<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $customer = Auth::guard('customer')->user();
        $customer->load(['bookings.bed.room.branch', 'payments', 'requests']);

        $announcements = Announcement::visibleTo($customer)->limit(5)->get();

        // Get pending monthly charges (Rent + EB)
        $pendingCharges = $customer->monthlyCharges()
            ->where('status', 'pending')
            ->orderBy('month_year', 'desc')
            ->get();
        
        // Get pending dues (custom charges)
        $pendingDues = $customer->dues()
            ->where('status', 'pending')
            ->orderBy('due_date', 'asc')
            ->get();
        
        // Calculate totals
        $totalPendingCharges = $pendingCharges->sum('total_amount');
        $totalPendingDues = $pendingDues->sum('amount');
        $totalPending = $totalPendingCharges + $totalPendingDues;
        
        // Get active booking
        $activeBooking = $customer->bookings()->where('status', 'active')->first();
        
        // Calculate months stayed
        $totalMonthsStayed = 0;
        if ($activeBooking) {
            $checkInDate = \Carbon\Carbon::parse($activeBooking->check_in_date);
            $totalMonthsStayed = $checkInDate->diffInMonths(now());
        }
        
        $totalPayments = $customer->payments()->where('status', 'paid')->sum('amount');

        return view('customer.dashboard', compact(
            'customer',
            'pendingCharges',
            'pendingDues',
            'totalPending',
            'activeBooking',
            'totalMonthsStayed',
            'totalPayments',
            'announcements'
        ));
    }

    public function showBooking($id)
    {
        $customer = Auth::guard('customer')->user();
        $booking = $customer->bookings()->with('bed.room.branch')->findOrFail($id);

        return view('customer.booking-details', compact('booking'));
    }

    public function announcements()
    {
        $customer = Auth::guard('customer')->user();

        $announcements = Announcement::visibleTo($customer)->paginate(15);

        return view('customer.announcements', compact('announcements'));
    }
}
