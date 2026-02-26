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
    && docker-php-ext-install pdo_mysql mysqli mbstring exif pcntl bcmath gd zip

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
    && chmod -R 775 storage bootstrap/cache \
    && chown -R www-data:www-data /var/www

# Setup Nginx config
COPY docker/nginx.conf /etc/nginx/sites-available/default
RUN ln -sf /etc/nginx/sites-available/default /etc/nginx/sites-enabled/default

# Clear all Laravel caches (route, config, view) built into the image
RUN php artisan route:clear \
    && php artisan config:clear \
    && php artisan view:clear \
    || true

# Copy startup script
COPY docker/start.sh /start.sh
RUN chmod +x /start.sh

# Expose port and start via script
EXPOSE 8080
CMD ["/start.sh"]
