<?php

namespace App\Http\Controllers;

use App\Models\Partida;
use App\Services\CampaignManager;
use App\Services\GameBalanceService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class SlotsController extends Controller
{
    public function __construct(private readonly GameBalanceService $balances) {}

    private array $themes = [
        'default' => [
            'name' => 'Slots',
            'emoji' => '🎰',
            'tagline' => 'Gira los carretes y gana premios',
            'provider' => 'Lootra',
            'symbols' => ['🍒', '🍋', '🍊', '🍇', '💎', '⭐', '7️⃣', '🔔'],
            'weights' => ['🍒' => 25, '🍋' => 25, '🍊' => 20, '🍇' => 15, '💎' => 8, '⭐' => 5, '7️⃣' => 1, '🔔' => 1],
            'raw_return' => 0.969089,
            'target_return' => 0.969089,
            'fallback_payout' => 8,
            'pair_payout' => 2,
            'paytable' => [
                ['symbols' => '7️⃣7️⃣7️⃣', 'label' => 'x50', 'multiplier' => 50],
                ['symbols' => '💎💎💎', 'label' => 'x25', 'multiplier' => 25],
                ['symbols' => '🔔🔔🔔', 'label' => 'x20', 'multiplier' => 20],
                ['symbols' => '⭐⭐⭐', 'label' => 'x15', 'multiplier' => 15],
                ['symbols' => '🍒🍒🍒', 'label' => 'x8', 'multiplier' => 8],
                ['symbols' => 'X X X', 'label' => 'x2 (pareja)', 'multiplier' => 2],
            ],
        ],
        'gates-of-olympus' => [
            'name' => 'Gates of Olympus',
            'emoji' => '⚡',
            'tagline' => 'Viaja al Monte del Olimpo con Zeus',
            'provider' => 'Pragmatic Play',
            'atlas' => 'images/slots/olympus-symbols-v2.webp',
            'hero' => '/images/lootra_visual_pack/01_heroes/hero_slots_olympus_1920x900.webp',
            'symbols' => ['👑', '⚡', '💎', '🏆', '🏺', '🪙', '🔴', '🔵'],
            'weights' => ['👑' => 5, '⚡' => 8, '💎' => 10, '🏆' => 12, '🏺' => 18, '🪙' => 20, '🔴' => 25, '🔵' => 25],
            'raw_return' => 0.951731639069,
            'target_return' => 0.951731639069,
            'fallback_payout' => 18,
            'pair_payout' => 2,
            'paytable' => [
                ['symbols' => '👑👑👑', 'label' => 'x50', 'multiplier' => 50],
                ['symbols' => '⚡⚡⚡', 'label' => 'x25', 'multiplier' => 25],
                ['symbols' => '💎💎💎', 'label' => 'x20', 'multiplier' => 20],
                ['symbols' => '🏆🏆🏆', 'label' => 'x15', 'multiplier' => 15],
                ['symbols' => '🏺🏺🏺', 'label' => 'x10', 'multiplier' => 10],
                ['symbols' => 'X X X', 'label' => 'x2 (pareja)', 'multiplier' => 2],
            ],
        ],
        'sweet-bonanza' => [
            'name' => 'Sweet Bonanza',
            'emoji' => '🍬',
            'tagline' => 'Un mundo de dulces y frutas te espera',
            'provider' => 'Pragmatic Play',
            'atlas' => 'images/slots/sweet-symbols-v2.webp',
            'hero' => '/images/lootra_visual_pack/01_heroes/hero_slots_sweet_1920x900.webp',
            'symbols' => ['🍭', '🍫', '🍬', '🍰', '🍩', '🍒', '🫐', '🟣'],
            'weights' => ['🍭' => 8, '🍫' => 10, '🍬' => 15, '🍰' => 15, '🍩' => 20, '🍒' => 22, '🫐' => 25, '🟣' => 25],
            'raw_return' => 0.94840196793,
            'target_return' => 0.94840196793,
            'fallback_payout' => 25,
            'pair_payout' => 2,
            'paytable' => [
                ['symbols' => '🍭🍭🍭', 'label' => 'x50', 'multiplier' => 50],
                ['symbols' => '🍫🍫🍫', 'label' => 'x25', 'multiplier' => 25],
                ['symbols' => '🍬🍬🍬', 'label' => 'x20', 'multiplier' => 20],
                ['symbols' => '🍰🍰🍰', 'label' => 'x15', 'multiplier' => 15],
                ['symbols' => '🍩🍩🍩', 'label' => 'x10', 'multiplier' => 10],
                ['symbols' => 'X X X', 'label' => 'x2 (pareja)', 'multiplier' => 2],
            ],
        ],
        'book-of-dead' => [
            'name' => 'Book of Dead',
            'emoji' => '📖',
            'tagline' => 'Aventura por el antiguo Egipto con Rich Wilde',
            'provider' => "Play'n GO",
            'atlas' => 'images/slots/book-symbols-v2.webp',
            'hero' => '/images/lootra_visual_pack/03_game_covers/game_book_of_dead_800x1000.webp',
            'symbols' => ['📖', '💀', '🧔', '🦅', '🏛️', '🃏', '🔟', '👑'],
            'weights' => ['📖' => 5, '💀' => 8, '🧔' => 10, '🦅' => 15, '🏛️' => 18, '🃏' => 20, '🔟' => 25, '👑' => 25],
            'raw_return' => 0.967903534136,
            'target_return' => 0.967903534136,
            'fallback_payout' => 20,
            'pair_payout' => 2,
            'paytable' => [
                ['symbols' => '📖📖📖', 'label' => 'x50', 'multiplier' => 50],
                ['symbols' => '💀💀💀', 'label' => 'x25', 'multiplier' => 25],
                ['symbols' => '🧔🧔🧔', 'label' => 'x20', 'multiplier' => 20],
                ['symbols' => '🦅🦅🦅', 'label' => 'x15', 'multiplier' => 15],
                ['symbols' => '🏛️🏛️🏛️', 'label' => 'x10', 'multiplier' => 10],
                ['symbols' => 'X X X', 'label' => 'x2 (pareja)', 'multiplier' => 2],
            ],
        ],
        'starburst' => [
            'name' => 'Starburst',
            'emoji' => '💫',
            'tagline' => 'La slot mas iconica de NetEnt',
            'provider' => 'NetEnt',
            'atlas' => 'images/slots/starburst-symbols-v2.webp',
            'hero' => '/images/lootra_visual_pack/03_game_covers/game_starburst_800x1000.webp',
            'symbols' => ['💎', '⭐', '🌟', '✨', '🔵', '🟢', '🔴', '🟠'],
            'weights' => ['💎' => 5, '⭐' => 8, '🌟' => 12, '✨' => 15, '🔵' => 20, '🟢' => 22, '🔴' => 25, '🟠' => 25],
            'raw_return' => 0.960933804296,
            'target_return' => 0.960933804296,
            'fallback_payout' => 21,
            'pair_payout' => 2,
            'paytable' => [
                ['symbols' => '💎💎💎', 'label' => 'x50', 'multiplier' => 50],
                ['symbols' => '⭐⭐⭐', 'label' => 'x25', 'multiplier' => 25],
                ['symbols' => '🌟🌟🌟', 'label' => 'x20', 'multiplier' => 20],
                ['symbols' => '✨✨✨', 'label' => 'x15', 'multiplier' => 15],
                ['symbols' => '🔵🔵🔵', 'label' => 'x10', 'multiplier' => 10],
                ['symbols' => 'X X X', 'label' => 'x2 (pareja)', 'multiplier' => 2],
            ],
        ],
        'big-bass-bonanza' => [
            'name' => 'Big Bass Bonanza',
            'emoji' => '🐟',
            'tagline' => 'Salva de pesca en esta slot acuatica',
            'provider' => 'Pragmatic Play',
            'atlas' => 'images/slots/bass-symbols-v2.webp',
            'hero' => '/images/lootra_visual_pack/03_game_covers/game_big_bass_bonanza_800x1000.webp',
            'symbols' => ['🐟', '🎣', '🪣', '🦞', '🐡', '🌊', '⚓', '🐠'],
            'weights' => ['🐟' => 5, '🎣' => 8, '🪣' => 12, '🦞' => 15, '🐡' => 20, '🌊' => 22, '⚓' => 25, '🐠' => 25],
            'raw_return' => 0.960933804296,
            'target_return' => 0.960933804296,
            'fallback_payout' => 21,
            'pair_payout' => 2,
            'paytable' => [
                ['symbols' => '🐟🐟🐟', 'label' => 'x50', 'multiplier' => 50],
                ['symbols' => '🎣🎣🎣', 'label' => 'x25', 'multiplier' => 25],
                ['symbols' => '🪣🪣🪣', 'label' => 'x20', 'multiplier' => 20],
                ['symbols' => '🦞🦞🦞', 'label' => 'x15', 'multiplier' => 15],
                ['symbols' => '🐡🐡🐡', 'label' => 'x10', 'multiplier' => 10],
                ['symbols' => 'X X X', 'label' => 'x2 (pareja)', 'multiplier' => 2],
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
        $triplePaytable = collect($theme['paytable'])
            ->reject(fn (array $row) => $row['symbols'] === 'X X X')
            ->map(fn (array $row) => [
                ...$row,
                'icon' => collect($theme['symbols'])->first(fn (string $symbol) => str_repeat($symbol, 3) === $row['symbols']) ?? $theme['symbols'][0],
            ]);
        $pairPaytable = collect($theme['paytable'])->firstWhere('symbols', 'X X X');
        $paytable = $triplePaytable
            ->push([
                'symbols' => 'OTHER TRIPLE',
                'label' => 'x'.rtrim(rtrim(number_format($theme['fallback_payout'], 2, '.', ''), '0'), '.'),
                'icon' => $theme['symbols'][0],
            ])
            ->push([
                ...$pairPaytable,
                'icon' => $theme['symbols'][0],
            ])
            ->values()
            ->all();

        return view('games.slots', [
            'partidas' => $partidas,
            'gameSlug' => $gameSlug,
            'gameName' => $theme['name'],
            'gameEmoji' => $theme['emoji'],
            'gameTagline' => $theme['tagline'],
            'gameProvider' => $theme['provider'],
            'rtp' => number_format($theme['target_return'] * 100, 1, ',', '.'),
            'symbols' => $theme['symbols'],
            'paytable' => $paytable,
            'symbolAtlas' => asset($theme['atlas'] ?? 'images/slots/olympus-symbols-v2.webp'),
            'gameHero' => $theme['hero'] ?? null,
            'saldo' => $this->balances->balance(Auth::user(), 'slots'),
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
            $campaignId = $this->balances->campaignId($user, 'slots');
            $existing = Partida::where('usuario_id', $user->id)->where('juego', 'slots')
                ->where('request_token', $request->request_token)->lockForUpdate()->first();
            if ($existing) {
                return $existing;
            }
            abort_unless($this->balances->debit($user, 'slots', $apuesta, 'apuesta_slots', ['juego' => $gameSlug], null, $request->request_token, $campaignId), 422, 'Saldo insuficiente.');
            $reels = [
                $this->spin($theme['symbols'], $theme['weights']),
                $this->spin($theme['symbols'], $theme['weights']),
                $this->spin($theme['symbols'], $theme['weights']),
            ];
            $multiplicador = $this->winningMultiplier($reels, $gameSlug);
            $combinacion = $this->winningType($reels);
            $ganancia = $this->calculateWin($reels, $apuesta, $gameSlug);
            if ($ganancia > 0) {
                $this->balances->credit($user, 'slots', $ganancia, 'premio_slots', ['juego' => $gameSlug], null, $campaignId);
            }

            $game = Partida::create([
                'usuario_id' => $user->id, 'juego' => 'slots', 'request_token' => $request->request_token,
                'apuesta' => $apuesta, 'ganancia' => $ganancia,
                'detalles' => [
                    'reels' => $reels,
                    'resultado' => $ganancia > 0 ? 'win' : 'lose',
                    'game' => $gameSlug,
                    'multiplicador' => $multiplicador,
                    'combinacion' => $combinacion,
                ],
                'campaign_challenge_id' => $campaignId,
                'campaign_key' => $campaignId ? CampaignManager::KEY : null,
            ]);
            $this->balances->recordGame($game);

            return $game;
        });

        return response()->json([
            'reels' => $round->detalles['reels'],
            'ganancia' => (float) $round->ganancia,
            'resultado' => $round->detalles['resultado'],
            'multiplicador' => (float) ($round->detalles['multiplicador'] ?? 0),
            'combinacion' => $round->detalles['combinacion'] ?? 'none',
            'saldo' => $this->balances->balance($user, 'slots', $round->campaign_challenge_id),
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
        return round($apuesta * $this->winningMultiplier($reels, $gameSlug), 2);
    }

    private function winningMultiplier(array $reels, string $gameSlug): float
    {
        $theme = $this->themes[$gameSlug] ?? $this->themes['default'];

        if ($this->winningType($reels) === 'triple') {
            $symbols = implode('', $reels);
            $row = collect($theme['paytable'])->firstWhere('symbols', $symbols);

            return (float) ($row['multiplier'] ?? $theme['fallback_payout']);
        }

        return $this->winningType($reels) === 'pair' ? (float) $theme['pair_payout'] : 0.0;
    }

    private function winningType(array $reels): string
    {
        if ($reels[0] === $reels[1] && $reels[1] === $reels[2]) {
            return 'triple';
        }

        if ($reels[0] === $reels[1] || $reels[1] === $reels[2]) {
            return 'pair';
        }

        return 'none';
    }
}
