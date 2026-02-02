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

# Verify Vite assets were built correctly
echo "Verifying Vite assets..."
if [ ! -f "public/build/manifest.json" ]; then
    echo "WARNING: Vite manifest.json not found! Assets may not load correctly."
    echo "Attempting to rebuild assets..."
    npm run build || echo "Asset rebuild failed, continuing anyway..."
else
    echo "✓ Vite manifest.json found"
fi

# Create storage link at runtime (must be done after app is copied)
echo "Creating storage symlink..."
php artisan storage:link || {
    echo "WARNING: Failed to create storage link. Images may not load."
}

# Verify storage link exists
if [ -L "public/storage" ]; then
    echo "✓ Storage symlink created successfully"
else
    echo "WARNING: Storage symlink verification failed"
fi

# Run migrations
echo "Running database migrations..."
php artisan migrate --force || {
    echo "WARNING: Migrations failed, but continuing..."
}

# Clear all caches first (important for production)
echo "Clearing caches..."
php artisan config:clear || true
php artisan route:clear || true
php artisan view:clear || true
php artisan cache:clear || true

# Verify APP_URL is set (critical for asset URLs)
if [ -z "$APP_URL" ]; then
    echo "WARNING: APP_URL environment variable is not set!"
    echo "This may cause incorrect asset URLs. Please set APP_URL in Render environment variables."
else
    echo "✓ APP_URL is set to: $APP_URL"
fi

# Cache config, routes, and views (after clearing)
echo "Caching configuration..."
php artisan config:cache || {
    echo "WARNING: Config caching failed"
}
php artisan route:cache || {
    echo "WARNING: Route caching failed"
}
php artisan view:cache || {
    echo "WARNING: View caching failed"
}

# Use PORT from environment variable (Render provides this)
PORT=${PORT:-8000}

# Start the application
echo "Starting Laravel application on port $PORT..."
echo "Application ready! Visit your Render URL to see the site."
exec php artisan serve --host=0.0.0.0 --port=$PORT
