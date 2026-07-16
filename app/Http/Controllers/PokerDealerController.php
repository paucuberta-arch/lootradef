<?php

namespace App\Http\Controllers;

use App\Models\Cartera;
use App\Models\Partida;
use App\Models\PokerDealerHand;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class PokerDealerController extends Controller
{
    public function index(Request $request): View
    {
        $active = PokerDealerHand::where('usuario_id', $request->user()->id)->whereNotIn('fase', ['finalizada', 'retirada'])->latest()->first();

        return view('games.poker-dealer', [
            'activeHand' => $active ? $this->data($active) : null,
            'history' => Partida::where('usuario_id', $request->user()->id)->where('juego', 'poker_dealer')->latest()->take(8)->get(),
        ]);
    }

    public function start(Request $request): JsonResponse
    {
        $validated = $request->validate(['ante' => ['required', 'numeric', 'min:1', 'max:1000']]);
        $hand = DB::transaction(function () use ($request, $validated) {
            if (PokerDealerHand::where('usuario_id', $request->user()->id)->whereNotIn('fase', ['finalizada', 'retirada'])->lockForUpdate()->exists()) {
                abort(409, 'Ya tienes una partida de poker en curso.');
            }
            $wallet = Cartera::where('usuario_id', $request->user()->id)->lockForUpdate()->firstOrFail();
            $ante = round((float) $validated['ante'], 2);
            abort_if($wallet->saldo < $ante, 422, 'Saldo insuficiente.');
            $deck = $this->deck();
            shuffle($deck);

            $hand = PokerDealerHand::create([
                'usuario_id' => $request->user()->id, 'ante' => $ante, 'apostado' => $ante,
                'mano_jugador' => [array_pop($deck), array_pop($deck)],
                'mano_dealer' => [array_pop($deck), array_pop($deck)],
                'comunitarias' => [], 'baraja' => $deck, 'fase' => 'preflop',
            ]);
            abort_unless($wallet->apostar($ante, 'ante_poker_dealer', [], $hand), 422, 'Saldo insuficiente.');

            return $hand;
        });

        return response()->json($this->data($hand), 201);
    }

    public function action(Request $request): JsonResponse
    {
        $action = $request->validate(['accion' => ['required', 'in:jugar,continuar,retirarse']])['accion'];
        $hand = DB::transaction(function () use ($request, $action) {
            $hand = PokerDealerHand::where('usuario_id', $request->user()->id)->whereNotIn('fase', ['finalizada', 'retirada'])->lockForUpdate()->first();
            abort_unless($hand, 409, 'No tienes una partida activa.');

            if ($action === 'retirarse') {
                $this->finish($hand, 'retirada', 0, 'retirada');

                return $hand->fresh();
            }
            if ($hand->fase === 'preflop') {
                abort_unless($action === 'jugar', 422, 'Primero debes jugar la mano o retirarte.');
                $wallet = Cartera::where('usuario_id', $hand->usuario_id)->lockForUpdate()->firstOrFail();
                abort_if($wallet->saldo < $hand->ante, 422, 'Necesitas saldo para igualar el ante.');
                abort_unless($wallet->apostar($hand->ante, 'igualar_poker_dealer', ['fase' => 'preflop'], $hand), 422, 'Necesitas saldo para igualar el ante.');
                $hand->apostado += $hand->ante;
                $this->reveal($hand, 3, 'flop');
            } elseif ($hand->fase === 'flop') {
                abort_unless($action === 'continuar', 422, 'La acción disponible es ver el turn o retirarte.');
                $this->reveal($hand, 1, 'turn');
            } elseif ($hand->fase === 'turn') {
                abort_unless($action === 'continuar', 422, 'La acción disponible es ver el river o retirarte.');
                $this->reveal($hand, 1, 'river');
            } elseif ($hand->fase === 'river') {
                abort_unless($action === 'continuar', 422, 'La acción disponible es llegar al showdown o retirarte.');
                $player = $this->score([...$hand->mano_jugador, ...$hand->comunitarias]);
                $dealer = $this->score([...$hand->mano_dealer, ...$hand->comunitarias]);
                $result = $player['score'] > $dealer['score'] ? 'ganada' : ($player['score'] === $dealer['score'] ? 'empate' : 'perdida');
                $payout = $result === 'ganada' ? $hand->apostado * 2 : ($result === 'empate' ? $hand->apostado : 0);
                $this->finish($hand, $result, $payout, 'finalizada');
            }

            return $hand->fresh();
        });

        return response()->json($this->data($hand));
    }

    private function reveal(PokerDealerHand $hand, int $count, string $phase): void
    {
        $deck = $hand->baraja;
        $community = $hand->comunitarias;
        for ($i = 0; $i < $count; $i++) {
            $community[] = array_pop($deck);
        }
        $hand->update(['baraja' => $deck, 'comunitarias' => $community, 'fase' => $phase, 'apostado' => $hand->apostado]);
    }

    private function finish(PokerDealerHand $hand, string $result, float $payout, string $phase): void
    {
        if ($payout > 0) {
            $wallet = Cartera::where('usuario_id', $hand->usuario_id)->lockForUpdate()->firstOrFail();
            $wallet->ganar($payout, 'premio_poker_dealer', ['resultado' => $result], $hand);
        }
        $hand->update(['fase' => $phase, 'resultado' => $result, 'ganancia' => $payout, 'finalizada_at' => now()]);
        Partida::create([
            'usuario_id' => $hand->usuario_id, 'juego' => 'poker_dealer', 'apuesta' => $hand->apostado, 'ganancia' => $payout,
            'detalles' => ['resultado' => $result, 'jugador' => $this->score([...$hand->mano_jugador, ...$hand->comunitarias])['name'], 'dealer' => $this->score([...$hand->mano_dealer, ...$hand->comunitarias])['name']],
        ]);
    }

    private function data(PokerDealerHand $hand): array
    {
        $finished = in_array($hand->fase, ['finalizada', 'retirada'], true);

        return [
            'id' => $hand->id, 'phase' => $hand->fase, 'player' => $hand->mano_jugador,
            'dealer' => $finished ? $hand->mano_dealer : [['hidden' => true], ['hidden' => true]],
            'community' => $hand->comunitarias ?? [], 'ante' => $hand->ante, 'wagered' => $hand->apostado,
            'result' => $hand->resultado, 'winnings' => $hand->ganancia,
            'player_hand' => count($hand->comunitarias ?? []) >= 3 ? $this->score([...$hand->mano_jugador, ...$hand->comunitarias])['name'] : null,
            'dealer_hand' => $finished ? $this->score([...$hand->mano_dealer, ...$hand->comunitarias])['name'] : null,
            'balance' => (float) Cartera::where('usuario_id', $hand->usuario_id)->value('saldo'),
        ];
    }

    private function deck(): array
    {
        $deck = [];
        foreach (['S', 'H', 'D', 'C'] as $suit) {
            foreach (range(2, 14) as $rank) {
                $deck[] = compact('rank', 'suit');
            }
        }

        return $deck;
    }

    private function score(array $cards): array
    {
        $best = ['score' => -1, 'name' => 'Carta alta'];
        foreach ($this->combinations($cards, 5) as $hand) {
            $value = $this->five($hand);
            if ($value['score'] > $best['score']) {
                $best = $value;
            }
        }

        return $best;
    }

    private function five(array $cards): array
    {
        $ranks = array_column($cards, 'rank');
        rsort($ranks);
        $counts = array_count_values($ranks);
        arsort($counts);
        $groups = collect($counts)->map(fn ($count, $rank) => ['rank' => (int) $rank, 'count' => $count])->sort(fn ($a, $b) => [$b['count'], $b['rank']] <=> [$a['count'], $a['rank']])->values();
        $flush = count(array_unique(array_column($cards, 'suit'))) === 1;
        $unique = array_values(array_unique($ranks));
        $straight = $unique === [14, 5, 4, 3, 2] ? 5 : (count($unique) === 5 && $unique[0] - $unique[4] === 4 ? $unique[0] : 0);
        if ($flush && $straight) {
            return $this->value(8, 'Escalera de color', [$straight]);
        }
        if ($groups[0]['count'] === 4) {
            return $this->value(7, 'Póker', $groups->pluck('rank')->all());
        }
        if ($groups[0]['count'] === 3 && $groups[1]['count'] === 2) {
            return $this->value(6, 'Full house', $groups->pluck('rank')->all());
        }
        if ($flush) {
            return $this->value(5, 'Color', $ranks);
        }
        if ($straight) {
            return $this->value(4, 'Escalera', [$straight]);
        }
        if ($groups[0]['count'] === 3) {
            return $this->value(3, 'Trío', $groups->pluck('rank')->all());
        }
        if ($groups[0]['count'] === 2 && $groups[1]['count'] === 2) {
            return $this->value(2, 'Doble pareja', $groups->pluck('rank')->all());
        }
        if ($groups[0]['count'] === 2) {
            return $this->value(1, 'Pareja', $groups->pluck('rank')->all());
        }

        return $this->value(0, 'Carta alta', $ranks);
    }

    private function value(int $category, string $name, array $ranks): array
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
