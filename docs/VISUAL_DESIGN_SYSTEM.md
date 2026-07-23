# Sistema visual y de movimiento de Lootra

Estado: primera iteración implementada y pendiente de validación en navegador real.

## 1. Stack y arquitectura visual

Lootra utiliza Laravel Blade para renderizar las pantallas, Vite para el pipeline de frontend, Tailwind CSS 4 para utilidades y tokens, Alpine.js para interacción local y JavaScript/CSS nativo para las animaciones de los juegos. No se detectaron Phaser, PixiJS, Three.js, GSAP ni un motor externo.

La lógica matemática y los pagos permanecen en los controladores/servicios del servidor. Las mejoras visuales únicamente representan el resultado recibido y no generan resultados, premios ni probabilidades en el cliente.

## 2. Inventario de juegos

| Juego | Vista principal | Tecnología | Recursos | Animación actual | Prioridad |
| --- | --- | --- | --- | --- | --- |
| Slots | `resources/views/games/slots.blade.php` | DOM, Alpine, CSS | Atlas WebP por tema | Giro escalonado, desenfoque, partículas | Alta |
| Ruleta | `resources/views/games/ruleta.blade.php` | DOM, Alpine, Web Animations API | Mesa, rueda y layout WebP | Rueda/bola, desaceleración, aterrizaje | Alta |
| Blackjack | `resources/views/games/blackjack.blade.php` | DOM, Alpine, CSS | Mesa de blackjack WebP | Reparto, giro y revelado de cartas | Alta |
| Crash | `resources/views/games/crash.blade.php` | SVG, Alpine, `requestAnimationFrame` | Fondo cósmico WebP | Curva, cohete, sincronización de visibilidad | Alta |
| Quantum Plinko | `resources/views/games/quantum-plinko.blade.php` | Canvas 2D, Alpine, `requestAnimationFrame` | Board y partículas Canvas | Gravedad, rebotes, trazas y partículas | Alta |
| Originals | `resources/views/games/arcade.blade.php` | DOM, Alpine, CSS | Pack visual Lootra | Variantes de rueda, minas, dados, hilo, keno, etc. | Media |
| Poker contra dealer | `resources/views/games/poker-dealer.blade.php` | DOM, Alpine, CSS | Mesa de póker WebP | Reparto por fases y showdown | Alta |
| Poker All-In | `resources/views/games/poker-live.blade.php` | DOM, Alpine, CSS | Mesa de póker WebP | Reparto secuencial y revelado | Alta |

## 3. Componentes visuales compartidos

- `resources/views/layouts/game.blade.php`: envolvente común de las experiencias de juego.
- `resources/views/components/ui/game-toolbar.blade.php`: estado de mesa, sonido y pantalla completa.
- `resources/css/app.css`: tokens, mundos visuales, estados responsive y reducción de movimiento.
- `resources/js/app.js`: preferencias compartidas, audio Web Audio ligero, fullscreen y estado global de wallet.
- `resources/views/components/ui/game-header.blade.php` y `game-result.blade.php`: componentes disponibles para una segunda fase de extracción de markup repetido.

## 4. Dirección artística

La dirección común es casino premium oscuro: superficies azul-negro, oro para valor y acciones de mesa, cian/violeta para tecnología, verde para estados positivos y rojo únicamente para pérdidas/errores. Los juegos conservan personalidad propia:

- Slots: lujo mitológico, rodillos con profundidad y lectura rápida.
- Ruleta: mesa de estudio, madera/oro, rueda dominante y resultado validado.
- Blackjack/póker: mesa física oscura, cartas limpias, sombras y reparto escalonado.
- Crash: cubierta de vuelo cósmica, telemetría y gráfica legible.
- Plinko: laboratorio Quantum, trazas y partículas limitadas al área de juego.
- Originals: arcade modular, con un mismo lenguaje de controles y resultados.

## 5. Tokens de movimiento

Los tokens están en `resources/css/app.css`:

```css
--motion-fast: 160ms;
--motion-base: 320ms;
--motion-emphasis: 620ms;
--ease-standard: cubic-bezier(.2, .8, .2, 1);
--ease-emphasis: cubic-bezier(.16, 1, .3, 1);
```

Reglas de uso:

1. `fast` para foco, selección y pulsación.
2. `base` para paneles, botones y cambios de estado.
3. `emphasis` para entrada de resultado, carta, rodillo o celebración.
4. El resultado visual se inicia después de recibir la respuesta real del servidor cuando la animación representa un desenlace.
5. `prefers-reduced-motion` elimina bucles decorativos, reduce partículas y convierte las esperas visuales en transiciones breves.
6. Canvas y temporizadores se pausan o reducen cuando la pestaña queda oculta para evitar consumo innecesario.

## 6. Audio

El sistema compartido no descarga archivos ni añade dependencias: usa Web Audio con tonos cortos. La reproducción comienza únicamente después de una interacción del usuario, respeta el botón global de sonido y persiste la preferencia en `localStorage` bajo una clave no sensible.

Eventos disponibles: `click`, `select`, `spin`, `reel-stop`, `card`, `land`, `win`, `jackpot`, `lose`, `cashout`, `crash` y `error`.

No se usa audio para anticipar un premio no confirmado. Las celebraciones distinguen entre premio bruto positivo, empate/devolución y pérdida neta.

## 7. Responsive y accesibilidad

- Controles táctiles con separación y tamaño adecuados.
- `env(safe-area-inset-*)` para navegación móvil y controles flotantes.
- Inputs de 16px en móvil para evitar zoom automático de Safari.
- Estados `focus-visible`, `aria-live`, `role="alert"`, `aria-label` y `aria-pressed` en controles relevantes.
- Pantalla completa opcional; no se considera necesaria para jugar.
- La información importante no depende únicamente de color.
- El modo de movimiento reducido se actualiza si cambia la preferencia del sistema durante la sesión.

## 8. Rendimiento y deuda visual

Ya se aplicó carga local con Vite, carga diferida de Chart.js, atlases WebP, variantes responsive, `requestAnimationFrame` para escenas activas y reducción de trabajo en pestañas ocultas. El build actual produce aproximadamente 171 kB CSS y 54 kB JS principales antes de compresión, además de un chunk automático de Alpine/terceros.

Pendientes de segunda fase:

- Extraer los ocho bloques de JavaScript inline a módulos por juego sin alterar contratos de backend.
- Unificar `game-header` y `game-result` en las ocho vistas.
- Generar métricas reales con Lighthouse/DevTools en móvil de gama baja.
- Auditar y convertir progresivamente los PNG restantes; no eliminar duplicados sin confirmar referencias.
- Añadir pruebas de screenshot y una matriz de navegadores.
- Evaluar una política de calidad baja/equilibrada/alta para Canvas y partículas.

## 9. Criterios de aceptación

Una mejora visual se considera validada cuando:

- El resultado mostrado coincide con el JSON real del servidor.
- No cambia la apuesta, el saldo, el pago ni el orden de las acciones válidas.
- Funciona con sonido activado y silenciado.
- Funciona con `prefers-reduced-motion`.
- No deja temporizadores, listeners o partículas acumuladas tras cambiar de ronda.
- Mantiene legibles saldo, apuesta, premio bruto, resultado neto e historial.
- Pasa `php artisan view:cache`, `npm run build`, lint PHP y las pruebas frontend disponibles.
