#!/bin/bash
set -e

echo "═══════════════════════════════════════════"
echo "   Iniciando Laravel + TiDB Cloud"
echo "═══════════════════════════════════════════"

# ── 1. Limpiar caché de configuración vieja ───────────────────────────────────
echo "🧹 Limpiando caché..."
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear

# ── 2. Verificar que el certificado CA existe ─────────────────────────────────
if [ -z "$DB_SSL_CA" ]; then
    echo "⚠️  ADVERTENCIA: DB_SSL_CA no está definida"
elif [ ! -f "$DB_SSL_CA" ]; then
    echo "❌ ERROR CRÍTICO: El certificado CA no existe en: $DB_SSL_CA"
    echo "   Asegúrate de que storage/ssl/isrgrootx1.pem esté en el repositorio"
    exit 1
else
    echo "✅ Certificado CA encontrado: $DB_SSL_CA"
fi

# ── 3. Verificar conexión a TiDB Cloud ───────────────────────────────────────
echo "🔌 Verificando conexión a TiDB Cloud..."
MAX_RETRIES=10
RETRY=0
until php artisan db:monitor 2>/dev/null || [ $RETRY -ge $MAX_RETRIES ]; do
    RETRY=$((RETRY+1))
    echo "   ⏳ Intento $RETRY/$MAX_RETRIES — esperando conexión..."
    sleep 3
done

if [ $RETRY -ge $MAX_RETRIES ]; then
    echo "❌ No se pudo conectar a TiDB Cloud después de $MAX_RETRIES intentos"
    echo "   Verifica: DB_HOST, DB_PORT, DB_SSL_CA, credenciales"
    # No salimos con error para que Render muestre los logs
fi

# ── 4. Ejecutar migraciones ───────────────────────────────────────────────────
echo "🗄️  Ejecutando migraciones..."
php artisan migrate --force --no-interaction

# ── 5. Optimizar para producción ─────────────────────────────────────────────
if [ "$APP_ENV" = "production" ]; then
    echo "⚡ Optimizando para producción..."
    php artisan config:cache
    php artisan route:cache
    php artisan view:cache
    php artisan event:cache
fi

# ── 6. Generar APP_KEY si no existe ──────────────────────────────────────────
if [ -z "$APP_KEY" ]; then
    echo "🔑 Generando APP_KEY..."
    php artisan key:generate --force
fi

echo "✅ Aplicación lista. Iniciando Nginx + PHP-FPM..."
echo "═══════════════════════════════════════════"

# Iniciar Supervisor (que gestiona Nginx + PHP-FPM)
exec /usr/bin/supervisord -c /etc/supervisor.d/laravel.ini
