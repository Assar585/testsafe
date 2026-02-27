FROM php:8.2-fpm

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    nginx \
    libzip-dev \
    libjpeg-dev \
    libfreetype6-dev

# Clear cache
RUN apt-get clean && rm -rf /var/lib/apt/lists/*

# Install PHP extensions
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install pdo_mysql mysqli mbstring exif pcntl bcmath gd zip opcache

# Setup highly optimized OPcache for production
COPY docker/opcache.ini /usr/local/etc/php/conf.d/opcache.ini

# Get latest Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www

# Copy existing application directory contents
COPY . /var/www

# Disable Composer audit blocks
RUN composer config audit.block-insecure false \
    && composer config audit.abandoned ignore

# Install dependencies (ignoring platform reqs)
RUN composer install --no-interaction --no-dev --optimize-autoloader --ignore-platform-reqs

# Setup storage and cache permissions
RUN mkdir -p storage/framework/sessions storage/framework/views storage/framework/cache \
    && mkdir -p /var/www/public/assets \
    && chmod -R 775 storage bootstrap/cache public \
    && chown -R www-data:www-data /var/www \
    && echo "=== /var/www/public contents ===" \
    && ls -la /var/www/public/ || echo "WARNING: /var/www/public does not exist!"

# Setup Nginx config
COPY docker/nginx.conf /etc/nginx/sites-available/default
RUN ln -sf /etc/nginx/sites-available/default /etc/nginx/sites-enabled/default

# Generate optimal Laravel caches (config, view) built into the image
RUN php artisan config:cache \
    && php artisan view:cache \
    || true

# Copy startup script
COPY docker/start.sh /start.sh
RUN chmod +x /start.sh

# Expose port and start via script
EXPOSE 8080
CMD ["/start.sh"]
