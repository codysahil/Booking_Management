<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\Room;
use App\Models\Bed;
use App\Models\Booking;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class BookingController extends Controller
{
    public function index()
    {
        $branches = Branch::withCount(['rooms', 'employees'])->get();
        return view('public.home', compact('branches'));
    }

    public function showBranch(Branch $branch)
    {
        $branch->load([
            'rooms' => function ($query) {
                $query->withCount([
                    'beds' => function ($q) {
                        $q->where('status', 'vacant')
                            ->where(function ($query) {
                                $query->whereNull('reserved_until')
                                    ->orWhere('reserved_until', '<', now());
                            });
                    }
                ]);
            }
        ]);
        return view('public.branch', compact('branch'));
    }

    public function showRoom(Branch $branch, Room $room)
    {
        $room->load('beds');
        return view('public.room', compact('branch', 'room'));
    }

    public function selectBeds(Request $request)
    {
        $request->validate([
            'bed_ids' => 'required|array|min:1',
            'bed_ids.*' => 'exists:beds,id',
        ]);

        $beds = Bed::whereIn('id', $request->bed_ids)
            ->where('status', 'vacant')
            ->where(function ($query) {
                $query->whereNull('reserved_until')
                    ->orWhere('reserved_until', '<', now());
            })
            ->get();

        if ($beds->count() !== count($request->bed_ids)) {
            return back()->withErrors(['beds' => 'Some selected beds are no longer available.']);
        }

        // Reserve beds for 10 minutes
        $reservationKey = 'reservation_' . Str::random(16);
        foreach ($beds as $bed) {
            $bed->update(['reserved_until' => now()->addMinutes(10)]);
        }

        // Store reservation in session
        session([
            'reservation_key' => $reservationKey,
            'selected_bed_ids' => $request->bed_ids,
            'reservation_expires_at' => now()->addMinutes(10)->toIso8601String(),
        ]);

        return redirect()->route('booking.checkout');
    }

    public function checkout()
    {
        if (!session('selected_bed_ids')) {
            return redirect()->route('home')->withErrors(['error' => 'No beds selected.']);
        }

        $beds = Bed::with('room.branch')->whereIn('id', session('selected_bed_ids'))->get();

        // Calculate advance: 1 month rent per bed OR ₹3,000 minimum
        $totalRent = $beds->sum('monthly_rent');
        $advance = max($totalRent, 3000);

        return view('public.checkout', compact('beds', 'advance'));
    }

    public function processPayment(Request $request)
    {
        $request->validate([
            'phone' => 'required|string|regex:/^[0-9]{10}$/',
            'name' => 'nullable|string|max:255',
            'check_in_date' => 'required|date|after_or_equal:today',
        ]);

        if (!session('selected_bed_ids')) {
            return redirect()->route('home')->withErrors(['error' => 'Session expired.']);
        }

        $beds = Bed::whereIn('id', session('selected_bed_ids'))->get();

        // Calculate advance
        $totalRent = $beds->sum('monthly_rent');
        $advance = max($totalRent, 3000);

        DB::beginTransaction();
        try {
            // Create customer with minimal info
            $customerCode = 'SS-' . date('Y') . '-' . str_pad(Customer::count() + 1, 4, '0', STR_PAD_LEFT);
            $customer = Customer::create([
                'customer_code' => $customerCode,
                'name' => $request->name ?: 'Customer ' . $customerCode,
                'phone' => $request->phone,
                'email' => $request->email,
                'password' => bcrypt($request->phone), // Use phone as default password
                'dob' => now()->subYears(20), // Dummy DOB - will be updated on check-in
                'address' => $request->address ?: 'To be collected on check-in',
                'guardian_phone' => $request->phone, // Same as customer for now
            ]);

            // Create bookings for each bed
            $bookingReference = 'BK-' . strtoupper(Str::random(8));
            foreach ($beds as $bed) {
                $booking = $customer->bookings()->create([
                    'booking_reference' => $bookingReference,
                    'bed_id' => $bed->id,
                    'check_in_date' => $request->check_in_date,
                    'status' => 'pending', // Changed to pending since payment not done yet
                    'advance_paid' => 0, // Will be updated when payment is made
                ]);

                // Update bed status to reserved (not occupied yet)
                $bed->update([
                    'status' => 'reserved',
                    'reserved_until' => null,
                ]);
            }

            // Create payment record as pending
            $customer->payments()->create([
                'booking_id' => $booking->id,
                'amount' => $advance,
                'payment_type' => 'Advance',
                'payment_method' => 'pending',
                'transaction_ref' => 'PENDING-' . strtoupper(Str::random(12)),
                'status' => 'pending',
                'paid_at' => null,
            ]);

            DB::commit();

            // Clear session
            session()->forget(['selected_bed_ids', 'reservation_key', 'reservation_expires_at']);

            return redirect()->route('booking.confirmation', $booking)->with('success', 'Booking confirmed!');
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Booking failed: ' . $e->getMessage());
            \Log::error('Stack trace: ' . $e->getTraceAsString());
            return back()->withErrors(['error' => 'Booking failed. Please try again: ' . $e->getMessage()])->withInput();
        }
    }

    public function confirmation(Booking $booking)
    {
        $booking->load(['customer', 'bed.room.branch']);

        // Get all bookings with same reference
        $relatedBookings = Booking::where('booking_reference', $booking->booking_reference)
            ->with('bed.room')
            ->get();

        return view('public.confirmation', compact('booking', 'relatedBookings'));
    }
}
