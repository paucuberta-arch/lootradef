<?php

namespace App\Http\Controllers;

use App\Models\Partida;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ArcadeController extends Controller
{
    public function index(Request $request, string $game): View
    {
        $definition = config("arcade_games.{$game}");
        abort_unless($definition, 404);

        return view($definition['mode'] === 'poker' ? 'games.poker-live' : 'games.arcade', [
            'game' => $definition,
            'history' => Partida::where('usuario_id', $request->user()->id)->where('juego', $game)->latest()->take(10)->get(),
            'initialCard' => $definition['mode'] === 'hilo' ? $this->prepareHiLo() : null,
        ]);
    }

    public function play(Request $request, string $game): JsonResponse
    {
        $definition = config("arcade_games.{$game}");
        abort_unless($definition, 404);
        $data = $request->validate([
            'apuesta' => 'required|numeric|min:0.2|max:500',
            'choice' => 'nullable',
            'numbers' => 'nullable|array|max:5',
            'numbers.*' => 'integer|between:1,30',
        ]);
        $user = $request->user();
        $bet = round((float) $data['apuesta'], 2);
        $choice = is_scalar($data['choice'] ?? null) ? (string) $data['choice'] : '';

        if (! $user->cartera || ! $user->cartera->apostar($bet, 'apuesta_original', ['juego' => $game])) {
            return response()->json(['message' => 'Saldo insuficiente.'], 422);
        }

        $result = match ($definition['mode']) {
            'wheel' => $this->wheel(), 'mines' => $this->mines($choice ?: 3),
            'dice' => $this->dice($choice ?: 'high'), 'hilo' => $this->hiLo($choice ?: 'higher'),
            'plinko' => $this->plinko(), 'keno' => $this->keno($data['numbers'] ?? []),
            'coin' => $this->coin($choice ?: 'heads'), 'baccarat' => $this->baccarat($choice ?: 'player'),
            'nebula' => $this->nebula($choice ?: 'violet'), 'poker' => $this->poker(),
        };

        $win = round($bet * $result['multiplier'], 2);
        if ($win > 0) {
            $user->cartera->ganar($win, 'premio_original', ['juego' => $game]);
        }

        Partida::create(['usuario_id' => $user->id, 'juego' => $game, 'apuesta' => $bet, 'ganancia' => $win, 'detalles' => $result]);

        return response()->json([...$result, 'ganancia' => $win, 'saldo' => (float) $user->cartera->saldo]);
    }

    private function wheel(): array
    {
        $segments = [0, 0, 0, .5, .5, 1, 1, 1.5, 2, 3];
        $index = array_rand($segments);

        return ['multiplier' => $segments[$index], 'segment' => $index, 'label' => $segments[$index].'x'];
    }

    private function mines(mixed $choice): array
    {
        $mines = max(1, min(8, (int) $choice));
        $safe = random_int(1, 12) > $mines;

        return ['multiplier' => $safe ? round(1 + $mines * .24, 2) : 0, 'safe' => $safe, 'mines' => $mines, 'cell' => random_int(0, 15)];
    }

    private function dice(string $choice): array
    {
        $roll = random_int(1, 100);
        $won = $choice === 'high' ? $roll > 55 : $roll < 46;

        return ['multiplier' => $won ? 2.05 : 0, 'roll' => $roll, 'choice' => $choice];
    }

    private function hiLo(string $choice): array
    {
        $first = (int) session()->pull('hilo_card_game', random_int(1, 13));
        $next = random_int(1, 13);
        session(['hilo_card_game' => $next]);
        $won = $choice === 'higher' ? $next > $first : $next < $first;

        return ['multiplier' => $won ? 1.9 : ($next === $first ? 1 : 0), 'first' => $first, 'next' => $next, 'choice' => $choice];
    }

    private function plinko(): array
    {
        $slots = [10, 3, 1.5, 1, .5, .2, .5, 1, 1.5, 3, 10];
        $path = array_map(fn () => random_int(0, 1), range(1, 10));
        $slot = array_sum($path);

        return ['multiplier' => $slots[$slot], 'slot' => $slot, 'path' => $path];
    }

    private function keno(array $numbers): array
    {
        $chosen = array_values(array_unique(array_map('intval', $numbers)));
        if (count($chosen) !== 5 || min($chosen) < 1 || max($chosen) > 30) {
            $chosen = [1, 7, 13, 21, 28];
        }
        $draw = collect(range(1, 30))->shuffle()->take(10)->sort()->values()->all();
        $hits = count(array_intersect($chosen, $draw));

        return ['multiplier' => [0, 0, .5, 2, 8, 25][$hits], 'chosen' => $chosen, 'draw' => $draw, 'hits' => $hits];
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

        return ['multiplier' => $choice === $winner ? ($winner === 'tie' ? 8 : ($winner === 'banker' ? 1.95 : 2)) : 0, 'player' => $player, 'banker' => $banker, 'winner' => $winner];
    }

    private function nebula(string $choice): array
    {
        $tables = [
            'violet' => [0, .5, .8, 1, 1, 1.2, 1.5, 2],
            'cyan' => [0, 0, 0, .5, 1, 1.5, 2, 5, 0, 0],
            'gold' => [0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 1, 3, 10],
        ];
        $table = $tables[$choice] ?? $tables['violet'];
        $multiplier = $table[array_rand($table)];

        return ['multiplier' => $multiplier, 'crystal' => $choice];
    }

    private function prepareHiLo(): int
    {
        $card = random_int(1, 13);
        session(['hilo_card_game' => $card]);

        return $card;
    }

    private function poker(): array
    {
        $deck = [];
        foreach (['S', 'H', 'D', 'C'] as $suit) {
            foreach (range(2, 14) as $rank) {
                $deck[] = ['rank' => $rank, 'suit' => $suit];
            }
        }
        shuffle($deck);
        $player = [array_pop($deck), array_pop($deck)];
        $dealer = [array_pop($deck), array_pop($deck)];
        $community = array_splice($deck, 0, 5);
        $playerScore = $this->pokerScore([...$player, ...$community]);
        $dealerScore = $this->pokerScore([...$dealer, ...$community]);
        $multiplier = $playerScore['score'] > $dealerScore['score'] ? 2 : ($playerScore['score'] === $dealerScore['score'] ? 1 : 0);

        return compact('multiplier', 'player', 'dealer', 'community') + ['player_hand' => $playerScore['name'], 'dealer_hand' => $dealerScore['name']];
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
