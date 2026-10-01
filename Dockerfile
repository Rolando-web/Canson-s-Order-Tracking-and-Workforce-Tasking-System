# syntax=docker/dockerfile:1

# ---------------------------------------------------------------------------
# Stage 1 - Frontend assets
# Builds the Vite/Tailwind bundle into public/build
# ---------------------------------------------------------------------------
FROM node:22-bookworm-slim AS assets

WORKDIR /app

COPY package.json package-lock.json ./
RUN npm ci

COPY vite.config.js ./
COPY resources/ ./resources/
RUN npm run build


# ---------------------------------------------------------------------------
# Stage 2 - PHP dependencies
# Kept separate so composer install is not re-run on every code change
# ---------------------------------------------------------------------------
FROM composer:2 AS vendor

WORKDIR /app

COPY composer.json composer.lock ./
RUN composer install \
    --no-dev \
    --prefer-dist \
    --no-scripts \
    --no-autoloader \
    --no-interaction

COPY . .
RUN composer dump-autoload --optimize --no-dev --no-interaction


# ---------------------------------------------------------------------------
# Stage 3 - Runtime
# nginx in front of php-fpm, supervised by supervisord
# ---------------------------------------------------------------------------
FROM php:8.2-fpm-bookworm AS runtime

RUN apt-get update && apt-get install -y --no-install-recommends \
        nginx \
        supervisor \
        libpq-dev \
        libzip-dev \
        libicu-dev \
        libjpeg62-turbo-dev \
        libpng-dev \
        libfreetype6-dev \
        libonig-dev \
        unzip \
    && docker-php-ext-configure gd --with-jpeg --with-freetype \
    && docker-php-ext-install -j"$(nproc)" \
        pdo_mysql \
        pdo_pgsql \
        pgsql \
        bcmath \
        intl \
        mbstring \
        opcache \
        pcntl \
        zip \
        gd \
    && apt-get purge -y --auto-remove \
    && rm -rf /var/lib/apt/lists/*

COPY docker/nginx.conf /etc/nginx/sites-available/default
COPY docker/php.ini /usr/local/etc/php/conf.d/app.ini
COPY docker/supervisord.conf /etc/supervisor/conf.d/app.conf
COPY docker/entrypoint.sh /usr/local/bin/entrypoint

RUN chmod +x /usr/local/bin/entrypoint \
    && rm -f /etc/nginx/sites-enabled/default

WORKDIR /var/www/html

COPY --from=vendor /app /var/www/html
COPY --from=assets /app/public/build ./public/build

# The container filesystem is ephemeral, so these must exist before boot.
RUN mkdir -p \
        storage/app/public \
        storage/framework/cache/data \
        storage/framework/sessions \
        storage/framework/views \
        storage/logs \
        bootstrap/cache \
    && chown -R www-data:www-data storage bootstrap/cache

EXPOSE 80

ENTRYPOINT ["/usr/local/bin/entrypoint"]
CMD ["supervisord", "-c", "/etc/supervisor/conf.d/app.conf"]