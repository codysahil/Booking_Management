<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\Room;
use App\Models\Bed;
use App\Models\Booking;
use App\Models\Customer;
use App\Services\Razorpay;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class BookingController extends Controller
{
    public function __construct(private Razorpay $razorpay)
    {
    }

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
            'accept_terms' => 'accepted',
        ], [
            'accept_terms.accepted' => 'Please accept the Terms & Conditions to continue.',
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

        if ($beds->isEmpty() || $beds->count() !== count($bedIds)) {
            return redirect()->route('home')->withErrors(['error' => 'No beds found. Please try again.']);
        }

        // Calculate advance
        $totalRent = $beds->sum('monthly_rent');
        $advance = max($totalRent, 3000);

        // Atomically re-claim each bed so two customers can't check out with the same bed.
        // A bed is still claimable if it's vacant and either unreserved, its reservation
        // expired, or it was this browser session that reserved it.
        $now = now();
        $sessionBedIds = collect(session('selected_bed_ids', []))->map(fn ($id) => (int) $id)->all();
        $claimedIds = [];

        foreach ($bedIds as $bedId) {
            $claimed = Bed::where('id', $bedId)
                ->where('status', 'vacant')
                ->where(function ($query) use ($now, $sessionBedIds) {
                    $query->whereNull('reserved_until')
                        ->orWhere('reserved_until', '<', $now)
                        ->orWhereIn('id', $sessionBedIds);
                })
                ->update(['status' => 'reserved', 'reserved_until' => null]);

            if ($claimed) {
                $claimedIds[] = $bedId;
            } else {
                if (!empty($claimedIds)) {
                    Bed::whereIn('id', $claimedIds)->update(['status' => 'vacant']);
                }

                \Log::warning('Booking failed: bed no longer available', ['bed_id' => $bedId]);

                return redirect()->route('home')->withErrors(['error' => 'Sorry, one or more selected beds were just booked by someone else. Please select beds again.']);
            }
        }

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

            try {
                \Illuminate\Support\Facades\Notification::send(
                    \App\Models\User::query()->where('is_active', true)->get(),
                    new \App\Notifications\NewBookingReceived($booking, $beds->count())
                );
            } catch (\Throwable $e) {
                \Log::warning('Could not notify staff of new booking', ['error' => $e->getMessage()]);
            }

            \Log::info('Booking created successfully', [
                'booking_id' => $booking->id,
                'booking_reference' => $bookingReference,
                'customer_code' => $customerCode,
            ]);

            $confirmationUrl = \Illuminate\Support\Facades\URL::signedRoute('booking.confirmation', ['booking' => $booking]);

            return redirect($confirmationUrl)->with('success', 'Booking confirmed!');
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

        $pendingAdvance = $booking->customer->payments()
            ->where('payment_type', 'Advance')
            ->where('status', 'pending')
            ->first();

        $onlinePaymentsEnabled = $this->razorpay->isConfigured();

        \Log::info('Confirmation page loaded', [
            'booking_id' => $booking->id,
            'booking_reference' => $booking->booking_reference,
            'customer_code' => $booking->customer->customer_code,
        ]);

        return view('public.confirmation', compact('booking', 'relatedBookings', 'pendingAdvance', 'onlinePaymentsEnabled'));
    }

    /** Create a Razorpay order to (optionally) pay the pending advance online, from the confirmation page. */
    public function createAdvanceOrder(Booking $booking)
    {
        abort_unless($this->razorpay->isConfigured(), 404);

        $payment = $booking->customer->payments()
            ->where('payment_type', 'Advance')
            ->where('status', 'pending')
            ->firstOrFail();

        $order = $this->razorpay->createOrder((float) $payment->amount, 'adv_' . $booking->id . '_' . time(), [
            'purpose' => 'advance',
            'booking_id' => $booking->id,
            'payment_id' => $payment->id,
        ]);

        return response()->json([
            'order_id' => $order->id,
            'amount' => $order->amount,
            'currency' => $order->currency,
            'key' => config('services.razorpay.key'),
        ]);
    }

    /** Verify the Razorpay callback and settle the pending advance payment. */
    public function verifyAdvancePayment(Request $request, Booking $booking)
    {
        $validated = $request->validate([
            'razorpay_order_id' => 'required|string',
            'razorpay_payment_id' => 'required|string',
            'razorpay_signature' => 'required|string',
        ]);

        $payment = $booking->customer->payments()
            ->where('payment_type', 'Advance')
            ->where('status', 'pending')
            ->firstOrFail();

        try {
            $this->razorpay->verifySignature($validated);
            $paymentData = $this->razorpay->fetchPayment($validated['razorpay_payment_id']);

            $payment->update([
                'payment_method' => Razorpay::describeMethod($paymentData->toArray()),
                'razorpay_payment_id' => $validated['razorpay_payment_id'],
                'razorpay_order_id' => $validated['razorpay_order_id'],
                'status' => 'paid',
                'paid_at' => now(),
            ]);

            $booking->update(['advance_paid' => $payment->amount]);

            return response()->json(['success' => true]);
        } catch (\Throwable $e) {
            \Log::error('Advance payment verification failed', ['error' => $e->getMessage(), 'booking_id' => $booking->id]);

            return response()->json(['success' => false, 'message' => 'Payment verification failed.'], 422);
        }
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
