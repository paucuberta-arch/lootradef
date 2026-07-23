<?php

$trustedProxies = array_values(array_filter(array_map(
    'trim',
    explode(',', (string) env('TRUSTED_PROXIES', '')),
)));
$applicationHost = parse_url((string) env('APP_URL', 'http://localhost'), PHP_URL_HOST) ?: 'localhost';
$trustedHosts = array_values(array_filter(array_map(
    'trim',
    explode(',', (string) env('TRUSTED_HOSTS', $applicationHost)),
)));

return [
    'admin_mfa_required' => (bool) env('ADMIN_MFA_REQUIRED', env('APP_ENV') === 'production'),
    'trusted_proxies' => $trustedProxies,
    'trusted_hosts' => $trustedHosts,
];
