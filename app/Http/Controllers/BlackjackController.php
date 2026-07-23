<?php

namespace App\Http\Controllers;

use App\Models\BlackjackHand;
use App\Models\Partida;
use App\Models\Usuario;
use App\Services\CampaignManager;
use App\Services\GameBalanceService;
use App\Services\SecureRandom;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class BlackjackController extends Controller
{
    private const BLACKJACK_PAYOUT_MULTIPLIER = 2.4;

    public function __construct(
        private readonly GameBalanceService $balances,
        private readonly SecureRandom $random,
    ) {}

    private array $palos = ['♠', '♥', '♦', '♣'];

    private array $valores = ['A', '2', '3', '4', '5', '6', '7', '8', '9', '10', 'J', 'Q', 'K'];

    public function index(string $variant = 'vip'): View
    {
        $this->validateVariant($variant);
        $campaignId = $this->balances->campaignId(Auth::user(), 'blackjack_'.$variant);
        $partidas = Partida::where('usuario_id', Auth::id())->where('juego', 'blackjack_'.$variant)->latest()->take(10)->get();
        $active = BlackjackHand::where('usuario_id', Auth::id())->where('variante', $variant)
            ->where('campaign_challenge_id', $campaignId)->where('estado', 'jugando')->latest()->first();

        return view('games.blackjack', [
            'partidas' => $partidas,
            'variant' => $variant,
            'gameName' => $variant === 'vip' ? 'Blackjack VIP' : 'Blackjack Classic',
            'dealRoute' => $variant === 'vip' ? route('games.blackjack.vip.deal') : route('games.blackjack.classic.deal'),
            'hitRoute' => $variant === 'vip' ? route('games.blackjack.vip.hit') : route('games.blackjack.classic.hit'),
            'standRoute' => $variant === 'vip' ? route('games.blackjack.vip.stand') : route('games.blackjack.classic.stand'),
            'statusRoute' => $variant === 'vip' ? route('games.blackjack.vip.status') : route('games.blackjack.classic.status'),
            'activeHand' => $active ? $this->handData($active) : null,
            'blackjackPayout' => self::BLACKJACK_PAYOUT_MULTIPLIER,
            'blackjackRtp' => $variant === 'classic' ? '≈99.4%' : '≈98.8%',
            'gameBalance' => $this->balances->balance(Auth::user(), 'blackjack_'.$variant, $campaignId),
        ]);
    }

    public function deal(Request $request, string $variant = 'vip'): JsonResponse
    {
        $this->validateVariant($variant);
        $validated = $request->validate([
            'apuesta' => [
                'required', 'numeric', $variant === 'vip' ? 'min:5' : 'min:1', $variant === 'vip' ? 'max:5000' : 'max:2000',
            ],
            'request_token' => ['required', 'uuid'],
        ]);

        $campaignId = $this->balances->campaignId($request->user(), 'blackjack_'.$variant);
        $result = DB::transaction(function () use ($request, $variant, $validated, $campaignId) {
            $existing = BlackjackHand::where('usuario_id', $request->user()->id)
                ->where('variante', $variant)
                ->where('request_token', $validated['request_token'])
                ->first();

            if ($existing) {
                return ['hand' => $existing, 'conflict' => false];
            }

            $active = BlackjackHand::where('usuario_id', $request->user()->id)
                ->where('variante', $variant)
                ->where('campaign_challenge_id', $campaignId)
                ->where('estado', 'jugando')
                ->lockForUpdate()
                ->first();
            if ($active) {
                return ['hand' => $active, 'conflict' => true];
            }

            $bet = round((float) $validated['apuesta'], 2);
            $deck = $this->crearBaraja($variant === 'classic' ? 6 : 1);
            $deck = $this->random->shuffle($deck);
            $player = [$this->draw($deck), $this->draw($deck)];
            $dealer = [$this->draw($deck), $this->draw($deck)];
            $hand = BlackjackHand::create([
                'usuario_id' => $request->user()->id, 'variante' => $variant, 'request_token' => $validated['request_token'], 'apuesta' => $bet,
                'baraja' => $deck, 'mano_jugador' => $player, 'mano_dealer' => $dealer,
                'estado' => 'jugando', 'ganancia' => 0,
                'campaign_challenge_id' => $campaignId, 'campaign_key' => $campaignId ? CampaignManager::KEY : null,
            ]);
            abort_unless($this->balances->debit($request->user(), 'blackjack_'.$variant, $bet, 'apuesta_blackjack', ['variante' => $variant], $hand, $validated['request_token'], $campaignId), 422, 'Saldo insuficiente.');

            $playerBlackjack = $this->calcularPuntos($player) === 21;
            $dealerBlackjack = $this->calcularPuntos($dealer) === 21;
            if ($playerBlackjack || $dealerBlackjack) {
                $state = $playerBlackjack && $dealerBlackjack ? 'push' : ($playerBlackjack ? 'blackjack' : 'lose');
                $payout = $state === 'push' ? $bet : ($state === 'blackjack' ? round($bet * self::BLACKJACK_PAYOUT_MULTIPLIER, 2) : 0);
                $this->finish($hand, $state, $payout);
            }

            return ['hand' => $hand->fresh(), 'conflict' => false];
        });

        if ($result['conflict']) {
            return response()->json([
                'message' => 'Ya tienes una mano en juego. La hemos recuperado para que puedas terminarla.',
                'active_hand' => $this->handData($result['hand']),
            ], 409);
        }

        return response()->json($this->handData($result['hand'], $result['hand']->estado !== 'jugando'));
    }

    public function hit(Request $request, string $variant = 'vip'): JsonResponse
    {
        $this->validateVariant($variant);
        $campaignId = $this->balances->campaignId($request->user(), 'blackjack_'.$variant);

        $hand = DB::transaction(function () use ($request, $variant, $campaignId) {
            $hand = $this->activeHand($request->user()->id, $variant, $campaignId);
            $deck = $hand->baraja;
            $player = $hand->mano_jugador;
            $player[] = $this->draw($deck);
            $hand->update(['baraja' => $deck, 'mano_jugador' => $player]);
            $points = $this->calcularPuntos($player);
            if ($points > 21) {
                $this->finish($hand, 'bust', 0);
            } elseif ($points === 21) {
                $this->resolveDealer($hand);
            }

            return $hand->fresh();
        });

        return response()->json($this->handData($hand, $hand->estado !== 'jugando'));
    }

    public function stand(Request $request, string $variant = 'vip'): JsonResponse
    {
        $this->validateVariant($variant);
        $campaignId = $this->balances->campaignId($request->user(), 'blackjack_'.$variant);
        $hand = DB::transaction(function () use ($request, $variant, $campaignId) {
            $hand = $this->activeHand($request->user()->id, $variant, $campaignId);
            $this->resolveDealer($hand);

            return $hand->fresh();
        });

        return response()->json($this->handData($hand, true));
    }

    public function status(Request $request, string $variant = 'vip'): JsonResponse
    {
        $this->validateVariant($variant);
        $campaignId = $this->balances->campaignId($request->user(), 'blackjack_'.$variant);
        $validated = $request->validate([
            'hand_id' => ['nullable', 'integer'],
            'request_token' => ['nullable', 'uuid'],
        ]);

        $query = BlackjackHand::where('usuario_id', $request->user()->id)->where('variante', $variant)
            ->where('campaign_challenge_id', $campaignId);
        if (! empty($validated['hand_id'])) {
            $query->whereKey($validated['hand_id']);
        } elseif (! empty($validated['request_token'])) {
            $query->where('request_token', $validated['request_token']);
        } else {
            $query->where('estado', 'jugando');
        }

        $hand = $query->latest()->first();
        abort_unless($hand, 404, 'No se encontró la mano solicitada.');

        return response()->json($this->handData($hand, $hand->estado !== 'jugando'));
    }

    private function activeHand(int $userId, string $variant, ?int $campaignId): BlackjackHand
    {
        $hand = BlackjackHand::where('usuario_id', $userId)->where('variante', $variant)
            ->where('campaign_challenge_id', $campaignId)->where('estado', 'jugando')->lockForUpdate()->first();
        abort_unless($hand, 409, 'No hay ninguna mano activa. Reparte una nueva mano.');

        return $hand;
    }

    private function resolveDealer(BlackjackHand $hand): void
    {
        $deck = $hand->baraja;
        $dealer = $hand->mano_dealer;
        while ($this->calcularPuntos($dealer) < 17) {
            $dealer[] = $this->draw($deck);
        }
        $hand->update(['baraja' => $deck, 'mano_dealer' => $dealer]);
        $playerPoints = $this->calcularPuntos($hand->mano_jugador);
        $dealerPoints = $this->calcularPuntos($dealer);
        $state = $dealerPoints > 21 || $playerPoints > $dealerPoints ? 'win' : ($playerPoints === $dealerPoints ? 'push' : 'lose');
        $payout = $state === 'win' ? round($hand->apuesta * 2, 2) : ($state === 'push' ? $hand->apuesta : 0);
        $this->finish($hand, $state, $payout);
    }

    private function finish(BlackjackHand $hand, string $state, float $payout): void
    {
        abort_unless($hand->estado === 'jugando', 409, 'Esta mano ya está finalizada.');
        if ($payout > 0) {
            $this->balances->credit(
                Usuario::findOrFail($hand->usuario_id), 'blackjack_'.$hand->variante, $payout,
                'premio_blackjack', ['resultado' => $state], $hand, $hand->campaign_challenge_id
            );
        }
        $hand->update(['estado' => $state, 'ganancia' => $payout, 'finalizada_at' => now()]);
        $game = Partida::create([
            'usuario_id' => $hand->usuario_id, 'juego' => 'blackjack_'.$hand->variante,
            'apuesta' => $hand->apuesta, 'ganancia' => $payout,
            'campaign_challenge_id' => $hand->campaign_challenge_id, 'campaign_key' => $hand->campaign_key,
            'detalles' => [
                'mano_jugador' => $hand->mano_jugador, 'mano_dealer' => $hand->mano_dealer,
                'puntos_jugador' => $this->calcularPuntos($hand->mano_jugador),
                'puntos_dealer' => $this->calcularPuntos($hand->mano_dealer), 'resultado' => $state,
            ],
        ]);
        $this->balances->recordGame($game);
    }

    private function handData(BlackjackHand $hand, bool $revealDealer = false): array
    {
        $finished = $hand->estado !== 'jugando';

        return [
            'id' => $hand->id,
            'mano_jugador' => $hand->mano_jugador,
            'mano_dealer' => $revealDealer || $finished ? $hand->mano_dealer : [$hand->mano_dealer[0], ['oculta' => true]],
            'puntos_jugador' => $this->calcularPuntos($hand->mano_jugador),
            'puntos_dealer' => $revealDealer || $finished ? $this->calcularPuntos($hand->mano_dealer) : $this->calcularPuntos([$hand->mano_dealer[0]]),
            'estado' => $hand->estado, 'ganancia' => $hand->ganancia, 'apuesta' => $hand->apuesta,
            'saldo' => $this->balances->balance(Usuario::findOrFail($hand->usuario_id), 'blackjack_'.$hand->variante, $hand->campaign_challenge_id),
        ];
    }

    private function validateVariant(string $variant): void
    {
        abort_unless(in_array($variant, ['vip', 'classic'], true), 404);
    }

    private function crearBaraja(int $decks = 1): array
    {
        $deck = [];
        for ($copy = 0; $copy < $decks; $copy++) {
            foreach ($this->palos as $suit) {
                foreach ($this->valores as $value) {
                    $deck[] = ['palo' => $suit, 'valor' => $value];
                }
            }
        }

        return $deck;
    }

    private function draw(array &$deck): array
    {
        return array_pop($deck);
    }

    private function calcularPuntos(array $hand): int
    {
        $points = 0;
        $aces = 0;
        foreach ($hand as $card) {
            if (in_array($card['valor'], ['J', 'Q', 'K'], true)) {
                $points += 10;
            } elseif ($card['valor'] === 'A') {
                $points += 11;
                $aces++;
            } else {
                $points += (int) $card['valor'];
            }
        }
        while ($points > 21 && $aces-- > 0) {
            $points -= 10;
        }

        return $points;
    }
}
