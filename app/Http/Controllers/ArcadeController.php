<?php

namespace App\Http\Controllers;

use App\Models\Partida;
use App\Services\CampaignManager;
use App\Services\GameBalanceService;
use App\Services\GameCatalog;
use App\Services\SecureRandom;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ArcadeController extends Controller
{
    private const HI_LO_RETURN_TO_PLAYER = 0.96;

    private const DICE_WIN_MULTIPLIER = 2.1333;

    private const POKER_WIN_MULTIPLIER = 1.92;

    private const POKER_TIE_MULTIPLIER = 0.96;

    // Hypergeometric probabilities for 5 numbers selected from 30 and a
    // 10-number draw produce an RTP of approximately 96.00% with this table.
    // The values are fixed for every player and are not changed at runtime.
    private const KENO_MULTIPLIERS = [0, 0, 1.5, 1.5, 5, 18.46];

    private const BACCARAT_WIN_MULTIPLIER = 2.1333;

    private const BACCARAT_TIE_MULTIPLIER = 9.6;

    private const NEBULA_TABLES = [
        'violet' => [0, .5, .8, 1, 1, 1.2, 1.5, 1.68],
        'cyan' => [0, 0, 0, .5, 1, 1.2, 2, 4.9, 0, 0],
        'gold' => [0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 1, 3, 5.2, 10],
    ];

    public function __construct(
        private readonly GameBalanceService $balances,
        private readonly SecureRandom $random,
    ) {}

    public function index(Request $request, string $game, GameCatalog $catalog): View
    {
        $definition = $catalog->find($game);
        abort_unless($definition && isset($definition['mode']), 404);

        $view = match ($definition['mode']) {
            'poker' => 'games.poker-live',
            'plinko' => 'games.quantum-plinko',
            default => 'games.arcade',
        };

        return view($view, [
            'game' => $definition,
            'history' => Partida::where('usuario_id', $request->user()->id)->where('juego', $game)->latest()->take(10)->get(),
            'initialCard' => $definition['mode'] === 'hilo' ? $this->prepareHiLo() : null,
            'gameBalance' => $this->balances->balance($request->user(), $game),
        ]);
    }

    public function play(Request $request, string $game, GameCatalog $catalog): JsonResponse
    {
        $definition = $catalog->find($game);
        abort_unless($definition && isset($definition['mode']), 404);
        $data = $request->validate([
            'apuesta' => 'required|numeric|min:0.2|max:500',
            'choice' => 'nullable',
            'numbers' => 'nullable|array|max:5',
            'numbers.*' => 'integer|between:1,30',
            'request_token' => 'required|uuid',
        ]);
        $user = $request->user();
        $bet = round((float) $data['apuesta'], 2);
        $choice = is_scalar($data['choice'] ?? null) ? (string) $data['choice'] : '';
        $this->validateChoice($definition['mode'], $choice, $data['numbers'] ?? []);

        $round = DB::transaction(function () use ($user, $bet, $game, $definition, $choice, $data) {
            $campaignId = $this->balances->campaignId($user, $game);
            $existing = Partida::where('usuario_id', $user->id)->where('juego', $game)
                ->where('request_token', $data['request_token'])->lockForUpdate()->first();
            if ($existing) {
                return $existing;
            }
            abort_unless($this->balances->debit($user, $game, $bet, 'apuesta_original', ['juego' => $game], null, $data['request_token'], $campaignId), 422, 'Saldo insuficiente.');
            $result = match ($definition['mode']) {
                'wheel' => $this->wheel(), 'mines' => $this->mines($choice),
                'dice' => $this->dice($choice), 'hilo' => $this->hiLo($choice),
                'plinko' => $this->plinko(), 'keno' => $this->keno($data['numbers'] ?? []),
                'coin' => $this->coin($choice), 'baccarat' => $this->baccarat($choice),
                'nebula' => $this->nebula($choice), 'poker' => $this->poker(),
            };
            $win = round($bet * $result['multiplier'], 2);
            if ($win > 0) {
                $this->balances->credit($user, $game, $win, 'premio_original', ['juego' => $game], null, $campaignId, 'game-payout:'.$game.':'.$data['request_token']);
            }

            $round = Partida::create([
                'usuario_id' => $user->id, 'juego' => $game, 'request_token' => $data['request_token'],
                'apuesta' => $bet, 'ganancia' => $win, 'detalles' => $result,
                'campaign_challenge_id' => $campaignId,
                'campaign_key' => $campaignId ? CampaignManager::KEY : null,
            ]);
            $this->balances->recordGame($round);

            return $round;
        });

        return response()->json([
            ...$round->detalles,
            'apuesta' => (float) $round->apuesta,
            'ganancia' => (float) $round->ganancia,
            'saldo' => $this->balances->balance($user, $game, $round->campaign_challenge_id),
        ]);
    }

    private function wheel(): array
    {
        $segments = [0, 0, 0, .5, .5, 1, 1, 1.5, 2, 3];
        $index = $this->random->index($segments);

        return ['multiplier' => $segments[$index], 'segment' => $index, 'label' => $segments[$index].'x'];
    }

    private function mines(mixed $choice): array
    {
        $mines = max(1, min(8, (int) $choice));
        $safe = random_int(1, 12) > $mines;
        $safeOutcomes = 12 - $mines;
        $multiplier = round((self::HI_LO_RETURN_TO_PLAYER * 12) / $safeOutcomes, 4);

        return ['multiplier' => $safe ? $multiplier : 0, 'safe' => $safe, 'mines' => $mines, 'cell' => random_int(0, 15)];
    }

    private function dice(string $choice): array
    {
        $roll = random_int(1, 100);
        $won = $choice === 'high' ? $roll > 55 : $roll < 46;

        return ['multiplier' => $won ? self::DICE_WIN_MULTIPLIER : 0, 'roll' => $roll, 'choice' => $choice];
    }

    private function hiLo(string $choice): array
    {
        $first = (int) session()->pull('hilo_card_game', random_int(1, 13));
        $next = random_int(1, 13);
        session(['hilo_card_game' => $next]);
        $won = $choice === 'higher' ? $next > $first : $next < $first;

        return [
            'multiplier' => $won ? $this->hiLoMultiplier($first, $choice) : ($next === $first ? 1 : 0),
            'first' => $first,
            'next' => $next,
            'choice' => $choice,
        ];
    }

    private function plinko(): array
    {
        $slots = [12, 5, 2, 1.2, .7, .4, .7, 1.2, 2, 5, 12];
        $path = array_map(fn () => random_int(0, 1), range(1, 10));
        $slot = array_sum($path);

        return ['multiplier' => $slots[$slot], 'slot' => $slot, 'path' => $path];
    }

    private function keno(array $numbers): array
    {
        $chosen = array_values(array_unique(array_map('intval', $numbers)));
        $draw = collect($this->random->shuffle(range(1, 30)))->take(10)->sort()->values()->all();
        $hits = count(array_intersect($chosen, $draw));

        return ['multiplier' => self::KENO_MULTIPLIERS[$hits], 'chosen' => $chosen, 'draw' => $draw, 'hits' => $hits];
    }

    private function coin(string $choice): array
    {
        $landed = random_int(0, 1) ? 'heads' : 'tails';

        return ['multiplier' => $choice === $landed ? 1.95 : 0, 'choice' => $choice, 'landed' => $landed];
    }

    private function baccarat(string $choice): array
    {
        $player = (random_int(1, 10) + random_int(1, 10)) % 10;
        $banker = (random_int(1, 10) + random_int(1, 10)) % 10;
        $winner = $player === $banker ? 'tie' : ($player > $banker ? 'player' : 'banker');

        return [
            'multiplier' => $choice === $winner
                ? ($winner === 'tie' ? self::BACCARAT_TIE_MULTIPLIER : self::BACCARAT_WIN_MULTIPLIER)
                : 0,
            'player' => $player,
            'banker' => $banker,
            'winner' => $winner,
        ];
    }

    private function nebula(string $choice): array
    {
        $table = self::NEBULA_TABLES[$choice] ?? self::NEBULA_TABLES['violet'];
        $multiplier = $this->random->pick($table);

        return ['multiplier' => $multiplier, 'crystal' => $choice];
    }

    private function prepareHiLo(): int
    {
        $current = session('hilo_card_game');
        if (is_int($current) && $current >= 1 && $current <= 13) {
            return $current;
        }

        $card = random_int(1, 13);
        session(['hilo_card_game' => $card]);

        return $card;
    }

    private function hiLoMultiplier(int $first, string $choice): float
    {
        $winningOutcomes = $choice === 'higher' ? 13 - $first : $first - 1;
        if ($winningOutcomes === 0) {
            return 0;
        }

        // A tie returns the stake. The win multiplier is therefore derived from
        // the remaining outcomes so every visible card keeps the configured RTP.
        return round(((self::HI_LO_RETURN_TO_PLAYER * 13) - 1) / $winningOutcomes, 4);
    }

    private function poker(): array
    {
        $deck = [];
        foreach (['S', 'H', 'D', 'C'] as $suit) {
            foreach (range(2, 14) as $rank) {
                $deck[] = ['rank' => $rank, 'suit' => $suit];
            }
        }
        $deck = $this->random->shuffle($deck);
        $player = [array_pop($deck), array_pop($deck)];
        $dealer = [array_pop($deck), array_pop($deck)];
        $community = array_splice($deck, 0, 5);
        $playerScore = $this->pokerScore([...$player, ...$community]);
        $dealerScore = $this->pokerScore([...$dealer, ...$community]);
        $outcome = $playerScore['score'] > $dealerScore['score']
            ? 'win'
            : ($playerScore['score'] === $dealerScore['score'] ? 'tie' : 'lose');
        $multiplier = $outcome === 'win'
            ? self::POKER_WIN_MULTIPLIER
            : ($outcome === 'tie' ? self::POKER_TIE_MULTIPLIER : 0);

        return compact('multiplier', 'player', 'dealer', 'community', 'outcome') + ['player_hand' => $playerScore['name'], 'dealer_hand' => $dealerScore['name']];
    }

    private function validateChoice(string $mode, string $choice, array $numbers): void
    {
        $allowed = [
            'mines' => ['1', '3', '5', '8'], 'dice' => ['high', 'low'],
            'hilo' => ['higher', 'lower'], 'coin' => ['heads', 'tails'],
            'baccarat' => ['player', 'banker', 'tie'], 'nebula' => ['violet', 'cyan', 'gold'],
        ];
        if (isset($allowed[$mode])) {
            abort_unless(in_array($choice, $allowed[$mode], true), 422, 'La selección no es válida para este juego.');
        }
        if ($mode === 'keno') {
            $unique = array_unique(array_map('intval', $numbers));
            abort_unless(count($unique) === 5, 422, 'Debes elegir exactamente cinco números distintos.');
        }
    }

    private function pokerScore(array $cards): array
    {
        $best = ['score' => -1, 'name' => 'Carta alta'];

        foreach ($this->combinations($cards, 5) as $hand) {
            $evaluated = $this->evaluateFiveCards($hand);
            if ($evaluated['score'] > $best['score']) {
                $best = $evaluated;
            }
        }

        return $best;
    }

    private function evaluateFiveCards(array $cards): array
    {
        $ranks = array_column($cards, 'rank');
        rsort($ranks);
        $groups = collect(array_count_values($ranks))
            ->map(fn ($count, $rank) => ['rank' => (int) $rank, 'count' => $count])
            ->sort(fn ($a, $b) => [$b['count'], $b['rank']] <=> [$a['count'], $a['rank']])
            ->values();
        $flush = count(array_unique(array_column($cards, 'suit'))) === 1;
        $unique = array_values(array_unique($ranks));
        if ($unique === [14, 5, 4, 3, 2]) {
            $straightHigh = 5;
        } else {
            $straightHigh = count($unique) === 5 && $unique[0] - $unique[4] === 4 ? $unique[0] : 0;
        }

        if ($flush && $straightHigh) {
            return $this->handScore(8, 'Escalera de color', [$straightHigh]);
        }
        if ($groups[0]['count'] === 4) {
            return $this->handScore(7, 'Póker', [$groups[0]['rank'], $groups[1]['rank']]);
        }
        if ($groups[0]['count'] === 3 && $groups[1]['count'] === 2) {
            return $this->handScore(6, 'Full house', [$groups[0]['rank'], $groups[1]['rank']]);
        }
        if ($flush) {
            return $this->handScore(5, 'Color', $ranks);
        }
        if ($straightHigh) {
            return $this->handScore(4, 'Escalera', [$straightHigh]);
        }
        if ($groups[0]['count'] === 3) {
            return $this->handScore(3, 'Trío', $groups->pluck('rank')->all());
        }
        if ($groups[0]['count'] === 2 && $groups[1]['count'] === 2) {
            return $this->handScore(2, 'Doble pareja', $groups->pluck('rank')->all());
        }
        if ($groups[0]['count'] === 2) {
            return $this->handScore(1, 'Pareja', $groups->pluck('rank')->all());
        }

        return $this->handScore(0, 'Carta alta', $ranks);
    }

    private function handScore(int $category, string $name, array $ranks): array
    {
        $score = $category;
        foreach (array_pad(array_slice($ranks, 0, 5), 5, 0) as $rank) {
            $score = $score * 15 + $rank;
        }

        return compact('score', 'name');
    }

    private function combinations(array $cards, int $size, int $offset = 0, array $prefix = []): array
    {
        if ($size === 0) {
            return [$prefix];
        }

        $result = [];
        for ($i = $offset; $i <= count($cards) - $size; $i++) {
            $result = [...$result, ...$this->combinations($cards, $size - 1, $i + 1, [...$prefix, $cards[$i]])];
        }

        return $result;
    }
}
