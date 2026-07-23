# Despliegue seguro de Lootra

Este documento no contiene secretos. Los valores sensibles deben proceder de un gestor de secretos del proveedor.

## Construcción

~~~bash
composer validate --strict
composer install --no-interaction --prefer-dist --optimize-autoloader
php artisan test --compact
npm ci
npm run build
php artisan config:cache
php artisan route:cache
php artisan view:cache
~~~

Para la imagen o servidor final, instala Composer sin dependencias de desarrollo:

~~~bash
composer install --no-dev --no-interaction --prefer-dist --classmap-authoritative
~~~

## Variables obligatorias

Usa .env.production.example como plantilla y define los valores reales fuera del repositorio:

- APP_ENV=production
- APP_DEBUG=false
- APP_KEY válido
- APP_URL=https://...
- SESSION_DRIVER=redis
- CACHE_DRIVER=redis
- QUEUE_CONNECTION=redis
- SESSION_SECURE_COOKIE=true
- ADMIN_MFA_REQUIRED=true
- DEMO_DEPOSITS_ENABLED=false
- TRUSTED_HOSTS con hosts exactos
- TRUSTED_PROXIES con CIDR exactos, si existe un proxy
- CORS_ALLOWED_ORIGINS con orígenes exactos
- MAIL_HOST y TLS configurados

La aplicación aborta al arrancar en producción si faltan varias de estas garantías.

## Workers y scheduler

Mantén al menos un worker supervisado:

~~~bash
php artisan queue:work --sleep=3 --tries=3 --timeout=90
~~~

Y ejecuta el scheduler cada minuto:

~~~cron
* * * * * cd /var/www/lootra && php artisan schedule:run >> /dev/null 2>&1
~~~

## Optimización segura

Activa OPcache en PHP-FPM, sirve únicamente public/, usa Redis compartido para sesiones/cache/colas y aplica las migraciones antes de cambiar tráfico. Los índices añadidos por la migración de rendimiento están orientados a historial de cartera, partidas, apuestas, reviews, feedback y panel administrativo.

## Verificación posterior

~~~bash
curl -I http://example.com/
curl -I https://example.com/
php artisan about
php artisan migrate:status
php artisan queue:failed
~~~

HTTP debe redirigir a HTTPS. Nunca publiques .env, storage/, logs, vendor/ ni source maps.
