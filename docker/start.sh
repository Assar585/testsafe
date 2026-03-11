#!/bin/bash
set -e

# Apply CoreComponentRepository patch
echo "Applying CoreComponent patch..."
if [ -f "/var/www/core_patch.php" ]; then
    cp /var/www/core_patch.php /var/www/vendor/mehedi-iitdu/core-component-repository/src/CoreComponentRepository.php
    echo "CoreComponent patch applied."
fi

# Pre-warm Laravel caches for production on container boot
echo "Building Laravel caches at runtime..."
php /var/www/artisan package:discover
php /var/www/artisan config:clear
php /var/www/artisan config:cache
php /var/www/artisan view:cache
php /var/www/artisan storage:link || echo "Storage link already exists or failed..."

# Fix branding
echo "Fixing branding..."
php /var/www/fix_branding.php || echo "Branding fix failed..."

# Run migrations
echo "Running migrations..."
php /var/www/artisan migrate --force || echo "Warning: Migration failed..."

echo "Startup initialization complete. Proceeding to start services..."

# Start Nginx
service nginx start

# Start PHP-FPM in foreground
exec php-fpm
