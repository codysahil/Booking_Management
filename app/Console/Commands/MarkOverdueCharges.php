<?php

namespace App\Console\Commands;

use App\Models\Due;
use App\Models\MonthlyCharge;
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

        $overdueDues = Due::where('status', 'pending')
            ->whereDate('due_date', '<', today())
            ->count();

        $feeNote = $lateFee > 0 ? " (+ ₹{$lateFee} late fee each)" : '';
        $this->info("Marked {$newlyOverdue->count()} monthly charge(s) as overdue{$feeNote}. {$overdueDues} due(s) are past their due date (dues stay pending until paid).");

        return self::SUCCESS;
    }
}
