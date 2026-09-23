<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Every root table has been backfilled with a tenant_id by now — tighten the
 * column to required. `users` is the one deliberate exception: a super admin
 * (is_super_admin = true) has no tenant, so users.tenant_id stays nullable
 * forever (the invariant is tenant_id IS NULL iff is_super_admin = true).
 */
return new class extends Migration
{
    private const TABLES_TO_TIGHTEN = [
        'customers', 'branches', 'settings',
        'hero_sliders', 'employees', 'expenses', 'announcements',
    ];

    public function up(): void
    {
        foreach (self::TABLES_TO_TIGHTEN as $table) {
            Schema::table($table, function (Blueprint $blueprint) {
                $blueprint->foreignId('tenant_id')->nullable(false)->change();
            });
        }

        Schema::table('settings', function (Blueprint $table) {
            $table->unique(['tenant_id', 'key']);
        });
    }

    public function down(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->dropUnique(['tenant_id', 'key']);
        });

        foreach (self::TABLES_TO_TIGHTEN as $table) {
            Schema::table($table, function (Blueprint $blueprint) {
                $blueprint->foreignId('tenant_id')->nullable()->change();
            });
        }
    }
};
