<?php

namespace App\Models\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use Illuminate\Support\Facades\App;

/**
 * Restricts every query on a tenant-owned model to the currently bound
 * tenant. Bound through the container (not read from Auth::user() directly)
 * so it works the same way from HTTP requests, console commands, and the
 * Super Admin panel, none of which necessarily have a `web`-guard user.
 *
 * No-ops when no tenant is bound (e.g. a login lookup, which must find the
 * account before it knows which tenant it belongs to).
 */
class TenantScope implements Scope
{
    public function apply(Builder $builder, Model $model): void
    {
        if (App::bound('currentTenantId') && ($tenantId = App::make('currentTenantId'))) {
            $builder->where($model->getTable() . '.tenant_id', $tenantId);
        }
    }
}
