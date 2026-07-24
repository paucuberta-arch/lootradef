# Lootra

Lootra es una demostración web de una plataforma de juegos y de su economía
virtual. Está construida con Laravel, Blade, Tailwind CSS, Alpine.js, Vite y
MySQL. Los juegos, depósitos, retiradas, premios, cajas y métricas utilizan
únicamente créditos ficticios.

> **Aviso importante:** Proyecto de demostración. No utiliza dinero real. Los
> créditos, depósitos, premios y retiradas son ficticios, no tienen valor
> económico y no pueden canjearse ni transferirse.

## Funcionalidades

- Catálogo de 21 juegos, incluidos slots, ruleta, blackjack, crash y Lootra Originals.
- Registro, inicio de sesión, verificación de correo y recuperación de contraseña.
- Saldo demo, bonos, historial y libro mayor auditable.
- Depósitos y retiradas demo protegidos por feature flags.
- Cajas e inventario de cosméticos virtuales sin valor económico.
- Apuestas deportivas simuladas, retos, rankings, reseñas y campaña RickyEdit opcional.
- Panel administrativo con roles, MFA, métricas y auditoría.
- Simulador económico determinista mediante `economy:simulate`.

## Capturas

La lista de capturas finales y sus condiciones de generación está en
[`docs/FINAL_SCREENSHOTS.md`](docs/FINAL_SCREENSHOTS.md). Las imágenes de una
ejecución local se guardan en `test-results/final/` y siempre utilizan datos
sintéticos.

## Requisitos

- PHP 8.2 o superior con las extensiones necesarias para Laravel y SQLite/MySQL.
- Composer 2.
- Node.js compatible con Vite 8 y npm.
- MySQL para el entorno habitual; SQLite en memoria para la suite rápida.
- Redis en producción para sesiones, caché y colas compartidas.

## Instalación local

```bash
cp .env.example .env
composer install
php artisan key:generate
npm ci
```

Configura en `.env` una base de datos local y ejecuta:

```bash
php artisan migrate --seed
php artisan storage:link
npm run build
php artisan serve
```

La aplicación estará disponible en `http://localhost:8000`. El seeder base
crea roles y permisos, pero no instala contraseñas administrativas conocidas.
Para una demo con datos sintéticos adicionales, ejecuta únicamente en local o
testing:

```bash
php artisan db:seed --class=DemoDataSeeder
php artisan db:seed --class=PlatformActivitySeeder
```

Estos seeders generan usuarios y actividad ficticios con contraseñas aleatorias;
no deben ejecutarse en producción ni se deben reutilizar sus datos como cuentas
reales. Para acceder al panel, crea una cuenta local y asígnale el rol apropiado
desde un flujo administrativo controlado o mediante Tinker, usando una
contraseña local que no se guarde en el repositorio.

Para una instalación reproducible de presentación, crea las cuentas de usuario
y administrador en una base local aislada, define `E2E_EMAIL` y `E2E_PASSWORD`
solo en el entorno de ejecución y activa MFA si vas a mostrar el panel. No hay
credenciales demo predefinidas en el repositorio.

## Configuración demo

Las funciones económicas se controlan con flags. En local, `.env.example` activa
la economía demo; los depósitos demo permanecen desactivados hasta activar
explícitamente `DEMO_DEPOSITS_ENABLED=true`. En producción, la economía y los
depósitos demo deben permanecer desactivados salvo en un entorno expresamente
identificado como demostración.

Variables principales:

- `DEMO_ECONOMY_ENABLED`: habilita las rutas de economía demo.
- `BUSINESS_SIMULATION_MODE`: habilita el comando de simulación empresarial.
- `DEMO_ONLY_ENVIRONMENT`: requisito adicional para economía demo en producción.
- `DEMO_DEPOSITS_ENABLED`: habilita depósitos exclusivamente ficticios.
- `VIRTUAL_CURRENCY_CODE` y `VIRTUAL_CURRENCY_LABEL`: identifican la moneda demo.
- `ADMIN_MFA_REQUIRED`: exige MFA para operaciones administrativas.
- `REQUIRE_VERIFIED_FOR_PLAY`: exige correo verificado antes de jugar.

No existe ninguna integración con Stripe, PayPal, bancos, criptomonedas reales,
transferencias o premios físicos.

## Tests y build

```bash
composer test
composer validate --strict
npm run build
php artisan view:cache
git diff --check
```

Para la configuración completa de SQLite, MySQL de testing y Playwright,
consulta [`docs/TESTING.md`](docs/TESTING.md). Las pruebas E2E requieren un
usuario de prueba creado fuera del repositorio mediante `E2E_EMAIL` y
`E2E_PASSWORD`.

## Simulación económica

Con la flag activada en un entorno local aislado:

```bash
BUSINESS_SIMULATION_MODE=true php artisan economy:simulate --users=100 --rounds=100 --seed=20260724
```

El comando no escribe en la base de datos y muestra apuesta total, premios,
RTP observado, GGR, ingresos complementarios, costes y resultado neto en EUR
Demo. No representa una previsión financiera real.

## Documentación

- [`docs/DEMO_GUIDE.md`](docs/DEMO_GUIDE.md): recorrido recomendado para presentar el proyecto.
- [`docs/ARCHITECTURE.md`](docs/ARCHITECTURE.md): arquitectura, economía, juegos, seguridad, flags y despliegue local.
- [`docs/FINAL_SCREENSHOTS.md`](docs/FINAL_SCREENSHOTS.md): lista de capturas finales y reglas de privacidad.
- [`docs/TESTING.md`](docs/TESTING.md): tests, build, MySQL aislado y E2E.
- [`docs/SECURITY_DEPLOYMENT.md`](docs/SECURITY_DEPLOYMENT.md): configuración defensiva de despliegue.
- [`docs/demo-economy.md`](docs/demo-economy.md): economía demo y límites.
- [`docs/VISUAL_DESIGN_SYSTEM.md`](docs/VISUAL_DESIGN_SYSTEM.md): sistema visual.

## Limitaciones conocidas

- La infraestructura de producción, el proveedor de correo y Redis deben
  configurarse fuera de este repositorio.
- Las auditorías de dependencias necesitan acceso de red al registro oficial.
- Los resultados estadísticos observados dependen de la semilla y del tamaño
  de cada simulación; no constituyen una garantía de rentabilidad.

## Licencia

Proyecto educativo y de demostración. Añade la licencia correspondiente antes
de distribuirlo públicamente.
