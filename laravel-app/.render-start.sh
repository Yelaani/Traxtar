#!/usr/bin/env bash
# Render start script for Laravel application

set -e

echo "🚀 Starting Laravel application..."

# Run migrations on startup (optional, can be done manually)
# php artisan migrate --force --no-interaction

# Start the application
php artisan serve --host=0.0.0.0 --port=$PORT
