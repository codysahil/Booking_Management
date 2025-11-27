#!/bin/bash

# One-time database reset script for Railway
# Run this manually or temporarily add to start.sh

echo "Resetting database..."
php artisan migrate:fresh --force
php artisan db:seed --force
echo "Database reset complete!"
