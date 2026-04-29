FROM php:8.2-cli

RUN apt-get update && apt-get install -y \
    unzip git curl libpng-dev libonig-dev libxml2-dev zip libpq-dev

RUN docker-php-ext-install \
    pdo \
    pdo_pgsql \
    pgsql \
    mbstring \
    bcmath \
    gd

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www
COPY . .

RUN composer install --no-dev --optimize-autoloader

RUN chmod -R 777 storage bootstrap/cache

# 🔥 TAMBAH INI
CMD php artisan config:cache && \
    php artisan route:cache && \
    php artisan migrate --force && \
    php -S 0.0.0.0:${PORT} -t public