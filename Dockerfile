# --- Build Stage ---
FROM composer:2.7 AS vendor
WORKDIR /app
COPY composer.json composer.lock ./
RUN composer install --no-dev --optimize-autoloader --no-scripts

# --- Application Stage ---
FROM php:8.2-cli-alpine AS app
WORKDIR /var/www

# Install system dependencies
RUN apk add --no-cache libpng libpng-dev libjpeg-turbo-dev freetype-dev \
    libzip-dev zip unzip git bash oniguruma-dev icu-dev

# Install PHP extensions
RUN docker-php-ext-install pdo pdo_mysql mbstring zip exif pcntl bcmath intl gd

# Copy app source
COPY . /var/www

# Copy vendor from build stage
COPY --from=vendor /app/vendor /var/www/vendor

# Set permissions
RUN chown -R www-data:www-data /var/www \
    && chmod -R 755 /var/www/storage /var/www/bootstrap/cache

# Expose port
EXPOSE 10000

# Entrypoint
CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=10000"]
