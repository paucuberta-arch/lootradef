<?php

namespace App\Http\Controllers;

use App\Models\Partida;
use App\Models\PokerDealerHand;
use App\Models\Usuario;
use App\Services\CampaignManager;
use App\Services\GameBalanceService;
use App\Services\SecureRandom;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;

class PokerDealerController extends Controller
{
    private const WIN_PAYOUT_MULTIPLIER = 1.92;

    private const TIE_PAYOUT_MULTIPLIER = 0.96;

    public function __construct(
        private readonly GameBalanceService $balances,
        private readonly SecureRandom $random,
    ) {}

    public function index(Request $request): View
    {
        $campaignId = $this->balances->campaignId($request->user(), 'poker_dealer');
        $active = PokerDealerHand::where('usuario_id', $request->user()->id)
            ->where('campaign_challenge_id', $campaignId)->whereNotIn('fase', ['finalizada', 'retirada'])->latest()->first();

        return view('games.poker-dealer', [
            'activeHand' => $active ? $this->data($active) : null,
            'history' => Partida::where('usuario_id', $request->user()->id)->where('juego', 'poker_dealer')->latest()->take(8)->get(),
            'gameBalance' => $this->balances->balance($request->user(), 'poker_dealer', $campaignId),
        ]);
    }

    public function start(Request $request): JsonResponse
    {
        if (! $request->filled('request_token')) {
            $request->merge(['request_token' => (string) Str::uuid()]);
        }
        $validated = $request->validate([
            'ante' => ['required', 'numeric', 'min:1', 'max:1000'],
            'request_token' => ['required', 'uuid'],
        ]);
        $campaignId = $this->balances->campaignId($request->user(), 'poker_dealer');
        $hand = DB::transaction(function () use ($request, $validated, $campaignId) {
            $existing = PokerDealerHand::where('usuario_id', $request->user()->id)
                ->where('request_token', $validated['request_token'])->first();
            if ($existing) {
                return $existing;
            }
            if (PokerDealerHand::where('usuario_id', $request->user()->id)->where('campaign_challenge_id', $campaignId)->whereNotIn('fase', ['finalizada', 'retirada'])->lockForUpdate()->exists()) {
                abort(409, 'Ya tienes una partida de poker en curso.');
            }
            $ante = round((float) $validated['ante'], 2);
            $deck = $this->deck();
            $deck = $this->random->shuffle($deck);

            $hand = PokerDealerHand::create([
                'usuario_id' => $request->user()->id, 'request_token' => $validated['request_token'], 'ante' => $ante, 'apostado' => $ante,
                'mano_jugador' => [array_pop($deck), array_pop($deck)],
                'mano_dealer' => [array_pop($deck), array_pop($deck)],
                'comunitarias' => [], 'baraja' => $deck, 'fase' => 'preflop',
                'campaign_challenge_id' => $campaignId, 'campaign_key' => $campaignId ? CampaignManager::KEY : null,
            ]);
            abort_unless($this->balances->debit($request->user(), 'poker_dealer', $ante, 'ante_poker_dealer', [], $hand, $validated['request_token'], $campaignId), 422, 'Saldo insuficiente.');

            return $hand;
        });

        return response()->json($this->data($hand), 201);
    }

    public function action(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'accion' => ['required', 'in:jugar,pasar,apostar,continuar,retirarse'],
            'cantidad' => ['nullable', 'numeric', 'min:1', 'max:2000'],
            'fase' => ['nullable', 'in:preflop,flop,turn,river'],
        ]);
        $action = $validated['accion'] === 'continuar' ? 'pasar' : $validated['accion'];

        $campaignId = $this->balances->campaignId($request->user(), 'poker_dealer');
        $hand = DB::transaction(function () use ($request, $validated, $action, $campaignId) {
            $hand = PokerDealerHand::where('usuario_id', $request->user()->id)
                ->where('campaign_challenge_id', $campaignId)->whereNotIn('fase', ['finalizada', 'retirada'])->lockForUpdate()->first();
            abort_unless($hand, 409, 'No tienes una partida activa.');
            abort_if(
                isset($validated['fase']) && $validated['fase'] !== $hand->fase,
                409,
                'La mano ya había avanzado. Recarga la mesa para recuperar su estado.'
            );

            if ($action === 'retirarse') {
                $this->finish($hand, 'retirada', 0, 'retirada');

                return $hand->fresh();
            }
            if ($hand->fase === 'preflop') {
                abort_unless($action === 'jugar', 422, 'Primero debes jugar la mano o retirarte.');
                abort_unless($this->balances->debit($request->user(), 'poker_dealer', $hand->ante, 'igualar_poker_dealer', ['fase' => 'preflop'], $hand, null, $hand->campaign_challenge_id), 422, 'Necesitas saldo para igualar el ante.');
                $hand->apostado += $hand->ante;
                $this->reveal($hand, 3, 'flop');
            } else {
                abort_unless(in_array($action, ['pasar', 'apostar'], true), 422, 'Debes pasar, apostar o retirarte.');

                if ($action === 'apostar') {
                    $amount = round((float) ($validated['cantidad'] ?? 0), 2);
                    abort_if($amount < 1, 422, 'Indica una apuesta válida para esta calle.');
                    abort_if($amount > round($hand->ante * 2, 2), 422, 'La apuesta de una calle no puede superar dos veces el ante.');
                    abort_unless(
                        $this->balances->debit($request->user(), 'poker_dealer', $amount, 'apuesta_poker_dealer', ['fase' => $hand->fase], $hand, null, $hand->campaign_challenge_id),
                        422,
                        'No tienes saldo suficiente para esa apuesta.'
                    );
                    $hand->apostado = round($hand->apostado + $amount, 2);
                }

                if ($hand->fase === 'flop') {
                    $this->reveal($hand, 1, 'turn');
                } elseif ($hand->fase === 'turn') {
                    $this->reveal($hand, 1, 'river');
                } else {
                    $this->showdown($hand);
                }
            }

            return $hand->fresh();
        });

        return response()->json($this->data($hand));
    }

    public function status(Request $request): JsonResponse
    {
        $validated = $request->validate(['hand_id' => ['nullable', 'integer']]);
        $campaignId = $this->balances->campaignId($request->user(), 'poker_dealer');
        $query = PokerDealerHand::where('usuario_id', $request->user()->id)
            ->where('campaign_challenge_id', $campaignId);

        if (! empty($validated['hand_id'])) {
            $query->whereKey($validated['hand_id']);
        } else {
            $query->whereNotIn('fase', ['finalizada', 'retirada']);
        }

        $hand = $query->latest()->first();
        abort_unless($hand, 404, 'No se encontró una mano para recuperar.');

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

    private function showdown(PokerDealerHand $hand): void
    {
        $player = $this->score([...$hand->mano_jugador, ...$hand->comunitarias]);
        $dealer = $this->score([...$hand->mano_dealer, ...$hand->comunitarias]);
        $result = $player['score'] > $dealer['score'] ? 'ganada' : ($player['score'] === $dealer['score'] ? 'empate' : 'perdida');
        $payout = $result === 'ganada'
            ? round($hand->apostado * self::WIN_PAYOUT_MULTIPLIER, 2)
            : ($result === 'empate' ? round($hand->apostado * self::TIE_PAYOUT_MULTIPLIER, 2) : 0);
        $this->finish($hand, $result, $payout, 'finalizada');
    }

    private function finish(PokerDealerHand $hand, string $result, float $payout, string $phase): void
    {
        if ($payout > 0) {
            $this->balances->credit(Usuario::findOrFail($hand->usuario_id), 'poker_dealer', $payout, 'premio_poker_dealer', ['resultado' => $result], $hand, $hand->campaign_challenge_id);
        }
        $hand->update(['fase' => $phase, 'resultado' => $result, 'ganancia' => $payout, 'finalizada_at' => now()]);
        $game = Partida::create([
            'usuario_id' => $hand->usuario_id, 'juego' => 'poker_dealer', 'apuesta' => $hand->apostado, 'ganancia' => $payout,
            'campaign_challenge_id' => $hand->campaign_challenge_id, 'campaign_key' => $hand->campaign_key,
            'detalles' => ['resultado' => $result, 'jugador' => $this->score([...$hand->mano_jugador, ...$hand->comunitarias])['name'], 'dealer' => $this->score([...$hand->mano_dealer, ...$hand->comunitarias])['name']],
        ]);
        $this->balances->recordGame($game);
    }

    private function data(PokerDealerHand $hand): array
    {
        $finished = in_array($hand->fase, ['finalizada', 'retirada'], true);
        $showdown = $hand->fase === 'finalizada';

        return [
            'id' => $hand->id, 'phase' => $hand->fase, 'player' => $hand->mano_jugador,
            'dealer' => $showdown ? $hand->mano_dealer : [['hidden' => true], ['hidden' => true]],
            'community' => $hand->comunitarias ?? [], 'ante' => $hand->ante, 'wagered' => $hand->apostado,
            'result' => $hand->resultado, 'winnings' => $hand->ganancia,
            'player_hand' => count($hand->comunitarias ?? []) >= 3 ? $this->score([...$hand->mano_jugador, ...$hand->comunitarias])['name'] : null,
            'dealer_hand' => $showdown ? $this->score([...$hand->mano_dealer, ...$hand->comunitarias])['name'] : null,
            'can_bet' => ! $finished && $hand->fase !== 'preflop',
            'balance' => $this->balances->balance(Usuario::findOrFail($hand->usuario_id), 'poker_dealer', $hand->campaign_challenge_id),
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
