#!/bin/bash

# Clear config cache to load new environment variables
php artisan config:clear
php artisan cache:clear

# Create storage link (ignore if exists)
php artisan storage:link 2>/dev/null || true

# Run migrations
php artisan migrate --force

# Run seeders
php artisan db:seed --force

# Start the server
php artisan serve --host=0.0.0.0 --port=${PORT:-8000}
