# RickyEdit x Lootra — El Reto de los 1.000

## Resumen

Campaña demo en la que cada usuario dispone de una única participación con 1.000 créditos separados de su cartera normal. El tiempo de juego es de 15 minutos desde la confirmación de inicio. El servidor controla tiempo, saldo, puntuación, partidas e idempotencia.

La puntuación oficial es `floor(final_balance)`: el saldo final se trunca a créditos enteros al completar o expirar el reto. El resultado queda congelado y no puede reabrirse.

## Activación y variables

La configuración central está en `config/campaigns.php`. Variables disponibles:

```dotenv
RICKYEDIT_CAMPAIGN_ENABLED=true
RICKYEDIT_OFFICIAL_ASSETS_ENABLED=false
RICKYEDIT_INITIAL_BALANCE=1000
RICKYEDIT_DURATION_MINUTES=15
RICKYEDIT_CREATOR_SCORE=1250
RICKYEDIT_START_AT=2026-07-20 10:00:00
RICKYEDIT_END_AT=2026-08-20 23:59:59
RICKYEDIT_YOUTUBE_URL=https://www.youtube.com/watch?v=...
```

`enabled=false` oculta las integraciones, impide nuevos inicios y expira de forma segura cualquier participación activa en la siguiente petición. Las fechas son opcionales y se interpretan con la zona horaria de Laravel.

Tras cambiar variables en producción:

```bash
php artisan config:clear
php artisan config:cache
```

## Migraciones

- `2026_07_17_010000_create_campaign_tables.php`: atribuciones, participaciones, ledger y eventos.
- `2026_07_17_020000_link_games_to_campaign_challenges.php`: vincula partidas y rondas multietapa, añade tokens idempotentes a Poker/Crash y origen de datos a usuarios.

Son migraciones nuevas y reversibles. No se modifican migraciones históricas.

```bash
php artisan migrate
```

No debe utilizarse `migrate:fresh` en un entorno con datos. Las pruebas usan SQLite en memoria.

## Rutas

Públicas:

- `GET /rickyedit`
- `GET /rickyedit/ranking`
- `POST /rickyedit/event`

Autenticadas:

- `GET /rickyedit/reto`
- `POST /rickyedit/reto/iniciar`
- `POST /rickyedit/reto/finalizar`
- `GET /rickyedit/reto/estado`

Administración:

- `GET /admin/campanas/rickyedit`

La ruta administrativa exige rol `super_admin|admin` y permiso `campaigns.stats.view`.

## Atribución y registro

El middleware `CaptureCampaignAttribution` conserva en sesión y en `campaign_attributions`:

- `utm_source`
- `utm_medium`
- `utm_campaign`
- `utm_content`
- `referrer`
- `creator_code`

Entrar por `/rickyedit`, usar parámetros UTM o un código de creador inicia la atribución. El registro normal no cambia. El registro atribuido muestra el mensaje de campaña, marca la conversión y redirige a la introducción. El contador solo empieza al confirmar el reto.

## Saldo y juegos

`GameBalanceService` resuelve el contexto de cada operación:

- Sin reto activo: delega en la cartera y `WalletService` existentes.
- Con reto activo y juego permitido: utiliza `campaign_challenges.current_balance` y su ledger.
- Una ronda multietapa conserva `campaign_challenge_id` desde el inicio hasta su cierre.

Integrados:

- Slots
- Ruleta europea y Lightning
- Blackjack VIP y clásico
- Crash
- Poker All-In y Poker contra el dealer
- Lootra Originals/Arcade

Cajas y apuestas deportivas se promocionan o permanecen navegables, pero iniciar una operación queda bloqueado durante un reto activo. Así no existe una vía de transferencia entre inventario, apuestas deportivas y saldo de campaña.

## Seguridad

- Restricción única `user_id + campaign_key` y bloqueo del usuario al crear la participación.
- Tiempo, resultado y puntuación calculados en servidor.
- `lockForUpdate` para saldos, estados y rondas.
- Ledger independiente; no hay métodos de transferencia hacia `carteras`.
- Tokens idempotentes en juegos instantáneos e inicio de rondas.
- La ronda se vincula a la participación para evitar cambiar de contexto a mitad de mano.
- CSRF de Laravel en todas las mutaciones web.
- Consultas por usuario en manos/rondas para impedir IDOR.
- Validación explícita, asignación controlada y límites de apuesta.
- Rate limiting para inicio, eventos, estado y acciones de juego durante la campaña.
- Una participación expirada o completada no admite nuevos movimientos.

Si el tiempo vence con una ronda abierta, el saldo actual se congela y la ronda deja de admitir acciones. No se acredita ningún resultado posterior al vencimiento.

## Ranking y privacidad

Solo se publican participaciones reales completadas o expiradas. Se excluyen:

- `is_demo=true`
- Origen `test` o `simulated`
- Usuarios marcados como demo
- Administradores y moderadores

El ranking usa un alias público generado al iniciar y un avatar de inicial. Nunca expone correo ni nombre completo.

## Analítica

Eventos registrados y deduplicados:

- `landing_view`
- `banner_click`
- `registration_started`
- `registration_completed`
- `challenge_started`
- `game_completed`
- `challenge_completed`
- `ranking_viewed`
- `result_shared`
- `day_1_return`
- `day_7_return`

El panel separa `real`, `test` y `simulated`, y muestra visitas, registros, retos, conversión, partidas, usuarios que superaron la puntuación, retención y fuentes UTM.

## Seeder

`RickyEditCampaignSeeder` crea tráfico bajo previo, pico en el lanzamiento del vídeo, descenso, segundo pico por directo y actividad residual. Los usuarios y todos los registros de campaña se marcan como demo/simulados.

```bash
php artisan db:seed --class=RickyEditCampaignSeeder
```

No está incluido en `DatabaseSeeder`, para evitar insertar simulaciones accidentalmente.

## Recursos visuales

Directorio: `public/images/campaigns/rickyedit/`.

- `hero.webp`
- `portrait.webp`
- `logo-collab.svg`
- `video-poster.webp`
- `badge.svg`
- `share-card.webp`
- `challenge-hero-v2.webp` (hero generado para la landing del reto)
- `ricky_edit2-removebg-preview.webp` (recorte transparente aportado e integrado en el hero de la landing)
- `aifaceswap-390bed6ee726c1170c6d1df14ca5d5b4.webp` (hero horizontal del home)
- `aifaceswap-a2a4b24b66d4ab9e64b25f2d6df8e767.webp` (hero móvil del home)
- `aifaceswap-b014c556f19b1e3ed6bac071a497552a.webp` (tarjeta lateral del home)

Los seis recursos con nombre semántico siguen siendo placeholders abstractos. Las tres creatividades `aifaceswap-*` fueron aportadas y autorizadas para el home de la colaboración y se seleccionan desde `config/campaigns.php`. Para reemplazar los placeholders restantes por recursos aprobados, colóquelos con los mismos nombres dentro de `public/images/campaigns/rickyedit/official/` y active `RICKYEDIT_OFFICIAL_ASSETS_ENABLED=true`. Si falta un archivo oficial, se usa el recurso base correspondiente.

## Componentes Blade

- `campaign.rickyedit.banner`
- `campaign.rickyedit.sidebar`
- `campaign.rickyedit.progress`
- `campaign.rickyedit.leaderboard`
- `campaign.rickyedit.floating-button`
- `campaign.rickyedit.home-promo`

`home-promo` ofrece hero responsive y tarjeta vertical; únicamente se renderiza con la campaña activa. El banner global utiliza `localStorage` con una clave versionada para recordar su cierre.

### Hero generado para la landing

Se creó con la herramienta integrada de generación de imágenes y se optimizó a WebP 1920×768. Prompt final:

> Hero cinematográfico premium para una landing de reto de casino demo de 15 minutos y 1.000 créditos: arena futurista negra, túnel violeta, monedas y palos de cartas flotantes, trofeo dorado facetado a la derecha, anillo de cronómetro, barras sutiles de ranking y estelas de luz. Composición panorámica con el 45 % izquierdo oscuro y despejado para texto HTML. Paleta negro, violeta, magenta, cian y oro. Sin texto, letras, logotipos, marcas, personas, rostros, marcas de agua, billetes ni símbolos de moneda real.

## Pruebas

La cobertura específica está en `tests/Feature/RickyEditCampaignTest.php` e incluye activación, atribución, registro, duración, inicio/finalización, separación de saldo, doble participación, ranking, exclusión demo y permisos.

```bash
php artisan test tests/Feature/RickyEditCampaignTest.php
php artisan test
npm run build
```

## Archivos principales modificados

- Rutas web, middleware web y permisos.
- Controladores de autenticación, perfil, juegos, cajas, apuestas deportivas y administración.
- Modelos `Usuario` y `Partida`.
- Servicios de campaña, reto, ranking, analítica y resolución de saldo.
- Layouts, navegación, inicio, registro, perfil y vistas de los juegos.
- `resources/js/app.js`, `resources/css/app.css`, `.env.example` y `phpunit.xml`.

## Pendientes antes del lanzamiento

- Confirmar puntuación oficial de RickyEdit.
- Aprobar fechas, URL del vídeo y textos legales finales.
- Recibir autorización y recursos oficiales; no se incluye imagen de RickyEdit.
- Revisar límites de apuestas y lista definitiva de juegos con el equipo de campaña.
- Definir política operativa ante incidencias de red durante los 15 minutos.
- Ejecutar migraciones y pruebas en staging con el mismo motor de base de datos de producción.
