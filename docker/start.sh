#!/bin/bash
set -e

# Clear Laravel route, config, and view caches on every startup
# This ensures no stale cache from previous deployments
echo "Clearing Laravel caches..."
php /var/www/artisan route:clear 2>/dev/null || true
php /var/www/artisan config:clear 2>/dev/null || true
php /var/www/artisan view:clear 2>/dev/null || true
echo "Caches cleared."

# Start Nginx
service nginx start

# Start PHP-FPM in foreground
exec php-fpm
