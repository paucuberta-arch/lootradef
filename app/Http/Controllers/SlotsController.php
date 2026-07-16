<?php

namespace App\Http\Controllers;

use App\Models\Cartera;
use App\Models\Partida;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class SlotsController extends Controller
{
    private array $themes = [
        'default' => [
            'name' => 'Slots',
            'emoji' => '🎰',
            'tagline' => 'Gira los carretes y gana premios',
            'provider' => 'Lootra',
            'symbols' => ['🍒', '🍋', '🍊', '🍇', '💎', '⭐', '7️⃣', '🔔'],
            'weights' => ['🍒' => 25, '🍋' => 25, '🍊' => 20, '🍇' => 15, '💎' => 8, '⭐' => 5, '7️⃣' => 1, '🔔' => 1],
            'paytable' => [
                ['symbols' => '7️⃣7️⃣7️⃣', 'label' => 'x50'],
                ['symbols' => '💎💎💎', 'label' => 'x25'],
                ['symbols' => '🔔🔔🔔', 'label' => 'x20'],
                ['symbols' => '⭐⭐⭐', 'label' => 'x15'],
                ['symbols' => '🍒🍒🍒', 'label' => 'x10'],
                ['symbols' => 'X X X', 'label' => 'x2 (pareja)'],
            ],
        ],
        'gates-of-olympus' => [
            'name' => 'Gates of Olympus',
            'emoji' => '⚡',
            'tagline' => 'Viaja al Monte del Olimpo con Zeus',
            'provider' => 'Pragmatic Play',
            'atlas' => 'images/slots/olympus-symbols-v2.png',
            'hero' => 'https://images.unsplash.com/photo-1603565816030-6b389eeb23cb?auto=format&fit=crop&w=1400&q=85',
            'symbols' => ['👑', '⚡', '💎', '🏆', '🏺', '🪙', '🔴', '🔵'],
            'weights' => ['👑' => 5, '⚡' => 8, '💎' => 10, '🏆' => 12, '🏺' => 18, '🪙' => 20, '🔴' => 25, '🔵' => 25],
            'paytable' => [
                ['symbols' => '👑👑👑', 'label' => 'x50'],
                ['symbols' => '⚡⚡⚡', 'label' => 'x25'],
                ['symbols' => '💎💎💎', 'label' => 'x20'],
                ['symbols' => '🏆🏆🏆', 'label' => 'x15'],
                ['symbols' => '🏺🏺🏺', 'label' => 'x10'],
                ['symbols' => 'X X X', 'label' => 'x2 (pareja)'],
            ],
        ],
        'sweet-bonanza' => [
            'name' => 'Sweet Bonanza',
            'emoji' => '🍬',
            'tagline' => 'Un mundo de dulces y frutas te espera',
            'provider' => 'Pragmatic Play',
            'atlas' => 'images/slots/sweet-symbols-v2.png',
            'hero' => 'https://images.unsplash.com/photo-1575224300306-1b8da36134ec?auto=format&fit=crop&w=1400&q=85',
            'symbols' => ['🍭', '🍫', '🍬', '🍰', '🍩', '🍒', '🫐', '🟣'],
            'weights' => ['🍭' => 8, '🍫' => 10, '🍬' => 15, '🍰' => 15, '🍩' => 20, '🍒' => 22, '🫐' => 25, '🟣' => 25],
            'paytable' => [
                ['symbols' => '🍭🍭🍭', 'label' => 'x50'],
                ['symbols' => '🍫🍫🍫', 'label' => 'x25'],
                ['symbols' => '🍬🍬🍬', 'label' => 'x20'],
                ['symbols' => '🍰🍰🍰', 'label' => 'x15'],
                ['symbols' => '🍩🍩🍩', 'label' => 'x10'],
                ['symbols' => 'X X X', 'label' => 'x2 (pareja)'],
            ],
        ],
        'book-of-dead' => [
            'name' => 'Book of Dead',
            'emoji' => '📖',
            'tagline' => 'Aventura por el antiguo Egipto con Rich Wilde',
            'provider' => "Play'n GO",
            'atlas' => 'images/slots/book-symbols-v2.png',
            'hero' => 'https://images.unsplash.com/photo-1539768942893-daf53e736b68?auto=format&fit=crop&w=1400&q=85',
            'symbols' => ['📖', '💀', '🧔', '🦅', '🏛️', '🃏', '🔟', '👑'],
            'weights' => ['📖' => 5, '💀' => 8, '🧔' => 10, '🦅' => 15, '🏛️' => 18, '🃏' => 20, '🔟' => 25, '👑' => 25],
            'paytable' => [
                ['symbols' => '📖📖📖', 'label' => 'x50'],
                ['symbols' => '💀💀💀', 'label' => 'x25'],
                ['symbols' => '🧔🧔🧔', 'label' => 'x20'],
                ['symbols' => '🦅🦅🦅', 'label' => 'x15'],
                ['symbols' => '🏛️🏛️🏛️', 'label' => 'x10'],
                ['symbols' => 'X X X', 'label' => 'x2 (pareja)'],
            ],
        ],
        'starburst' => [
            'name' => 'Starburst',
            'emoji' => '💫',
            'tagline' => 'La slot mas iconica de NetEnt',
            'provider' => 'NetEnt',
            'atlas' => 'images/slots/starburst-symbols-v2.png',
            'hero' => 'https://images.unsplash.com/photo-1462331940025-496dfbfc7564?auto=format&fit=crop&w=1400&q=85',
            'symbols' => ['💎', '⭐', '🌟', '✨', '🔵', '🟢', '🔴', '🟠'],
            'weights' => ['💎' => 5, '⭐' => 8, '🌟' => 12, '✨' => 15, '🔵' => 20, '🟢' => 22, '🔴' => 25, '🟠' => 25],
            'paytable' => [
                ['symbols' => '💎💎💎', 'label' => 'x50'],
                ['symbols' => '⭐⭐⭐', 'label' => 'x25'],
                ['symbols' => '🌟🌟🌟', 'label' => 'x20'],
                ['symbols' => '✨✨✨', 'label' => 'x15'],
                ['symbols' => '🔵🔵🔵', 'label' => 'x10'],
                ['symbols' => 'X X X', 'label' => 'x2 (pareja)'],
            ],
        ],
        'big-bass-bonanza' => [
            'name' => 'Big Bass Bonanza',
            'emoji' => '🐟',
            'tagline' => 'Salva de pesca en esta slot acuatica',
            'provider' => 'Pragmatic Play',
            'atlas' => 'images/slots/bass-symbols-v2.png',
            'hero' => 'https://images.unsplash.com/photo-1544551763-77ef2d0cfc6c?auto=format&fit=crop&w=1400&q=85',
            'symbols' => ['🐟', '🎣', '🪣', '🦞', '🐡', '🌊', '⚓', '🐠'],
            'weights' => ['🐟' => 5, '🎣' => 8, '🪣' => 12, '🦞' => 15, '🐡' => 20, '🌊' => 22, '⚓' => 25, '🐠' => 25],
            'paytable' => [
                ['symbols' => '🐟🐟🐟', 'label' => 'x50'],
                ['symbols' => '🎣🎣🎣', 'label' => 'x25'],
                ['symbols' => '🪣🪣🪣', 'label' => 'x20'],
                ['symbols' => '🦞🦞🦞', 'label' => 'x15'],
                ['symbols' => '🐡🐡🐡', 'label' => 'x10'],
                ['symbols' => 'X X X', 'label' => 'x2 (pareja)'],
            ],
        ],
    ];

    public function index(?string $slug = null)
    {
        $partidas = Partida::where('usuario_id', Auth::id())
            ->where('juego', 'slots')
            ->latest()
            ->take(10)
            ->get();

        $gameSlug = $slug ?? request()->query('game', 'default');
        $theme = $this->themes[$gameSlug] ?? $this->themes['default'];

        return view('games.slots', [
            'partidas' => $partidas,
            'gameSlug' => $gameSlug,
            'gameName' => $theme['name'],
            'gameEmoji' => $theme['emoji'],
            'gameTagline' => $theme['tagline'],
            'gameProvider' => $theme['provider'],
            'symbols' => $theme['symbols'],
            'paytable' => collect($theme['paytable'])->map(fn (array $row, int $index) => [
                ...$row,
                'icon' => $theme['symbols'][min($index, 4)],
            ])->all(),
            'symbolAtlas' => asset($theme['atlas'] ?? 'images/slots/olympus-symbols-v2.png'),
            'gameHero' => $theme['hero'] ?? null,
            'saldo' => Auth::user()?->cartera?->saldo ?? 0,
        ]);
    }

    public function play(Request $request, ?string $slug = null)
    {
        $request->validate([
            'apuesta' => 'required|numeric|min:0.10|max:500',
            'game' => 'nullable|string',
            'request_token' => 'required|uuid',
        ]);

        $user = Auth::user();
        $apuesta = round($request->apuesta, 2);
        $gameSlug = $slug ?? $request->input('game', 'default');
        $theme = $this->themes[$gameSlug] ?? $this->themes['default'];

        $round = DB::transaction(function () use ($request, $user, $apuesta, $gameSlug, $theme) {
            $wallet = Cartera::where('usuario_id', $user->id)->lockForUpdate()->first();
            $existing = Partida::where('usuario_id', $user->id)->where('juego', 'slots')
                ->where('request_token', $request->request_token)->lockForUpdate()->first();
            if ($existing) {
                return $existing;
            }
            abort_unless($wallet?->apostar($apuesta, 'apuesta_slots', ['juego' => $gameSlug]), 422, 'Saldo insuficiente.');
            $reels = [
                $this->spin($theme['symbols'], $theme['weights']),
                $this->spin($theme['symbols'], $theme['weights']),
                $this->spin($theme['symbols'], $theme['weights']),
            ];
            $ganancia = $this->calculateWin($reels, $apuesta, $gameSlug);
            if ($ganancia > 0) {
                $wallet->ganar($ganancia, 'premio_slots', ['juego' => $gameSlug]);
            }

            return Partida::create([
                'usuario_id' => $user->id, 'juego' => 'slots', 'request_token' => $request->request_token,
                'apuesta' => $apuesta, 'ganancia' => $ganancia,
                'detalles' => ['reels' => $reels, 'resultado' => $ganancia > 0 ? 'win' : 'lose', 'game' => $gameSlug],
            ]);
        });

        return response()->json([
            'reels' => $round->detalles['reels'],
            'ganancia' => (float) $round->ganancia,
            'resultado' => $round->detalles['resultado'],
            'saldo' => (float) $user->cartera()->value('saldo'),
        ]);
    }

    private function spin(array $symbols, array $weights): string
    {
        $totalWeight = array_sum($weights);
        $rand = random_int(1, $totalWeight);
        $accum = 0;

        foreach ($weights as $symbol => $weight) {
            $accum += $weight;
            if ($rand <= $accum) {
                return $symbol;
            }
        }

        return $symbols[0];
    }

    private function calculateWin(array $reels, float $apuesta, string $gameSlug): float
    {
        if ($gameSlug === 'gates-of-olympus' && in_array('⚡', $reels, true)) {
            return round($apuesta * count(array_filter($reels, fn ($symbol) => $symbol === '⚡')) * random_int(2, 5), 2);
        }

        if ($gameSlug === 'sweet-bonanza' && count(array_intersect($reels, ['🍭', '🍫', '🍬'])) >= 2) {
            return round($apuesta * 3, 2);
        }

        if ($gameSlug === 'book-of-dead' && count(array_filter($reels, fn ($symbol) => $symbol === '📖')) >= 2) {
            return round($apuesta * 12, 2);
        }

        if ($gameSlug === 'starburst' && in_array('✨', $reels, true)) {
            $nonWild = array_values(array_filter($reels, fn ($symbol) => $symbol !== '✨'));
            if (count(array_unique($nonWild)) === 1) {
                return round($apuesta * 8, 2);
            }
        }

        if ($gameSlug === 'big-bass-bonanza' && in_array('🐟', $reels, true) && in_array('🎣', $reels, true)) {
            return round($apuesta * 10, 2);
        }

        if ($reels[0] === $reels[1] && $reels[1] === $reels[2]) {
            return match ($reels[0]) {
                '7️⃣', '👑', '📖', '💎', '🐟', '🍭' => $apuesta * 50,
                '💎', '⚡', '💀', '⭐', '🎣', '🍫' => $apuesta * 25,
                '🔔', '🏆', '🧔', '🌟', '🪣', '🍬' => $apuesta * 20,
                '⭐', '🏺', '🦅', '✨', '🦞', '🍰' => $apuesta * 15,
                default => $apuesta * 10,
            };
        }

        if ($reels[0] === $reels[1] || $reels[1] === $reels[2]) {
            return $apuesta * 2;
        }

        return 0;
    }
}
