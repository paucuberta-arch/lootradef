# Guía de demostración de Lootra

## Mensaje inicial

Empieza indicando que Lootra es una simulación empresarial: no utiliza dinero
real y los créditos no tienen valor económico ni pueden canjearse.

## Recorrido recomendado

1. Abre la portada y muestra el aviso permanente de simulación.
2. Entra en el catálogo y filtra por una categoría.
3. Abre una ficha y consulta reglas, RTP informativo y límites.
4. Registra un usuario local o inicia sesión con una cuenta de prueba creada
   fuera del repositorio.
5. Ejecuta una ronda de Slots, Ruleta o un Lootra Original.
6. Muestra el saldo antes y después, el resultado y el historial.
7. Enseña cajas e inventario: los premios son cosméticos virtuales y no generan
   saldo ni valor económico.
8. Abre el perfil para mostrar actividad y preferencias.
9. Si el entorno demo está habilitado, muestra un depósito o retirada ficticios
   y su estado en el ledger.
10. Entra al panel con una cuenta administrativa local protegida por MFA y
    muestra métricas, permisos, auditoría y tesorería demo.
11. Ejecuta una simulación reproducible desde CLI y compara el RTP observado
    con el escenario configurado.

## Preparación

Usa una base local aislada, datos sintéticos y flags demo explícitas. No uses
credenciales, correos, nombres ni saldos de usuarios reales. Las pruebas E2E
leen sus credenciales desde `E2E_EMAIL` y `E2E_PASSWORD`; nunca las escribas en
el repositorio.

## Cuentas y orden recomendado

- **Usuario demo:** crea una cuenta local con un correo `.test`, verifica el
  correo mediante el mecanismo de testing y dale saldo sintético solo en la
  base de demostración.
- **Administrador demo:** crea una segunda cuenta local, asígnale el rol
  administrativo y habilita MFA antes de abrir el panel. No reutilices la
  cuenta de usuario.
- **Juegos recomendados:** Gates of Olympus para mostrar una ronda rápida,
  Ruleta Europea para enseñar resultado visible y Quantum Plinko para mostrar
  una animación original con trayectoria validada por servidor.
- **Funciones destacadas:** catálogo compartido, resultado server-side,
  idempotencia económica, ledger de doble entrada, tesorería demo y auditoría
  administrativa.

El panel administrativo disponible en esta versión cubre dashboard, usuarios,
roles, MFA, reviews, feedback, cajas, campaña, gráficos, logs y retiradas demo.
No deben anunciarse como disponibles módulos de gestión de juegos, retos,
alertas o configuración global porque no forman parte del alcance implementado.

## Qué no debe afirmarse

Lootra no es un casino operativo, no procesa pagos y no ofrece ganancias.
Las métricas y simulaciones sirven para explicar el diseño técnico y económico;
no son una recomendación financiera ni una garantía de sostenibilidad.
