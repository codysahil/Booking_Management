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
        $overdueCharges = MonthlyCharge::where('status', 'pending')
            ->whereDate('due_date', '<', today())
            ->update(['status' => 'overdue']);

        $overdueDues = Due::where('status', 'pending')
            ->whereDate('due_date', '<', today())
            ->count();

        $this->info("Marked {$overdueCharges} monthly charge(s) as overdue. {$overdueDues} due(s) are past their due date (dues stay pending until paid).");

        return self::SUCCESS;
    }
}
