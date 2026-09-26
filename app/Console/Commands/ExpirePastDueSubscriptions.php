<?php

namespace App\Console\Commands;

use App\Models\Subscription;
use Illuminate\Console\Command;

/**
 * Two independent expiries, run daily (see bootstrap/app.php):
 *  - A 'past_due' subscription (a failed charge, still in its grace window)
 *    whose grace has now passed becomes 'expired'.
 *  - A 'trialing' subscription whose tenant's trial has run out with no real
 *    billing ever started also becomes 'expired'.
 * Both are read-only-lockout triggers — see EnsureSubscriptionActive.
 */
class ExpirePastDueSubscriptions extends Command
{
    protected $signature = 'subscriptions:expire-past-due';

    protected $description = 'Expire subscriptions whose grace period or trial has run out';

    public function handle(): int
    {
        $expiredGrace = Subscription::where('status', Subscription::STATUS_PAST_DUE)
            ->whereNotNull('grace_ends_at')
            ->where('grace_ends_at', '<=', now())
            ->update(['status' => Subscription::STATUS_EXPIRED]);

        $expiredTrials = Subscription::where('status', Subscription::STATUS_TRIALING)
            ->whereHas('tenant', fn ($q) => $q->whereNotNull('trial_ends_at')->where('trial_ends_at', '<=', now()))
            ->update(['status' => Subscription::STATUS_EXPIRED]);

        $this->info("Expired {$expiredGrace} past-due subscription(s) and {$expiredTrials} trial(s).");

        return self::SUCCESS;
    }
}
