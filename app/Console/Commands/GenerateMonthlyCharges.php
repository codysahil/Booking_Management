<?php

namespace App\Console\Commands;

use App\Models\Booking;
use App\Models\MonthlyCharge;
use App\Models\Tenant;
use Carbon\Carbon;
use Illuminate\Console\Command;

/**
 * Creates this month's rent charge for every active booking that doesn't
 * already have one. Runs on the 1st of the month (see bootstrap/app.php);
 * can also be run by hand for a specific month, e.g. --month=2026-01.
 */
class GenerateMonthlyCharges extends Command
{
    protected $signature = 'charges:generate {--month= : Month to generate charges for, format Y-m (defaults to the current month)}';

    protected $description = 'Generate this month\'s rent charge for every active booking';

    public function handle(): int
    {
        $month = $this->option('month') ?: now()->format('Y-m');

        $generated = 0;
        $skipped = 0;

        // Bound per tenant, not once globally — a scheduled run has no tenant
        // bound at all, and each hostel has its own rent_due_day setting.
        foreach (Tenant::all() as $tenant) {
            app()->instance('currentTenantId', $tenant->id);

            $dueDay = (int) setting('rent_due_day', 5);
            $dueDate = Carbon::createFromFormat('Y-m', $month)->startOfMonth()->day(min($dueDay, 28));

            $activeBookings = Booking::where('status', 'active')->with(['customer', 'bed'])->get();

            foreach ($activeBookings as $booking) {
                $exists = MonthlyCharge::where('booking_id', $booking->id)
                    ->where('month_year', $month)
                    ->exists();

                if ($exists) {
                    $skipped++;
                    continue;
                }

                $rentAmount = MonthlyCharge::proratedRentAmount($booking, $month, (float) $booking->bed->monthly_rent);

                MonthlyCharge::create([
                    'customer_id' => $booking->customer_id,
                    'booking_id' => $booking->id,
                    'month_year' => $month,
                    'rent_amount' => $rentAmount,
                    'is_prorated' => MonthlyCharge::isProratedMonth($booking, $month),
                    'eb_amount' => 0,
                    'other_charges' => 0,
                    'total_amount' => $rentAmount,
                    'status' => 'pending',
                    'due_date' => $dueDate,
                ]);

                $generated++;
            }
        }

        app()->forgetInstance('currentTenantId');

        $this->info("Generated {$generated} charge(s) for {$month}. Skipped {$skipped} that already existed.");

        return self::SUCCESS;
    }
}
