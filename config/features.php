<?php

return [
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
