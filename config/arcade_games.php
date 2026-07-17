<?php

$game = static fn (string $slug, string $name, string $cat, string $mode, string $image, string $description, string $badge = '') => [
    'slug' => $slug, 'name' => $name, 'provider' => 'Lootra Originals', 'cat' => $cat, 'mode' => $mode,
    'grad' => 'game-gradient-7', 'badge' => $badge, 'badgeColor' => 'bg-fuchsia-500/90 text-white',
    'rtp' => '96.0%', 'min' => '€0.20', 'max' => '€500', 'image' => $image,
    'volatilidad' => 'Variable', 'max_win' => 'x25', 'min_bet' => '€0.20', 'max_bet' => '€500',
    'lines' => '-', 'reels' => '-', 'description' => $description,
];

return [
    'crazy-time' => $game('crazy-time', 'Crazy Time Neon', 'live', 'wheel', 'https://images.unsplash.com/photo-1596838132731-3301c3fd4317?auto=format&fit=crop&w=900&q=85', 'Una rueda física de multiplicadores con segmentos sorpresa y premios instantáneos.', 'Live'),
    'texas-holdem' => $game('texas-holdem', 'Poker All-In', 'poker', 'poker', 'https://images.unsplash.com/photo-1541278107931-e006523892df?auto=format&fit=crop&w=900&q=85', 'Una mano completa e instantánea: elige el importe, ve all-in y descubre el showdown.', 'Rápido'),
    'dealer-poker' => $game('dealer-poker', "Texas Hold'em contra el Dealer", 'poker', 'poker_dealer', 'https://images.unsplash.com/photo-1511193311914-0346f16efe90?auto=format&fit=crop&w=900&q=85', 'Juega una partida por fases contra el dealer: preflop, flop, turn, river y showdown.', 'Mesa real'),
    'neon-mines' => $game('neon-mines', 'Neon Mines', 'arcade', 'mines', 'https://images.unsplash.com/photo-1518709268805-4e9042af9f23?auto=format&fit=crop&w=900&q=85', 'Escanea una cuadrícula de energía y evita las minas para multiplicar la apuesta.'),
    'dice-arena' => $game('dice-arena', 'Dice Arena', 'arcade', 'dice', 'https://images.unsplash.com/photo-1551431009-a802eeec77b1?auto=format&fit=crop&w=900&q=85', 'Predice si la tirada será alta o baja y ajusta riesgo y recompensa.'),
    'high-low' => $game('high-low', 'Higher or Lower', 'cartas', 'hilo', 'https://images.unsplash.com/photo-1529480780361-c8cb81eb5735?auto=format&fit=crop&w=900&q=85', 'Lee la carta visible y decide si la siguiente será mayor o menor.'),
    'quantum-plinko' => array_replace(
        $game('quantum-plinko', 'Quantum Plinko', 'arcade', 'plinko', 'https://images.unsplash.com/photo-1635070041078-e363dbe005cb?auto=format&fit=crop&w=900&q=85', 'Lanza una esfera de energía a través de un tablero de probabilidades.', 'Nuevo'),
        ['rtp' => '96.4%', 'max_win' => 'x12']
    ),
    'cosmic-keno' => $game('cosmic-keno', 'Cosmic Keno', 'numeros', 'keno', 'https://images.unsplash.com/photo-1446776877081-d282a0f896e2?auto=format&fit=crop&w=900&q=85', 'Elige cinco constelaciones y busca coincidencias en el sorteo galáctico.'),
    'coin-duel' => $game('coin-duel', 'Coin Duel', 'arcade', 'coin', 'https://images.unsplash.com/photo-1621761191319-c6fb62004040?auto=format&fit=crop&w=900&q=85', 'Un duelo instantáneo de cara o cruz con presentación holográfica.'),
    'baccarat-royale' => $game('baccarat-royale', 'Baccarat Royale', 'cartas', 'baccarat', 'https://images.unsplash.com/photo-1511193311914-0346f16efe90?auto=format&fit=crop&w=900&q=85', 'Apuesta por jugador, banca o empate en el clásico juego de nueve puntos.'),
    'nebula-picks' => $game('nebula-picks', 'Nebula Picks', 'arcade', 'nebula', 'https://images.unsplash.com/photo-1462331940025-496dfbfc7564?auto=format&fit=crop&w=900&q=85', 'Elige un cristal cósmico; cada color oculta una distribución de premios distinta.', 'Original'),
];
