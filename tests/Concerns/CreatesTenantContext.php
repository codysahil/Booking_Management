<?php

namespace Tests\Concerns;

use App\Models\Tenant;
use App\Models\User;

/**
 * Every tenant-owned model auto-stamps its tenant_id from the container
 * binding `currentTenantId` when one is set (see App\Models\Concerns\BelongsToTenant).
 * An actual HTTP request binds it automatically (see App\Http\Middleware\ResolveTenant),
 * but a test creating records directly (Branch::create(...), etc.) outside of any
 * request needs that binding set up by hand first — these helpers do that.
 */
trait CreatesTenantContext
{
    protected ?Tenant $tenant = null;

    /** Create (or reuse) a tenant and bind it as the current tenant for direct model creation. */
    protected function bindTenant(?Tenant $tenant = null): Tenant
    {
        $this->tenant = $tenant ?? $this->tenant ?? Tenant::factory()->create();

        app()->instance('currentTenantId', $this->tenant->id);

        return $this->tenant;
    }

    /** Create a staff user for the (bound) tenant and log the test in as them. */
    protected function loginAsStaff(array $attributes = [], ?Tenant $tenant = null): User
    {
        $tenant = $this->bindTenant($tenant);

        $user = User::factory()->create(array_merge(['tenant_id' => $tenant->id], $attributes));

        $this->actingAs($user);

        return $user;
    }
}
