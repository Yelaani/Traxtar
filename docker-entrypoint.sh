#!/bin/bash

# Don't exit on error - we want to continue even if some commands fail
set +e

# Wait for database to be ready (optional, but helpful)
echo "Waiting for database connection..."
sleep 5

# Generate APP_KEY if not set (should be set via env var, but just in case)
if [ -z "$APP_KEY" ]; then
    echo "APP_KEY not set, generating..."
    php artisan key:generate --force
fi

# Create storage link if it doesn't exist
php artisan storage:link || true

# Run migrations
echo "Running database migrations..."
php artisan migrate --force

# Clear caches first
php artisan config:clear || true
php artisan route:clear || true
php artisan view:clear || true
php artisan cache:clear || true

# Cache config, routes, and views
php artisan config:cache || true
php artisan route:cache || true
php artisan view:cache || true

# Use PORT from environment variable (Render provides this)
PORT=${PORT:-8000}

# Start the application
echo "Starting Laravel application on port $PORT..."
exec php artisan serve --host=0.0.0.0 --port=$PORT
