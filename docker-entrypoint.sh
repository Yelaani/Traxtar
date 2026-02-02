#!/bin/bash
set -e

# Wait for database to be ready (optional, but helpful)
echo "Waiting for database connection..."
sleep 5

# Run migrations
echo "Running database migrations..."
php artisan migrate --force || true

# Clear and cache config
php artisan config:clear || true
php artisan config:cache || true

# Clear and cache routes
php artisan route:clear || true
php artisan route:cache || true

# Clear and cache views
php artisan view:clear || true
php artisan view:cache || true

# Start the application
echo "Starting Laravel application..."
exec php artisan serve --host=0.0.0.0 --port=8000
