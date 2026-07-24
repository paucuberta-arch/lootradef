<?php

return [
    'crash' => [
        // The tail is deliberately bounded so the configured bet limit has a
        // finite, auditable maximum exposure in the demo economy.
        'max_multiplier' => (float) env('CRASH_MAX_MULTIPLIER', 1000),
        'house_edge' => 0.03,
    ],
    'versions' => [
        'default' => '2026-07-24-catalog-v1',
        'cosmic_keno' => '2026-07-24-96pct-v2',
        'crash' => '2026-07-24-capped-tail-v1',
        'blackjack_vip' => '2026-07-24-unrated-v1',
        'blackjack_classic' => '2026-07-24-unrated-v1',
    ],
];
