<?php

return [
    'economy' => [
        // The application is a demo by default outside production. Production
        // must opt in explicitly as a demo-only environment.
        'enabled' => (bool) env('DEMO_ECONOMY_ENABLED', in_array((string) env('APP_ENV', 'local'), ['local', 'testing'], true)),
        'business_simulation' => (bool) env('BUSINESS_SIMULATION_MODE', false),
        'demo_only_environment' => (bool) env('DEMO_ONLY_ENVIRONMENT', false),
        'currency_code' => (string) env('VIRTUAL_CURRENCY_CODE', 'EUR_DEMO'),
        'currency_label' => (string) env('VIRTUAL_CURRENCY_LABEL', 'EUR Demo'),
        'real_value_redemption_enabled' => false,
        'treasury_initial_balance' => (float) env('DEMO_TREASURY_INITIAL_BALANCE', 100000),
        'withdrawal_minimum' => (float) env('DEMO_WITHDRAWAL_MINIMUM', 1),
        'withdrawal_maximum' => (float) env('DEMO_WITHDRAWAL_MAXIMUM', 5000),
    ],

    'registration_bonus' => (float) env('REGISTRATION_BONUS', 1000),

    'demo_deposits' => [
        'enabled' => (bool) env('DEMO_DEPOSITS_ENABLED', false),
        'max_balance' => (float) env('DEMO_DEPOSITS_MAX_BALANCE', 50000),
    ],

    'require_verified_for_play' => (bool) env(
        'REQUIRE_VERIFIED_FOR_PLAY',
        env('APP_ENV') === 'production'
    ),
];
