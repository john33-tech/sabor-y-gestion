# ─────────────────────────────────────────────────────────────────────────────
# Dockerfile para Laravel 11 + TiDB Cloud (SSL)
# Base: PHP 8.3 FPM en Alpine Linux
# ─────────────────────────────────────────────────────────────────────────────

# ── Etapa 1: Dependencias con Composer ───────────────────────────────────────
FROM composer:2.7 AS composer_builder

WORKDIR /app

COPY composer.json composer.lock ./

RUN composer install \
    --no-scripts \
    --no-autoloader \
    --prefer-dist \
    --ignore-platform-reqs

COPY . .

RUN composer dump-autoload --optimize

# ── NUEVA Etapa 2: Compilación de activos (Vite) ──────────────────────────────
FROM node:20-alpine AS node_builder
WORKDIR /app
COPY package.json package-lock.json ./
RUN npm install
COPY . .
RUN npm run build

# ── Etapa 2: Imagen final ─────────────────────────────────────────────────────
FROM php:8.3-fpm-alpine

LABEL maintainer="Tu Nombre <tu@email.com>"

# ── 1. Paquetes del sistema (UNA sola vez, incluyendo supervisor) ─────────────
RUN apk update && apk add --no-cache \
    nginx \
    supervisor \
    bash \
    curl \
    git \
    zip \
    unzip \
    openssl \
    ca-certificates \
    openssl-dev \
    libzip-dev \
    libpng-dev \
    libjpeg-turbo-dev \
    freetype-dev \
    icu-dev \
    $PHPIZE_DEPS \
    && rm -rf /var/cache/apk/*

# ── 2. Extensiones PHP ────────────────────────────────────────────────────────
RUN docker-php-ext-configure gd \
        --with-freetype \
        --with-jpeg \
    && docker-php-ext-install -j$(nproc) \
        pdo \
        pdo_mysql \
        mysqli \
        zip \
        gd \
        opcache \
        bcmath \
        pcntl \
        intl \
    && docker-php-ext-enable pdo pdo_mysql opcache

# ── 3. Configuración PHP ──────────────────────────────────────────────────────
RUN echo "upload_max_filesize = 50M"      >> /usr/local/etc/php/conf.d/laravel.ini \
    && echo "post_max_size = 50M"         >> /usr/local/etc/php/conf.d/laravel.ini \
    && echo "memory_limit = 256M"         >> /usr/local/etc/php/conf.d/laravel.ini \
    && echo "max_execution_time = 60"     >> /usr/local/etc/php/conf.d/laravel.ini

# ── 4. OPcache para producción ────────────────────────────────────────────────
RUN echo "opcache.enable=1"                      >> /usr/local/etc/php/conf.d/opcache.ini \
    && echo "opcache.memory_consumption=128"     >> /usr/local/etc/php/conf.d/opcache.ini \
    && echo "opcache.interned_strings_buffer=8"  >> /usr/local/etc/php/conf.d/opcache.ini \
    && echo "opcache.max_accelerated_files=4000" >> /usr/local/etc/php/conf.d/opcache.ini \
    && echo "opcache.validate_timestamps=0"      >> /usr/local/etc/php/conf.d/opcache.ini
# ── 7. Copiar la aplicación (UNA sola vez, destino correcto) ──────────────────
WORKDIR /var/www/html
COPY --from=composer_builder /app /var/www/html

# COPIAR activos compilados (Crucial para Laravel 11 + Vite)
COPY --from=node_builder /app/public/build /var/www/html/public/build
# ── 5. Copiar configuraciones de servicios ────────────────────────────────────
COPY docker/nginx/nginx.conf              /etc/nginx/nginx.conf
COPY docker/supervisor/laravel.ini        /etc/supervisor.d/laravel.ini

# ── 6. Copiar el script de inicio ─────────────────────────────────────────────
COPY docker/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh



# ── 8. Permisos ───────────────────────────────────────────────────────────────
RUN mkdir -p storage/framework/{sessions,views,cache} \
    && mkdir -p /var/log/supervisor \
    && mkdir -p /run/nginx \
    && chown -R www-data:www-data /var/www/html \
    && chmod -R 775 /var/www/html/storage \
    && chmod -R 775 /var/www/html/bootstrap/cache

RUN chown -R www-data:www-data /var/www/html/public \
    && chmod -R 755 /var/www/html/public/build
# ── 9. Puerto y punto de entrada (SIEMPRE al final) ───────────────────────────
EXPOSE 8080

ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]
