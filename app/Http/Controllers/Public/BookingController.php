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
use Illuminate\Support\Str;

class BookingController extends Controller
{
    public function index()
    {
        $branches = Branch::withCount(['rooms', 'employees'])->get();
        
        // Gracefully handle missing hero_sliders table
        try {
            $sliders = \App\Models\HeroSlider::where('is_active', true)
                ->orderBy('order')
                ->get();
        } catch (\Exception $e) {
            $sliders = collect(); // Empty collection if table doesn't exist
        }
        
        return view('public.home', compact('branches', 'sliders'));
    }

    public function showBranch(Branch $branch)
    {
        $branch->load([
            'rooms' => function ($query) {
                $query->with(['images' => function($q) {
                    $q->orderBy('order');
                }])->withCount([
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
        $room->load(['beds', 'images' => function($query) {
            $query->orderBy('order');
        }]);
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
            \Log::warning('Checkout accessed without session bed_ids');
            return redirect()->route('home')->withErrors(['error' => 'No beds selected. Please select beds again.']);
        }

        $beds = Bed::with('room.branch')->whereIn('id', session('selected_bed_ids'))->get();
        
        if ($beds->isEmpty()) {
            \Log::warning('No beds found for session bed_ids', ['bed_ids' => session('selected_bed_ids')]);
            return redirect()->route('home')->withErrors(['error' => 'Selected beds not found. Please try again.']);
        }

        // Calculate advance: 1 month rent per bed OR ₹3,000 minimum
        $totalRent = $beds->sum('monthly_rent');
        $advance = max($totalRent, 3000);
        
        \Log::info('Checkout page loaded', [
            'bed_count' => $beds->count(),
            'advance' => $advance,
        ]);

        return view('public.checkout', compact('beds', 'advance'));
    }

    public function processPayment(Request $request)
    {
        $request->validate([
            'phone' => 'required|string|regex:/^[0-9]{10}$/',
            'name' => 'nullable|string|max:255',
            'check_in_date' => 'required|date|after_or_equal:today',
            'bed_ids' => 'required|array|min:1', // Add bed_ids to request
            'bed_ids.*' => 'exists:beds,id',
        ]);

        // Use bed_ids from request instead of session (more reliable)
        $bedIds = $request->bed_ids;
        
        // Log for debugging
        \Log::info('Processing payment', [
            'bed_ids' => $bedIds,
            'phone' => $request->phone,
            'session_bed_ids' => session('selected_bed_ids'),
        ]);

        $beds = Bed::whereIn('id', $bedIds)->get();
        
        if ($beds->isEmpty()) {
            return redirect()->route('home')->withErrors(['error' => 'No beds found. Please try again.']);
        }

        // Calculate advance
        $totalRent = $beds->sum('monthly_rent');
        $advance = max($totalRent, 3000);

        // Note: Removed DB transaction due to Neon PostgreSQL serverless connection pooling issues
        try {
            // Create customer with minimal info and random unique code
            $customerCode = $this->generateUniqueCustomerCode();
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
                    'status' => 'active', // Active status for new bookings
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

            // Clear session
            session()->forget(['selected_bed_ids', 'reservation_key', 'reservation_expires_at']);

            \Log::info('Booking created successfully', [
                'booking_id' => $booking->id,
                'booking_reference' => $bookingReference,
                'customer_code' => $customerCode,
            ]);

            return redirect()->route('booking.confirmation', $booking)->with('success', 'Booking confirmed!');
        } catch (\Exception $e) {
            \Log::error('Booking failed: ' . $e->getMessage());
            \Log::error('Stack trace: ' . $e->getTraceAsString());
            \Log::error('Request data: ', $request->all());
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
        
        \Log::info('Confirmation page loaded', [
            'booking_id' => $booking->id,
            'booking_reference' => $booking->booking_reference,
            'customer_code' => $booking->customer->customer_code,
        ]);

        return view('public.confirmation', compact('booking', 'relatedBookings'));
    }

    /**
     * Generate a unique random customer code
     * Format: SS-XXXX-XXXX (where X is alphanumeric)
     */
    private function generateUniqueCustomerCode(): string
    {
        do {
            $code = 'SS-' . strtoupper(Str::random(4)) . '-' . strtoupper(Str::random(4));
        } while (Customer::where('customer_code', $code)->exists());

        return $code;
    }
}
