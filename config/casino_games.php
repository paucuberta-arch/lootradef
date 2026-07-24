<?php

$game = static fn (
    string $slug,
    string $name,
    string $provider,
    string $category,
    string $image,
    string $description,
    string $routeName,
    array $routeParameters,
    string $rtp,
    string $volatility,
    string $maxWin,
    string $minBet,
    string $maxBet,
    string $lines = '-',
    string $reels = '-',
    string $gradient = 'game-gradient-7',
    string $badge = '',
) => [
    'slug' => $slug,
    'name' => $name,
    'provider' => $provider,
    'cat' => $category,
    'category' => $category,
    'image' => $image,
    'description' => $description,
    'route_name' => $routeName,
    'route_parameters' => $routeParameters,
    'rtp' => $rtp,
    'volatilidad' => $volatility,
    'max_win' => $maxWin,
    'min_bet' => $minBet,
    'max_bet' => $maxBet,
    'min' => $minBet,
    'max' => $maxBet,
    'lines' => $lines,
    'reels' => $reels,
    'grad' => $gradient,
    'badge' => $badge,
    'badgeColor' => 'bg-brand-500/90 text-black',
    'status' => 'active',
];

$games = [
    'gates-of-olympus' => $game('gates-of-olympus', 'Gates of Olympus', 'Pragmatic Play', 'slots', '/images/lootra_visual_pack/03_game_covers/game_gates_of_olympus_800x1000.webp', 'Viaja al Monte del Olimpo con Zeus en esta slot de multiplicadores y premios por combinaciones.', 'games.slots.show', ['slug' => 'gates-of-olympus'], '95.17%', 'Alta', 'x5000', '0,20 EUR Demo', '125 EUR Demo', '20', '3', 'game-gradient-5', 'Popular'),
    'sweet-bonanza' => $game('sweet-bonanza', 'Sweet Bonanza', 'Pragmatic Play', 'slots', '/images/lootra_visual_pack/03_game_covers/game_sweet_bonanza_800x1000.webp', 'Un mundo de dulces y frutas con una presentación vibrante y premios por combinaciones.', 'games.slots.show', ['slug' => 'sweet-bonanza'], '94.84%', 'Alta', 'x21175', '0,20 EUR Demo', '100 EUR Demo', 'Pay Anywhere', '3', 'game-gradient-3'),
    'book-of-dead' => $game('book-of-dead', 'Book of Dead', "Play'n GO", 'slots', '/images/lootra_visual_pack/03_game_covers/game_book_of_dead_800x1000.webp', 'Acompaña a Rich Wilde en una aventura inspirada en el antiguo Egipto.', 'games.slots.show', ['slug' => 'book-of-dead'], '96.79%', 'Alta', 'x5000', '0,10 EUR Demo', '100 EUR Demo', '10', '3', 'game-gradient-1'),
    'starburst' => $game('starburst', 'Starburst', 'NetEnt', 'slots', '/images/lootra_visual_pack/03_game_covers/game_starburst_800x1000.webp', 'Una slot de gemas, luces y wilds con ritmo rápido.', 'games.slots.show', ['slug' => 'starburst'], '96.09%', 'Baja', 'x500', '0,10 EUR Demo', '100 EUR Demo', '10', '3', 'game-gradient-7', 'Clásico'),
    'big-bass-bonanza' => $game('big-bass-bonanza', 'Big Bass Bonanza', 'Pragmatic Play', 'slots', '/images/lootra_visual_pack/03_game_covers/game_big_bass_bonanza_800x1000.webp', 'Una jornada de pesca con peces, aparejos y premios acuáticos.', 'games.slots.show', ['slug' => 'big-bass-bonanza'], '96.09%', 'Alta', 'x2100', '0,10 EUR Demo', '250 EUR Demo', '10', '3', 'game-gradient-9'),
    'european-roulette' => $game('european-roulette', 'European Roulette', 'NetEnt', 'ruleta', '/images/lootra_visual_pack/03_game_covers/game_european_roulette_800x1000.webp', 'Ruleta europea de un solo cero con apuestas interiores y exteriores.', 'games.roulette.european', [], '97.3%', 'Variable', 'x35', '0,10 EUR Demo', '500 EUR Demo', '-', '-', 'game-gradient-11'),
    'lightning-roulette' => $game('lightning-roulette', 'Lightning Roulette', 'Evolution', 'ruleta', '/images/lootra_visual_pack/03_game_covers/game_lightning_roulette_800x1000.webp', 'Una variante eléctrica con números multiplicadores en cada ronda.', 'games.roulette.lightning', [], '97.29%', 'Media', 'x500', '0,20 EUR Demo', '500 EUR Demo', '-', '-', 'game-gradient-12'),
    'blackjack-vip' => $game('blackjack-vip', 'Blackjack VIP', 'Evolution', 'blackjack', '/images/lootra_visual_pack/03_game_covers/game_blackjack_vip_800x1000.webp', 'Mesa VIP con límites altos y reglas clásicas de blackjack.', 'games.blackjack.vip', [], 'Variable', 'Baja', 'x2.4', '5 EUR Demo', '5000 EUR Demo', '-', '-', 'game-gradient-4', 'VIP'),
    'blackjack-classic' => $game('blackjack-classic', 'Blackjack Classic', 'Microgaming', 'blackjack', '/images/lootra_visual_pack/03_game_covers/game_blackjack_classic_800x1000.webp', 'Blackjack clásico accesible con seis barajas y dealer plantado en 17.', 'games.blackjack.classic', [], 'Variable', 'Baja', 'x2.4', '1 EUR Demo', '2000 EUR Demo', '-', '-', 'game-gradient-10'),
    'crash-rocket' => $game('crash-rocket', 'Crash Rocket', 'Spribe', 'crash', '/images/lootra_visual_pack/03_game_covers/game_crash_rocket_800x1000.webp', 'Cobra antes de que el cohete explote mientras aumenta el multiplicador.', 'games.crash.show', [], '97.0%', 'Alta', 'x1000', '0,10 EUR Demo', '200 EUR Demo', '-', '-', 'game-gradient-8', 'Turbo'),
];

foreach (require __DIR__.'/arcade_games.php' as $slug => $original) {
    $original['category'] = $original['cat'];
    $original['route_name'] = $slug === 'dealer-poker' ? 'games.poker.dealer' : ($slug === 'texas-holdem' ? 'games.poker.all-in' : 'games.originals.show');
    $original['route_parameters'] = str_starts_with($original['route_name'], 'games.originals.') ? ['game' => $slug] : [];
    $original['status'] = 'active';
    $games[$slug] = $original;
}

return $games;
