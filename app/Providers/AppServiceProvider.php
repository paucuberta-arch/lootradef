<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use RuntimeException;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if (! app()->environment('production')) {
            return;
        }

        $errors = [];
        if (config('app.debug')) {
            $errors[] = 'APP_DEBUG debe estar desactivado';
        }
        $appKey = (string) config('app.key');
        $decodedKey = str_starts_with($appKey, 'base64:')
            ? base64_decode(substr($appKey, 7), true)
            : false;
        if (! is_string($decodedKey) || strlen($decodedKey) < 32) {
            $errors[] = 'APP_KEY debe ser una clave Laravel base64 válida';
        }
        if (! str_starts_with((string) config('app.url'), 'https://')) {
            $errors[] = 'APP_URL debe usar HTTPS';
        }
        if (! config('session.secure')) {
            $errors[] = 'SESSION_SECURE_COOKIE debe estar activado';
        }
        if (! config('security.admin_mfa_required')) {
            $errors[] = 'ADMIN_MFA_REQUIRED debe estar activado';
        }
        if (config('features.demo_deposits.enabled')) {
            $errors[] = 'Los depósitos demo deben permanecer desactivados';
        }
        if (config('features.economy.enabled') && ! config('features.economy.demo_only_environment')) {
            $errors[] = 'La economía demo en producción requiere DEMO_ONLY_ENVIRONMENT=true';
        }
        if (config('features.economy.real_value_redemption_enabled')) {
            $errors[] = 'Nunca se permite canjear créditos demo por valor real';
        }
        if (config('session.driver') === 'file') {
            $errors[] = 'SESSION_DRIVER debe usar un almacenamiento compartido en producción';
        }
        if (config('cache.default') === 'file') {
            $errors[] = 'CACHE_DRIVER debe usar Redis u otro backend compartido en producción';
        }
        if (config('queue.default') === 'sync') {
            $errors[] = 'QUEUE_CONNECTION no debe ser sync en producción';
        }
        if (! config('database.connections.'.config('database.default').'.host')) {
            $errors[] = 'La conexión de base de datos debe definir DB_HOST';
        }
        $databaseConnection = config('database.connections.'.config('database.default'));
        $databaseHost = (string) ($databaseConnection['host'] ?? '');
        $mysqlSslCa = defined('PDO::MYSQL_ATTR_SSL_CA')
            ? config('database.connections.mysql.options.'.\PDO::MYSQL_ATTR_SSL_CA)
            : null;
        if (($databaseConnection['driver'] ?? null) === 'mysql'
            && ! in_array($databaseHost, ['127.0.0.1', 'localhost'], true)
            && ! $mysqlSslCa) {
            $errors[] = 'MYSQL_ATTR_SSL_CA debe estar configurado para MySQL remoto';
        }
        if (config('mail.default') === 'smtp' && ! config('mail.mailers.smtp.host')) {
            $errors[] = 'MAIL_HOST debe estar configurado en producción';
        }

        if ($errors !== []) {
            throw new RuntimeException('Configuración de producción insegura: '.implode('; ', $errors).'.');
        }
    }
}
