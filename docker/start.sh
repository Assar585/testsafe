#!/bin/bash
set -e

# Apply CoreComponentRepository patch to bypass external API checks (resolves 15s login hang)
echo "Applying CoreComponent patch..."
if [ -f "/var/www/core_patch.php" ]; then
    cp /var/www/core_patch.php /var/www/vendor/mehedi-iitdu/core-component-repository/src/CoreComponentRepository.php
    echo "CoreComponent patch applied."
fi

# Ensure correct permissions for mounted volumes (Railway overrides Dockerfile permissions)
echo "Setting permissions for storage and upload volumes..."
mkdir -p /var/www/storage/framework/cache/data \
         /var/www/storage/framework/app/cache \
         /var/www/storage/framework/sessions \
         /var/www/storage/framework/views \
         /var/www/storage/logs

chown -R www-data:www-data /var/www/storage /var/www/public/uploads || true
chmod -R 775 /var/www/storage /var/www/public/uploads || true

# Pre-warm Laravel caches for production on container boot
echo "Building Laravel caches..."
php /var/www/artisan package:discover
php /var/www/artisan config:cache
php /var/www/artisan view:cache
# Run migrations safely to avoid startup crash during schema mismatch.
php /var/www/artisan migrate --force || echo "Warning: Migration failed, but continuing startup..."
echo "Caches built and migrations attempted. Startup proceeding..."
# Start Nginx
service nginx start

# Start PHP-FPM in foreground
exec php-fpm
