FROM php:8.3-fpm-alpine

# Install system dependencies and required PHP extension libraries
RUN apk add --no-cache \
    nginx \
    supervisor \
    curl \
    libpng-dev \
    libxml2-dev \
    zip \
    unzip \
    git \
    oniguruma-dev

# Install required PHP extensions for Laravel and MySQL
RUN docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd

# Install the latest stable version of Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory inside the container
WORKDIR /var/www

# Copy all your local project files into the container
COPY . .

# Run composer installation for production dependencies
RUN composer install --no-interaction --optimize-autoloader --no-dev

# Set the correct permissions so Laravel can write logs and cache files
RUN chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache

# Copy your custom Nginx configuration file
COPY ./nginx.conf /etc/nginx/nginx.conf

# Expose web port 80
EXPOSE 80

# Boot up both PHP-FPM and Nginx simultaneously
CMD php-fpm -D && nginx -g "daemon off;"
