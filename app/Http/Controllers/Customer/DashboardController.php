<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $customer = Auth::guard('customer')->user();
        $customer->load(['bookings.bed.room.branch', 'payments', 'requests']);

        // Calculate Dues (Mock Logic for now)
        $rentDue = 0;
        $ebDue = 0;

        if ($customer->bookings->isNotEmpty()) {
            $booking = $customer->bookings->first();
            if ($booking->status === 'active') {
                $rentDue = $booking->bed->monthly_rent;
            }
        }

        return view('customer.dashboard', compact('customer', 'rentDue', 'ebDue'));
    }
}
