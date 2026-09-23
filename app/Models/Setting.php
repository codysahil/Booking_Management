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

    /** @return array<string, mixed> */
    public static function allValues(): array
    {
        $defaults = config('hostel.defaults', []);

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
        foreach ($values as $key => $value) {
            static::query()->updateOrCreate(['key' => $key], ['value' => $value]);
        }

        Cache::forget(self::cacheKey());
    }
}
