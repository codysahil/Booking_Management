<?php

namespace App\Console\Commands;

use App\Models\Booking;
use App\Models\Payment;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * An online booking that requires payment (see Public\BookingController@processPayment)
 * holds its bed only until the payment window expires. Anyone who never paid never
 * becomes a real booking — free the bed back to inventory and mark the attempt failed
 * instead of leaving it stuck as a permanent, invisible hold.
 */
class ReleaseExpiredBookings extends Command
{
    protected $signature = 'bookings:release-expired';

    protected $description = 'Cancel online bookings whose payment window expired and free their beds';

    public function handle(): int
    {
        $expiredIds = Booking::awaitingPayment()
            ->whereHas('bed', fn ($q) => $q->where('reserved_until', '<', now()))
            ->pluck('id');

        $released = 0;

        foreach ($expiredIds as $bookingId) {
            // Lock the booking row so this can't race PaymentRecorder::settleAdvance()
            // settling the payment (webhook or browser callback) at the same moment —
            // whichever transaction commits first wins, the other sees the updated status.
            DB::transaction(function () use ($bookingId, &$released) {
                $booking = Booking::whereKey($bookingId)->lockForUpdate()->with('bed')->first();

                if (! $booking || $booking->status !== Booking::STATUS_PENDING_PAYMENT) {
                    return;
                }

                $booking->update(['status' => Booking::STATUS_CANCELLED]);

                $booking->bed?->update(['status' => 'vacant', 'reserved_until' => null]);

                Payment::where('booking_id', $booking->id)
                    ->where('payment_type', 'Advance')
                    ->where('status', Payment::STATUS_PENDING)
                    ->update(['status' => Payment::STATUS_FAILED]);

                $released++;
            });
        }

        $this->info("Released {$released} expired, unpaid booking(s).");

        return self::SUCCESS;
    }
}
