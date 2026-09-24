<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

/**
 * For local development only — creates one demo tenant with a full set of
 * realistic data. Deliberately NOT run automatically on deploy: seeding a
 * live multi-tenant database with demo data on every deploy would inject a
 * fake tenant into production. Run by hand when you want a demo/dev dataset:
 * `php artisan db:seed`.
 *
 * Deliberately does NOT use WithoutModelEvents — tenant_id auto-stamping
 * (BelongsToTenant) and Payment's receipt-number assignment both depend on
 * model events firing during create().
 */
class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            DemoDataSeeder::class,
        ]);
    }
}
