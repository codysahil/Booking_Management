<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;

/**
 * Key/value store for owner-editable settings (branding, contact, billing rules),
 * one row per tenant per key. Reads are cached per tenant; writes clear that
 * tenant's cache entry.
 */
class Setting extends Model
{
    use BelongsToTenant;

    protected $fillable = ['tenant_id', 'key', 'value'];

    private const CACHE_KEY = 'app_settings';

    private static function cacheKey(): string
    {
        $tenantId = App::bound('currentTenantId') ? App::make('currentTenantId') : 'none';

        return self::CACHE_KEY . ':' . $tenantId;
    }

    /**
     * @return array<string, mixed>
     *
     * With no tenant bound (a console command, webhook, or the super admin's
     * own actor — none of which belong to any one tenant), TenantScope
     * no-ops and a plain query would return an unpredictable mix of every
     * tenant's rows. Returning just the config defaults in that case is the
     * only safe option — reading "some other hostel's settings" is never
     * correct, and became a real risk once payment gateway credentials
     * started living in this same table.
     */
    public static function allValues(): array
    {
        $defaults = config('hostel.defaults', []);

        if (! App::bound('currentTenantId')) {
            return $defaults;
        }

        try {
            $stored = Cache::rememberForever(self::cacheKey(), function () {
                if (! Schema::hasTable('settings')) {
                    return [];
                }

                return static::query()->pluck('value', 'key')->all();
            });
        } catch (\Throwable $e) {
            // Database not reachable yet (fresh install, build step): fall back to defaults.
            $stored = [];
        }

        return array_merge($defaults, array_filter($stored, fn ($v) => $v !== null && $v !== ''));
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        return static::allValues()[$key] ?? $default;
    }

    /** @param array<string, mixed> $values */
    public static function putMany(array $values): void
    {
        if (! App::bound('currentTenantId')) {
            // Same reasoning as allValues(): with no tenant bound, updateOrCreate()'s
            // lookup by key alone could match and overwrite a different tenant's row.
            throw new \RuntimeException('Setting::putMany() called with no tenant bound.');
        }

        foreach ($values as $key => $value) {
            static::query()->updateOrCreate(['key' => $key], ['value' => $value]);
        }

        Cache::forget(self::cacheKey());
    }
}
