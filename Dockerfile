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
    libwebp-dev

# Clear cache
RUN apt-get clean && rm -rf /var/lib/apt/lists/*

# Install PHP extensions
RUN docker-php-ext-configure gd --with-freetype --with-jpeg --with-webp \
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

# Generate optimal Laravel views (Do not cache config here as env vars are not present during build)
RUN php artisan view:cache || true

# Setup storage and cache permissions (Move to after cache generation)
RUN mkdir -p storage/framework/sessions storage/framework/views storage/framework/cache \
    && mkdir -p /var/www/public/assets \
    && chown -R www-data:www-data /var/www \
    && chmod -R 775 storage bootstrap/cache public

# Copy startup script
COPY docker/start.sh /start.sh
RUN chmod +x /start.sh

# Copy nginx config
COPY docker/nginx.conf /etc/nginx/sites-available/default
COPY docker/nginx.conf /etc/nginx/sites-enabled/default

# Expose port and start via script
EXPOSE 8080
CMD ["/start.sh"]
