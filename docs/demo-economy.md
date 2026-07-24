# Economía demo y simulación empresarial

Lootra utiliza `EUR_DEMO` exclusivamente como crédito ficticio. No existe una
pasarela de pagos, una cuenta bancaria, una conversión a dinero real ni un
premio físico. El aviso de simulación se muestra en el layout autenticado.

## Activación

En desarrollo y testing la economía demo está activa por defecto. Para un
entorno de demostración empresarial se debe configurar:

```dotenv
DEMO_ECONOMY_ENABLED=true
BUSINESS_SIMULATION_MODE=true
DEMO_ONLY_ENVIRONMENT=true
VIRTUAL_CURRENCY_CODE=EUR_DEMO
VIRTUAL_CURRENCY_LABEL="EUR Demo"
```

En producción normal, `DEMO_ECONOMY_ENABLED=false` mantiene bloqueadas las
rutas de juego, cajas y retiradas demo. El proveedor de aplicación falla al
arrancar si se intenta activar la economía sin marcar el entorno como
`DEMO_ONLY_ENVIRONMENT=true`.

## Trazabilidad

Los cambios de cartera pasan por `WalletService` y generan un movimiento en
`wallet_movements` y una transacción equilibrada en el ledger de doble entrada.
Las partidas de campañas tienen su propia cuenta demo y también se reflejan en
el ledger. Las claves de idempotencia y los bloqueos pesimistas protegen los
reintentos y las operaciones concurrentes.

Las retiradas demo reservan saldo y tesorería. Una aprobación no libera el
saldo; una cancelación o rechazo libera ambos; una finalización consume la
reserva. Los métodos disponibles son ficticios y no aceptan datos bancarios.

## Matemáticas

`config/game_math.php` versiona las reglas relevantes. Cada `Partida` conserva
la versión matemática aplicada. Cosmic Keno tiene una tabla con RTP analítico
aproximado del 96%; Crash limita el multiplicador a `CRASH_MAX_MULTIPLIER`
para mantener una exposición máxima finita. Blackjack se etiqueta como
variable según la estrategia hasta disponer de un enumerador completo.

Las cajas entregan cosméticos y puntos virtuales. Activar una recompensa no
crea saldo ni una obligación monetaria.

## Simulación reproducible

El comando no escribe en la base de datos:

```bash
BUSINESS_SIMULATION_MODE=true php artisan economy:simulate \
  --users=100 --rounds=100 --rtp=0.96 --seed=123 --json
```

La salida separa apuestas, premios, GGR, ingresos complementarios, costes,
resultado neto, usuarios ganadores/perdedores y un hash reproducible. Una
simulación no demuestra sostenibilidad por sí sola: deben repetirse semillas y
escenarios de estrés, y deben incluirse reservas, bonos, costes e ingresos
complementarios.

## Operación previa al despliegue

1. Ejecutar `php artisan migrate` en la base de datos de demostración.
2. Verificar que `MYSQL_ATTR_SSL_CA` apunta al certificado esperado cuando
   MySQL sea remoto.
3. Ejecutar `php artisan test` y `npm run build`.
4. Ejecutar `composer audit` y `npm audit` desde un entorno con red y revisar
   los avisos oficiales.
5. Proporcionar credenciales efímeras para ejecutar los smoke tests E2E; no se
   almacenan credenciales por defecto en los tests.
