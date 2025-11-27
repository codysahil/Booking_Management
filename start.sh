#!/bin/bash

# Clear all caches to load new environment variables
php artisan config:clear
php artisan cache:clear
php artisan view:clear
php artisan route:clear

# Optimize for production (this will cache config with fresh env vars)
php artisan config:cache

# Create storage link (ignore if exists)
php artisan storage:link 2>/dev/null || true

# Run migrations
php artisan migrate --force

# Run seeders
php artisan db:seed --force

# Start the server
php artisan serve --host=0.0.0.0 --port=${PORT:-8000}
