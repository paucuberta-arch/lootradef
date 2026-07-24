# Arquitectura técnica de Lootra

Lootra es una demostración monolítica Laravel 12. Blade renderiza la interfaz,
Alpine.js coordina las interacciones ligeras y los controladores delegan las
operaciones económicas y las reglas de juego en servicios del servidor.

## Capas principales

- `routes/`: rutas web, API y nombres canónicos. Las rutas históricas se
  mantienen como alias para no romper enlaces existentes.
- `app/Http/Controllers/`: entrada HTTP para autenticación, perfil, juegos,
  deportes, cajas, campaña y administración.
- `app/Services/`: reglas reutilizables. `GameBalanceService` separa cartera
  normal y saldo de campaña; `WalletService` registra movimientos; `LedgerService`
  crea asientos de doble entrada; `DemoTreasuryService` controla la tesorería
  ficticia; `CasePrizeService` resuelve premios de cajas.
- `app/Models/`: Eloquent para usuarios, carteras, partidas, movimientos,
  ledger, retiradas demo, cajas, campaña y actividad administrativa.
- `database/`: migraciones incrementales, seeders sintéticos y factory de
  usuarios.
- `resources/views/`: layouts públicos, autenticación, juegos, perfil,
  administración, componentes UI y páginas de error.
- `resources/js/` y `resources/css/`: Alpine.js, audio Web Audio, controles de
  juego, reducción de movimiento y sistema visual compartido.

## Economía demo

Todos los importes se expresan como `EUR_DEMO`/`EUR Demo`. No existe una
pasarela, cuenta bancaria, criptomoneda, conversión, transferencia ni premio
físico.

Una operación económica debe validar el importe en el servidor, ejecutarse en
una transacción, usar una clave de idempotencia, crear un movimiento de cartera,
crear un asiento equilibrado en el ledger y dejar actividad auditable si la
acción es administrativa.

Las retiradas demo reservan saldo de usuario y tesorería. Cancelar o rechazar
libera ambas reservas; completar consume la reserva. Los premios cosméticos de
cajas no crean saldo.

## Juegos

El catálogo único está en `config/casino_games.php` y
`config/arcade_games.php`. `GameCatalog` genera las fichas y verifica que la
ruta de juego exista. El resultado económico se calcula y persiste en el
servidor; el navegador solo anima y muestra la respuesta.

Los tokens de solicitud y de acción evitan dobles cargos, dobles premios y
repeticiones de acciones de manos multietapa. Las versiones matemáticas se
guardan en `config/game_math.php` y en los registros de partida.

## Panel administrativo

El panel existente cubre dashboard, usuarios, roles, MFA, reviews, feedback,
cajas, campaña, gráficos, logs y retiradas demo. Spatie Permission aplica los
permisos por ruta y `EnsureAdminMfa` exige configuración y verificación MFA
cuando `ADMIN_MFA_REQUIRED=true`.

Las acciones mutables de usuarios, roles, reviews, feedback, cajas y retiradas
se registran en `ActivityLog`. No hay módulos independientes de gestión de
juegos, retos, alertas o configuración global; esas áreas no forman parte del
alcance de esta demo y no deben presentarse como disponibles.

## Flags importantes

- `DEMO_ECONOMY_ENABLED`: habilita la economía ficticia.
- `DEMO_DEPOSITS_ENABLED`: habilita depósitos demo, únicamente en cuentas
  elegibles y con límites.
- `DEMO_ONLY_ENVIRONMENT`: requisito adicional para habilitar economía demo en
  producción.
- `ADMIN_MFA_REQUIRED`: exige MFA administrativo.
- `REQUIRE_VERIFIED_FOR_PLAY`: exige correo verificado antes de jugar.
- `BUSINESS_SIMULATION_MODE`: permite el comando de simulación empresarial sin
  escribir en la base de datos.
- `RICKYEDIT_CAMPAIGN_ENABLED`: activa la campaña opcional.

## Colas y scheduler

El entorno local usa `QUEUE_CONNECTION=sync`. En un entorno compartido se
recomienda Redis y un worker supervisado. El scheduler registra la sincronía de
partidos deportivos y tareas de campaña; debe ejecutarse con
`php artisan schedule:run` cada minuto cuando se habiliten esas funciones.

## Seguridad

La aplicación aplica CSRF, sesiones autenticadas, verificación opcional de
correo, limitación de peticiones, cabeceras defensivas, CSP, cookies seguras en
producción, autorización por roles/permisos, MFA administrativo y protección
contra IDOR en operaciones económicas. `APP_DEBUG=false`, HTTPS, hosts
confiables y secretos externos son obligatorios fuera de local.

## Tests y despliegue local

La suite rápida usa SQLite en memoria. La validación de persistencia se realiza
con `phpunit.mysql.xml` y `docker-compose.testing.yml`. Playwright usa el
servidor local y credenciales proporcionadas mediante `E2E_EMAIL` y
`E2E_PASSWORD`; nunca se guardan en el repositorio.

Para instalar localmente, sigue el [README](../README.md) y consulta
[`TESTING.md`](TESTING.md) para SQLite, MySQL y E2E.
