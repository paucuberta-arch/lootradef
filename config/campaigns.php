<?php

return [
    'rickyedit' => [
        'enabled' => env('RICKYEDIT_CAMPAIGN_ENABLED', false),
        'official_assets_enabled' => env('RICKYEDIT_OFFICIAL_ASSETS_ENABLED', false),
        'initial_balance' => (float) env('RICKYEDIT_INITIAL_BALANCE', 1000),
        'duration_minutes' => (int) env('RICKYEDIT_DURATION_MINUTES', 15),
        'creator_score' => (int) env('RICKYEDIT_CREATOR_SCORE', 1250),
        'start_at' => env('RICKYEDIT_START_AT'),
        'end_at' => env('RICKYEDIT_END_AT'),
        'youtube_url' => env('RICKYEDIT_YOUTUBE_URL'),
        'creator_code' => 'rickyedit',
        'allowed_games' => [
            'slots', 'ruleta_european', 'ruleta_lightning',
            'blackjack_vip', 'blackjack_classic', 'crash', 'poker_dealer',
            'crazy-time', 'texas-holdem', 'neon-mines', 'dice-arena',
            'high-low', 'quantum-plinko', 'cosmic-keno', 'coin-duel',
            'baccarat-royale', 'nebula-picks',
        ],
        'assets' => [
            'hero' => 'hero.webp',
            'portrait' => 'portrait.webp',
            'logo' => 'logo-collab.svg',
            'video_poster' => 'video-poster.webp',
            'badge' => 'badge.svg',
            'share_card' => 'share-card.webp',
            'challenge_hero' => 'challenge-hero-v2.webp',
            'creator_portrait' => 'ricky_edit2-removebg-preview.webp',
            'home_hero' => 'aifaceswap-390bed6ee726c1170c6d1df14ca5d5b4.webp',
            'home_mobile' => 'aifaceswap-a2a4b24b66d4ab9e64b25f2d6df8e767.webp',
            'home_card' => 'aifaceswap-b014c556f19b1e3ed6bac071a497552a.webp',
        ],
    ],
];
