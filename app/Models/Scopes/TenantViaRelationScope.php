<?php

namespace App\Models\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use Illuminate\Support\Facades\App;

/**
 * For a model with no tenant_id column of its own but a required relation
 * that IS tenant-scoped (e.g. Booking -> customer, Room -> branch) —
 * restricts every query (route-model binding, raw finds, aggregates, anything
 * written later) to the current tenant via that relation. This is what makes
 * it safe for controllers to query these models directly without every call
 * site having to remember to join through the tenant-scoped ancestor itself.
 */
class TenantViaRelationScope implements Scope
{
    public function __construct(private string $relation)
    {
    }

    public function apply(Builder $builder, Model $model): void
    {
        if (App::bound('currentTenantId') && ($tenantId = App::make('currentTenantId'))) {
            $builder->whereHas($this->relation, fn ($q) => $q->where('tenant_id', $tenantId));
        }
    }
}
