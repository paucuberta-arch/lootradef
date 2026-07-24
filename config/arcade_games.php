<?php

$game = static fn (string $slug, string $name, string $cat, string $mode, string $image, string $description, string $badge = '') => [
    'slug' => $slug, 'name' => $name, 'provider' => 'Lootra Originals', 'cat' => $cat, 'mode' => $mode,
    'grad' => 'game-gradient-7', 'badge' => $badge, 'badgeColor' => 'bg-fuchsia-500/90 text-white',
    'rtp' => '96.0%', 'min' => '0,20 EUR Demo', 'max' => '500 EUR Demo', 'image' => $image,
    'volatilidad' => 'Variable', 'max_win' => 'x25', 'min_bet' => '0,20 EUR Demo', 'max_bet' => '500 EUR Demo',
    'lines' => '-', 'reels' => '-', 'description' => $description,
];

return [
    'crazy-time' => array_replace($game('crazy-time', 'Crazy Time Neon', 'live', 'wheel', '/images/lootra_visual_pack/03_game_covers/game_crazy_time_neon_800x1000.webp', 'Una rueda física de multiplicadores con segmentos sorpresa y premios instantáneos.', 'Live'), ['rtp' => '95.0%', 'max_win' => 'x3']),
    'texas-holdem' => array_replace($game('texas-holdem', 'Poker All-In', 'poker', 'poker', '/images/lootra_visual_pack/03_game_covers/game_poker_all_in_800x1000.webp', 'Una mano completa e instantánea: elige el importe, ve all-in y descubre el showdown.', 'Rápido'), ['rtp' => '96.0%', 'max_win' => 'x1.92']),
    'dealer-poker' => array_replace($game('dealer-poker', "Texas Hold'em contra el Dealer", 'poker', 'poker_dealer', '/images/lootra_visual_pack/03_game_covers/game_poker_all_in_800x1000.webp', 'Juega una partida por fases contra el dealer: preflop, flop, turn, river y showdown.', 'Mesa real'), ['rtp' => '96.0%', 'max_win' => 'x1.92']),
    'neon-mines' => array_replace($game('neon-mines', 'Neon Mines', 'arcade', 'mines', '/images/lootra_visual_pack/04_backgrounds/bg_cyber_city_rain_1920x1080.webp', 'Escanea una cuadrícula de energía y evita las minas para multiplicar la apuesta.'), ['rtp' => '96.0%']),
    'dice-arena' => array_replace($game('dice-arena', 'Dice Arena', 'arcade', 'dice', '/images/lootra_visual_pack/04_backgrounds/bg_crimson_realm_1920x1080.webp', 'Predice si la tirada será alta o baja y ajusta riesgo y recompensa.'), ['rtp' => '96.0%', 'max_win' => 'x2.1333']),
    'high-low' => array_replace($game('high-low', 'Higher or Lower', 'cartas', 'hilo', '/images/lootra_visual_pack/03_game_covers/game_blackjack_classic_800x1000.webp', 'Lee la carta visible y decide si la siguiente será mayor o menor.'), ['rtp' => '96.0%']),
    'quantum-plinko' => array_replace(
        $game('quantum-plinko', 'Quantum Plinko', 'arcade', 'plinko', '/images/lootra_visual_pack/03_game_covers/game_quantum_plinko_800x1000.webp', 'Lanza una esfera de energía a través de un tablero de probabilidades.', 'Nuevo'),
        ['rtp' => '96.4%', 'max_win' => 'x12']
    ),
    'cosmic-keno' => array_replace($game('cosmic-keno', 'Cosmic Keno', 'numeros', 'keno', '/images/lootra_visual_pack/04_backgrounds/bg_cosmic_planet_1920x1080.webp', 'Elige cinco constelaciones y busca coincidencias en el sorteo galáctico.'), ['rtp' => '96.0%', 'max_win' => 'x18.46']),
    'coin-duel' => array_replace($game('coin-duel', 'Coin Duel', 'arcade', 'coin', '/images/lootra_visual_pack/04_backgrounds/bg_ember_fortress_1920x1080.webp', 'Un duelo instantáneo de cara o cruz con presentación holográfica.'), ['rtp' => '97.5%', 'max_win' => 'x1.95']),
    'baccarat-royale' => array_replace($game('baccarat-royale', 'Baccarat Royale', 'cartas', 'baccarat', '/images/lootra_visual_pack/03_game_covers/game_blackjack_vip_800x1000.webp', 'Apuesta por jugador, banca o empate en el clásico juego de nueve puntos.'), ['rtp' => '96.0%', 'max_win' => 'x9.6']),
    'nebula-picks' => array_replace($game('nebula-picks', 'Nebula Picks', 'arcade', 'nebula', '/images/lootra_visual_pack/04_backgrounds/bg_neon_city_night_1920x1080.webp', 'Elige un cristal cósmico; cada color oculta una distribución de premios distinta.', 'Original'), ['rtp' => '96.0%', 'max_win' => 'x10']),
];
