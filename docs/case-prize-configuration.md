# Configuración de premios de cajas

## Panel

Ruta: `/admin/premios-cajas`.

Requiere rol `super_admin` o `admin` y permiso `case-prizes.manage`. El panel permite:

- Cambiar el porcentaje de cada premio con cuatro decimales.
- Marcar qué premios se consideran buenos.
- Establecer un máximo diario de premios buenos por caja; vacío significa sin límite y cero bloquea todos.
- Consultar cuántos premios buenos se han entregado durante el día actual.
- Crear multiplicadores temporales únicamente para cuentas `demo`, `test` o `simulated`.

Los porcentajes de cada caja deben sumar exactamente 100%. Si se alcanza el cupo, se excluyen los premios buenos y las probabilidades positivas restantes se normalizan durante la selección.

## Integridad y seguridad

- La selección usa `random_int` en el servidor.
- La configuración, el contador diario y la cartera se bloquean dentro de la misma transacción.
- Los cambios administrativos y multiplicadores se registran en `activity_logs`.
- Los multiplicadores individuales nunca se aplican a usuarios reales, aunque exista un registro insertado fuera del panel.
- Los administradores no pueden ser objetivos de multiplicadores.
- Las rutas mutables tienen CSRF, permiso dedicado y rate limiting.

## Tablas

- `case_reward_settings`: cupo diario por caja.
- `case_prize_rules`: porcentaje y clasificación de cada premio.
- `case_reward_daily_stats`: contador diario protegido por restricción única.
- `case_demo_prize_boosts`: multiplicadores auditables para pruebas.

Migración reversible:

```bash
php artisan migrate
```

Para actualizar permisos:

```bash
php artisan db:seed --class=RolesPermissionsSeeder
```

## Pruebas

```bash
php artisan test --filter=CasePrizeAdministrationTest
php artisan test --filter=CajaInventoryTest
```
