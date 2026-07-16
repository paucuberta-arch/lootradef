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
    'gates-of-olympus' => $game('gates-of-olympus', 'Gates of Olympus', 'Pragmatic Play', 'slots', 'https://images.unsplash.com/photo-1551524559-8af4e6624178?w=800&h=600&fit=crop', 'Viaja al Monte del Olimpo con Zeus en esta slot de multiplicadores y premios por combinaciones.', 'games.slots.show', ['slug' => 'gates-of-olympus'], '96.5%', 'Alta', 'x5000', '€0.20', '€125', '20', '3', 'game-gradient-5', 'Popular'),
    'sweet-bonanza' => $game('sweet-bonanza', 'Sweet Bonanza', 'Pragmatic Play', 'slots', 'https://images.unsplash.com/photo-1575224300306-1b8da36134ec?auto=format&fit=crop&w=1200&h=700&q=90', 'Un mundo de dulces y frutas con una presentación vibrante y premios por combinaciones.', 'games.slots.show', ['slug' => 'sweet-bonanza'], '96.48%', 'Alta', 'x21175', '€0.20', '€100', 'Pay Anywhere', '3', 'game-gradient-3'),
    'book-of-dead' => $game('book-of-dead', 'Book of Dead', "Play'n GO", 'slots', 'https://images.unsplash.com/photo-1539768942893-daf53e736b68?w=800&h=600&fit=crop', 'Acompaña a Rich Wilde en una aventura inspirada en el antiguo Egipto.', 'games.slots.show', ['slug' => 'book-of-dead'], '96.21%', 'Alta', 'x5000', '€0.10', '€100', '10', '3', 'game-gradient-1'),
    'starburst' => $game('starburst', 'Starburst', 'NetEnt', 'slots', 'https://images.unsplash.com/photo-1462331940025-496dfbfc7564?w=800&h=600&fit=crop', 'Una slot de gemas, luces y wilds con ritmo rápido.', 'games.slots.show', ['slug' => 'starburst'], '96.09%', 'Baja', 'x500', '€0.10', '€100', '10', '3', 'game-gradient-7', 'Clásico'),
    'big-bass-bonanza' => $game('big-bass-bonanza', 'Big Bass Bonanza', 'Pragmatic Play', 'slots', 'https://images.unsplash.com/photo-1544551763-77ef2d0cfc6c?auto=format&fit=crop&w=1200&h=700&q=90', 'Una jornada de pesca con peces, aparejos y premios acuáticos.', 'games.slots.show', ['slug' => 'big-bass-bonanza'], '96.71%', 'Alta', 'x2100', '€0.10', '€250', '10', '3', 'game-gradient-9'),
    'european-roulette' => $game('european-roulette', 'European Roulette', 'NetEnt', 'ruleta', 'https://images.unsplash.com/photo-1517232115160-ff93364542dd?w=800&h=600&fit=crop', 'Ruleta europea de un solo cero con apuestas interiores y exteriores.', 'games.roulette.european', [], '97.3%', 'Variable', 'x35', '€0.10', '€500', '-', '-', 'game-gradient-11'),
    'lightning-roulette' => $game('lightning-roulette', 'Lightning Roulette', 'Evolution', 'ruleta', 'https://images.unsplash.com/photo-1507400492013-162706c8c05e?w=800&h=600&fit=crop', 'Una variante eléctrica con números multiplicadores en cada ronda.', 'games.roulette.lightning', [], '97.3%', 'Media', 'x500', '€0.20', '€500', '-', '-', 'game-gradient-12'),
    'blackjack-vip' => $game('blackjack-vip', 'Blackjack VIP', 'Evolution', 'blackjack', 'https://images.unsplash.com/photo-1541278107931-e006523892df?w=800&h=600&fit=crop', 'Mesa VIP con límites altos y reglas clásicas de blackjack.', 'games.blackjack.vip', [], '99.28%', 'Baja', 'x3', '€5', '€5000', '-', '-', 'game-gradient-4', 'VIP'),
    'blackjack-classic' => $game('blackjack-classic', 'Blackjack Classic', 'Microgaming', 'blackjack', 'https://images.unsplash.com/photo-1560015534-cee980ba7e13?w=800&h=600&fit=crop', 'Blackjack clásico accesible con seis barajas y dealer plantado en 17.', 'games.blackjack.classic', [], '99.91%', 'Baja', 'x3', '€1', '€2000', '-', '-', 'game-gradient-10'),
    'crash-rocket' => $game('crash-rocket', 'Crash Rocket', 'Spribe', 'crash', 'https://images.unsplash.com/photo-1516849841032-87cbdec47910?w=800&h=600&fit=crop', 'Cobra antes de que el cohete explote mientras aumenta el multiplicador.', 'games.crash.show', [], '97.0%', 'Alta', 'x∞', '€0.10', '€200', '-', '-', 'game-gradient-8', 'Turbo'),
];

foreach (require __DIR__.'/arcade_games.php' as $slug => $original) {
    $original['category'] = $original['cat'];
    $original['route_name'] = $slug === 'dealer-poker' ? 'games.poker.dealer' : ($slug === 'texas-holdem' ? 'games.poker.all-in' : 'games.originals.show');
    $original['route_parameters'] = str_starts_with($original['route_name'], 'games.originals.') ? ['game' => $slug] : [];
    $original['status'] = 'active';
    $games[$slug] = $original;
}

return $games;
