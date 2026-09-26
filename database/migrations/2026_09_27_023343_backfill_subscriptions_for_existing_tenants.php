<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Any tenant that already existed before billing was introduced (Nestay PG,
 * the platform owner's own hostel, plus anyone onboarded during earlier
 * testing) was created with no subscription row at all. EnsureSubscriptionActive
 * would lock all of them out the moment it ships, since a missing subscription
 * is not writable — so every pre-existing tenant gets an 'internal' one here:
 * never expires, no billing attached, and the super admin can always move a
 * specific tenant onto a real plan afterward from the Subscription screen.
 * Tenants onboarded from now on get a proper 'trialing' subscription at
 * creation time instead (see SuperAdmin\TenantController::store()).
 */
return new class extends Migration
{
    public function up(): void
    {
        $tenantIds = DB::table('tenants')
            ->leftJoin('subscriptions', 'subscriptions.tenant_id', '=', 'tenants.id')
            ->whereNull('subscriptions.id')
            ->pluck('tenants.id');

        if ($tenantIds->isEmpty()) {
            return;
        }

        $now = now();

        DB::table('subscriptions')->insert(
            $tenantIds->map(fn ($tenantId) => [
                'tenant_id' => $tenantId,
                'plan_id' => null,
                'status' => 'internal',
                'created_at' => $now,
                'updated_at' => $now,
            ])->all()
        );
    }

    public function down(): void
    {
        // Backfilled rows are indistinguishable from any other 'internal'
        // subscription created later; nothing safe to reverse here.
    }
};
