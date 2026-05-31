
# ---------------------------------------------------------------------------
# Sabor & Gestión — Dockerfile para Laravel 12 + PHP 8.2 + Apache
# Multi-stage: composer (deps PHP) -> node (build Vite) -> runtime (Apache+PHP)
# Compatible con Railway, Fly.io, Render, y `docker compose up` local.
# ---------------------------------------------------------------------------

# ============================================================================
# STAGE 1 — Composer: instala dependencias PHP sin dev
# ============================================================================
FROM composer:2 AS vendor

WORKDIR /app

COPY composer.json composer.lock ./

RUN composer install \
    --no-dev \
    --no-interaction \
    --no-progress \
    --no-scripts \
    --prefer-dist \
    --optimize-autoloader \
    --ignore-platform-req=ext-gd

# ============================================================================
# STAGE 2 — Node: compila assets con Vite
# ============================================================================
FROM node:20-alpine AS assets

WORKDIR /app

COPY package.json package-lock.json ./
RUN npm ci

COPY . .
COPY --from=vendor /app/vendor ./vendor

RUN npm run build

# ============================================================================
# STAGE 3 — Runtime: Apache + PHP 8.2
# ============================================================================
FROM php:8.2-apache AS runtime

# Dependencias del sistema y extensiones PHP
RUN apt-get update && apt-get install -y --no-install-recommends \
        git \
        curl \
        unzip \
        libzip-dev \
        libpng-dev \
        libjpeg-dev \
        libfreetype6-dev \
        libonig-dev \
        libxml2-dev \
        libicu-dev \
        default-mysql-client \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) \
        pdo_mysql \
        mysqli \
        mbstring \
        exif \
        pcntl \
        bcmath \
        gd \
        zip \
        intl \
        opcache \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# Apache: forzar SOLO mpm_prefork + habilitar proxy_wstunnel para Reverb
RUN rm -f /etc/apache2/mods-enabled/mpm_*.load \
          /etc/apache2/mods-enabled/mpm_*.conf \
 && ln -sf /etc/apache2/mods-available/mpm_prefork.load /etc/apache2/mods-enabled/mpm_prefork.load \
 && ln -sf /etc/apache2/mods-available/mpm_prefork.conf /etc/apache2/mods-enabled/mpm_prefork.conf \
 && a2enmod rewrite headers proxy proxy_http proxy_wstunnel

# Proxy WS: /app/* y /apps/* → Reverb local en 8081
COPY docker/reverb-proxy.conf /etc/apache2/conf-available/reverb-proxy.conf
RUN a2enconf reverb-proxy

ENV APACHE_DOCUMENT_ROOT=/var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf \
 && sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

# Configuración PHP para producción
COPY docker/php.ini /usr/local/etc/php/conf.d/zz-app.ini

# Copiamos código de la app
WORKDIR /var/www/html

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
COPY --chown=www-data:www-data . .
COPY --from=vendor --chown=www-data:www-data /app/vendor ./vendor
COPY --from=assets --chown=www-data:www-data /app/public/build ./public/build

RUN chown -R www-data:www-data storage bootstrap/cache \
    && chmod -R ug+rwx storage bootstrap/cache

# Entrypoint
COPY docker/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

ENV PORT=8080
EXPOSE 8080

ENTRYPOINT ["entrypoint.sh"]
CMD ["apache2-foreground"]
