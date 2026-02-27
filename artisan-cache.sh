#!/bin/bash

echo "======================================"
echo "    Laravel Optimizer & Cacher        "
echo "======================================"
echo ""

if [ "$1" == "clear" ]; then
    echo "Clearing all caches..."
    php artisan optimize:clear
    echo "Done!"
else
    echo "Building all production caches for maximum speed..."
    php artisan config:cache
    php artisan route:cache
    php artisan view:cache
    echo "Done! The website should now be significantly faster."
fi
