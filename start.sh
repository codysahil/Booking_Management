#!/bin/bash

# Clear all caches to load new environment variables
php artisan config:clear
php artisan cache:clear
php artisan view:clear
php artisan route:clear

# Optimize for production (this will cache config with fresh env vars)
# Optimize for production (disabled for debugging)
# php artisan config:cache

# Create storage link (ignore if exists)
php artisan storage:link 2>/dev/null || true

# Run migrations (includes monthly_charges and dues tables)
php artisan migrate --force

# Seeding is deliberately NOT run automatically here — this is a live
# multi-tenant database now; auto-seeding demo data on every deploy would
# inject a fake tenant into production. Onboard real hostels through the
# Super Admin panel; run `php artisan db:seed` by hand only for local dev.

# Start the server
php artisan serve --host=0.0.0.0 --port=${PORT:-8000}
