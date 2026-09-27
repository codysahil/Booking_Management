<?php

namespace App\Console\Commands;

use App\Models\Due;
use App\Models\MonthlyCharge;
use App\Models\Tenant;
use Illuminate\Console\Command;

/**
 * Flags any pending monthly charge or due whose due date has passed as
 * overdue, so the dashboard and resident portal reflect it. Runs daily
 * (see bootstrap/app.php).
 */
class MarkOverdueCharges extends Command
{
    protected $signature = 'charges:mark-overdue';

    protected $description = 'Mark pending monthly charges and dues past their due date as overdue';

    public function handle(): int
    {
        $totalOverdue = 0;
        $totalOverdueDues = 0;

        // Bound per tenant, not once globally — a scheduled run has no tenant
        // bound at all, and each hostel has its own late_fee setting.
        foreach (Tenant::all() as $tenant) {
            app()->instance('currentTenantId', $tenant->id);

            $lateFee = (float) setting('late_fee', 0);

            $newlyOverdue = MonthlyCharge::where('status', 'pending')
                ->whereDate('due_date', '<', today())
                ->get();

            foreach ($newlyOverdue as $charge) {
                $charge->update([
                    'status' => 'overdue',
                    'other_charges' => $charge->other_charges + $lateFee,
                    'total_amount' => $charge->total_amount + $lateFee,
                ]);
            }

            $totalOverdue += $newlyOverdue->count();

            $totalOverdueDues += Due::where('status', 'pending')
                ->whereDate('due_date', '<', today())
                ->count();
        }

        app()->forgetInstance('currentTenantId');

        $this->info("Marked {$totalOverdue} monthly charge(s) as overdue. {$totalOverdueDues} due(s) are past their due date (dues stay pending until paid).");

        return self::SUCCESS;
    }
}
