FROM php:8.2-fpm-alpine

# Install system dependencies, MySQL extensions, and Node.js + NPM
RUN apk add --no-cache \
    nginx \
    supervisor \
    curl \
    libpng-dev \
    libxml2-dev \
    zip \
    unzip \
    git \
    oniguruma-dev \
    nodejs \
    npm

# Install exact PHP native extensions required for Laravel & MySQL database pipelines
RUN docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd

# Fetch the exact production-ready Composer utility binary safely
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Define working directory context inside container space
WORKDIR /var/www

# Copy the entire codebase structure across layers
COPY . .

# Install PHP packages matching the composer.lock file structural signatures exactly
RUN composer install --no-interaction --optimize-autoloader --no-dev

# Install Node modules and run the Laravel Mix pipeline using npm run prod script
RUN npm install
RUN npm run prod

# Enforce explicit standard write/cache directory permissions for Alpine processes
RUN chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache \
    && chmod -R 775 /var/www/storage /var/www/bootstrap/cache

# Bind the custom Nginx server runtime blocks 
COPY ./nginx.conf /etc/nginx/nginx.conf

# Bind exposure targeting default HTTP web process layers 
EXPOSE 80

# Automate cache clearing, storage symlink, migrations, seeding, and cache compilation on container deployment
CMD php artisan config:clear \
    && php artisan cache:clear \
    && php artisan view:clear \
    && php artisan route:clear \
    && php artisan storage:link --force \
    && php artisan migrate --force \
    && php artisan db:seed --force \
    && php artisan config:cache \
    && php artisan route:cache \
    && php artisan view:cache \
    && php-fpm -D \
    && nginx -g "daemon off;"
