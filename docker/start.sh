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
php /var/www/artisan config:clear
php /var/www/artisan view:cache
echo "Caches built."

# Write Railway environment variables into a physical .env file to guarantee Laravel reads them
echo "Writing environment variables to .env file..."
env | grep -E '^(DB_|REDIS_|APP_|MAIL_)_?' > /var/www/.env
chmod 644 /var/www/.env

# Start Nginx
service nginx start

# Start PHP-FPM in foreground
exec php-fpm
