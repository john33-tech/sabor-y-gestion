#!/usr/bin/env bash
# ---------------------------------------------------------------------------
# Entrypoint: prepara la app antes de arrancar Apache.
# Corre cada vez que el contenedor inicia (en local y en Railway).
# ---------------------------------------------------------------------------
set -e

cd /var/www/html

echo "==> Sabor & Gestión: arrancando contenedor"

# --- Safety net: asegurar que SOLO mpm_prefork esté habilitado en runtime ---
rm -f /etc/apache2/mods-enabled/mpm_event.load \
      /etc/apache2/mods-enabled/mpm_event.conf \
      /etc/apache2/mods-enabled/mpm_worker.load \
      /etc/apache2/mods-enabled/mpm_worker.conf 2>/dev/null || true

# --- Configurar Apache para escuchar en el $PORT inyectado por la plataforma -
PORT="${PORT:-8080}"
sed -ri "s!^Listen .*!Listen ${PORT}!" /etc/apache2/ports.conf
sed -ri "s!<VirtualHost \\*:[0-9]+>!<VirtualHost *:${PORT}>!" /etc/apache2/sites-available/000-default.conf
echo "==> Apache escuchará en el puerto ${PORT}"

# --- Permisos de TODO storage/ y bootstrap/cache/ ----------------------------
# Cuando docker-compose monta volumes (app_storage, app_logs) o Railway monta
# su Volume, los puntos de montaje aparecen como root y www-data no puede
# escribir. Hay que chownear cada vez que arranca el contenedor.
echo "==> Ajustando permisos de storage/ y bootstrap/cache/"
mkdir -p \
    /var/www/html/storage/logs \
    /var/www/html/storage/framework/cache/data \
    /var/www/html/storage/framework/sessions \
    /var/www/html/storage/framework/views \
    /var/www/html/storage/app/public/platos \
    /var/www/html/storage/app/public/comprobantes
chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache 2>/dev/null || true
chmod -R ug+rwx /var/www/html/storage /var/www/html/bootstrap/cache 2>/dev/null || true

# Si no hay APP_KEY definida, generarla
if [ -z "${APP_KEY}" ] || [ "${APP_KEY}" = "base64:" ]; then
    echo "==> APP_KEY no definida, generando una nueva (recordá copiarla a Railway)"
    php artisan key:generate --force --show || true
fi

# Esperar a MySQL
if [ -n "${DB_HOST}" ] && [ -n "${DB_PORT}" ]; then
    echo "==> Esperando a MySQL en ${DB_HOST}:${DB_PORT}..."
    for i in $(seq 1 30); do
        if mysqladmin ping -h"${DB_HOST}" -P"${DB_PORT}" -u"${DB_USERNAME}" -p"${DB_PASSWORD}" --silent 2>/dev/null; then
            echo "    MySQL listo."
            break
        fi
        sleep 2
    done
fi

# Caches
if [ "${APP_ENV}" = "production" ]; then
    echo "==> Optimizando para producción"
    php artisan config:cache
    php artisan route:cache
    php artisan view:cache
else
    echo "==> Limpiando caches (modo dev)"
    php artisan config:clear
    php artisan route:clear
    php artisan view:clear
fi

# Migraciones
if [ "${RUN_MIGRATIONS:-true}" = "true" ]; then
    echo "==> Corriendo migraciones"
    php artisan migrate --force
fi

# Seeders opcionales
if [ "${RUN_SEEDERS:-false}" = "true" ]; then
    echo "==> Corriendo seeders"
    php artisan db:seed --force || echo "==> Seeders fallaron, continuando"
fi

# Symlink de storage
rm -f /var/www/html/public/storage 2>/dev/null || true
php artisan storage:link || true

# --- Arrancar Reverb en background si CMD es Apache ---
if [ "${1:-}" = "apache2-foreground" ] && [ "${ENABLE_REVERB:-true}" = "true" ]; then
    echo "==> Lanzando Reverb en background (127.0.0.1:8081)"
    php artisan reverb:start --host=127.0.0.1 --port=8081 >> /var/log/reverb.log 2>&1 &
fi

echo "==> Arrancando Apache en puerto ${PORT:-8080}"
exec "$@"
