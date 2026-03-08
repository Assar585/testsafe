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
php /var/www/artisan migrate --force
echo "Caches built and migrations applied."
# Start Nginx
service nginx start

# Start PHP-FPM in foreground
exec php-fpm
