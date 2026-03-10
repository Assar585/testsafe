#!/bin/bash
set -e

# Apply CoreComponentRepository patch to bypass external API checks (resolves 15s login hang)
echo "Applying CoreComponent patch..."
if [ -f "/var/www/core_patch.php" ]; then
    cp /var/www/core_patch.php /var/www/vendor/mehedi-iitdu/core-component-repository/src/CoreComponentRepository.php
    echo "CoreComponent patch applied."
fi

# Pre-warm Laravel caches for production on container boot
echo "Building Laravel caches..."
php /var/www/artisan package:discover
php /var/www/artisan config:cache
php /var/www/artisan view:cache
# Run the exact missing migration explicitly to bypass any earlier migration crashes
php /var/www/artisan migrate --path=database/migrations/2026_03_03_000000_create_api_integrations_table.php --force || echo "Warning: Explicit API integration migration failed..."

# Run migrations safely to avoid startup crash during schema mismatch.
echo "Caches built and migrations attempted. Startup proceeding..."

# Ensure correct permissions for mounted volumes and locally generated cache files
echo "Setting permissions for storage, cache, and upload volumes..."
mkdir -p /var/www/storage/framework/cache/data \
         /var/www/storage/framework/app/cache \
         /var/www/storage/framework/sessions \
         /var/www/storage/framework/views \
         /var/www/storage/logs \
         /var/www/bootstrap/cache

chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache /var/www/public || true
chmod -R 775 /var/www/storage /var/www/bootstrap/cache /var/www/public || true

# Start Nginx
service nginx start

# Start PHP-FPM in foreground
exec php-fpm
