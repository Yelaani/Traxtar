#!/usr/bin/env bash
# Render build script for Laravel application

set -e

echo "🚀 Starting Render build process..."

# Install PHP dependencies
echo "📦 Installing Composer dependencies..."
composer install --no-dev --optimize-autoloader --no-interaction

# Install Node dependencies
echo "📦 Installing Node dependencies..."
npm ci

# Build frontend assets
echo "🔨 Building frontend assets..."
npm run build

# Generate application key if not set
if [ -z "$APP_KEY" ]; then
    echo "🔑 Generating application key..."
    php artisan key:generate --force
fi

# Cache configuration
echo "⚙️ Caching configuration..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Run migrations (if needed)
echo "🗄️ Running database migrations..."
php artisan migrate --force --no-interaction || true

# Create storage link
echo "🔗 Creating storage link..."
php artisan storage:link || true

echo "✅ Build process completed successfully!"
