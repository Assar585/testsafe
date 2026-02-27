#!/bin/bash
set -e

# Pre-warm Laravel caches for production on container boot
echo "Building Laravel caches..."
php /var/www/artisan config:cache
php /var/www/artisan view:cache
echo "Caches built."
# Start Nginx
service nginx start

# Start PHP-FPM in foreground
exec php-fpm
