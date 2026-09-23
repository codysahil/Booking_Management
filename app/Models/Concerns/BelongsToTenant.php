<?php

namespace App\Models\Concerns;

use App\Models\Scopes\TenantScope;
use App\Models\Tenant;
use Illuminate\Support\Facades\App;

/**
 * Applied to every model with a direct tenant_id column. Automatically
 * scopes queries to the current tenant and stamps new rows with it.
 */
trait BelongsToTenant
{
    protected static function bootBelongsToTenant(): void
    {
        static::addGlobalScope(new TenantScope);

        static::creating(function ($model) {
            if (! $model->tenant_id && App::bound('currentTenantId')) {
                $model->tenant_id = App::make('currentTenantId');
            }
        });
    }

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }
}
