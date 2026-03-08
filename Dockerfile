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
    libfreetype6-dev \
    libwebp-dev \
    libicu-dev

# Clear cache
RUN apt-get clean && rm -rf /var/lib/apt/lists/*

# Install PHP extensions
RUN docker-php-ext-configure gd --with-freetype --with-jpeg --with-webp \
    && docker-php-ext-install pdo_mysql mysqli mbstring exif pcntl bcmath gd zip opcache intl fileinfo iconv

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
RUN export COMPOSER_MEMORY_LIMIT=-1 \
    && composer install --verbose --no-interaction --no-dev --no-scripts --optimize-autoloader --ignore-platform-reqs

# Create necessary directories and set permissions
RUN mkdir -p /var/www/storage/framework/cache/data \
    && mkdir -p /var/www/storage/framework/app/cache \
    && mkdir -p /var/www/storage/framework/sessions \
    && mkdir -p /var/www/storage/framework/views \
    && mkdir -p /var/www/storage/logs \
    && mkdir -p /var/www/bootstrap/cache \
    && chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache \
    && chmod -R 775 /var/www/storage /var/www/bootstrap/cache

# Switch to root to ensure we can run start.sh with proper permissions if needed
USER root

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
