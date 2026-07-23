# Pruebas reproducibles de Lootra

## Contexto

La suite de PHPUnit usa SQLite en memoria por defecto. Esto permite ejecutar pruebas rápidas y aisladas, pero no sustituye la validación con MySQL. Antes de ejecutar tests, se limpian las cachés de Laravel para evitar que un `bootstrap/cache/config.php` generado desde `.env` local fuerce una conexión incorrecta.

## Suite rápida aislada

```bash
composer test
```

Resultado esperado: todas las pruebas PHPUnit pasan usando el entorno de testing definido en `phpunit.xml`.

## MySQL local aislado

El servicio de `docker-compose.testing.yml` usa la base `lootra_testing`, el puerto local `3307` y un usuario no root. Los valores son exclusivos para testing y no deben reutilizarse en producción.

```bash
docker compose -f docker-compose.testing.yml up -d --wait
php artisan optimize:clear
DB_CONNECTION=mysql DB_HOST=127.0.0.1 DB_PORT=3307 DB_DATABASE=lootra_testing DB_USERNAME=lootra_test DB_PASSWORD=lootra_test_password php artisan migrate:fresh --seed --env=testing
vendor/bin/phpunit --configuration phpunit.mysql.xml
docker compose -f docker-compose.testing.yml down
```

Para usar otro puerto o contraseña, define `LOOTRA_TEST_DB_PORT`, `LOOTRA_TEST_DB_PASSWORD` y sus variables relacionadas fuera del repositorio. Nunca uses la base de datos de producción ni ejecutes `migrate:fresh` sobre ella.

## Checks de build

```bash
composer validate --strict
npm ci
npm audit --omit=dev
npm run build
php artisan view:cache
git diff --check
```

## E2E y navegador

Playwright cubre el catálogo y una ronda controlada de cada juego. El runner necesita
Chromium instalado y un usuario de prueba local; nunca reutilices credenciales de
producción.

```bash
npm ci
node node_modules/playwright-core/cli.js install chromium
npm run test:e2e -- --project=chromium-desktop
npm run test:e2e -- --project=iphone-small
npm run test:e2e -- --project=android-small
```

Los informes, capturas y trazas se generan en `test-results/` y no deben versionarse.
