<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Every table already has data belonging to one hostel (whatever was deployed
 * before multi-tenancy existed). Create a tenant row for it and point every
 * existing row at it. No business data is touched — only the new tenant_id
 * columns (added nullable by the migrations just before this one) get filled in.
 */
return new class extends Migration
{
    private const TENANT_TABLES = [
        'users', 'customers', 'branches', 'settings',
        'hero_sliders', 'employees', 'expenses', 'announcements',
    ];

    public function up(): void
    {
        $hasExistingData = collect(self::TENANT_TABLES)->contains(fn ($table) => DB::table($table)->exists());

        if (! $hasExistingData) {
            // Fresh install (a new dev environment, CI, or a fresh deploy of this
            // template) — nothing to backfill, so don't create a phantom tenant.
            return;
        }

        $hostelName = DB::table('settings')->where('key', 'hostel_name')->value('value')
            ?: config('hostel.defaults.hostel_name', 'My Hostel');

        $tenantId = DB::table('tenants')->insertGetId([
            'name' => $hostelName,
            'slug' => \Illuminate\Support\Str::slug($hostelName) ?: 'tenant-1',
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        foreach (self::TENANT_TABLES as $table) {
            DB::table($table)->whereNull('tenant_id')->update(['tenant_id' => $tenantId]);
        }
    }

    public function down(): void
    {
        // Backfilling is not meaningfully reversible — rolling back the schema
        // changes in the surrounding migrations already removes the column.
    }
};
