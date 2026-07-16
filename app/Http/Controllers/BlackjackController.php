<?php

namespace App\Http\Controllers;

use App\Models\BlackjackHand;
use App\Models\Cartera;
use App\Models\Partida;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class BlackjackController extends Controller
{
    private array $palos = ['♠', '♥', '♦', '♣'];

    private array $valores = ['A', '2', '3', '4', '5', '6', '7', '8', '9', '10', 'J', 'Q', 'K'];

    public function index(string $variant = 'vip'): View
    {
        $this->validateVariant($variant);
        $partidas = Partida::where('usuario_id', Auth::id())->where('juego', 'blackjack_'.$variant)->latest()->take(10)->get();
        $active = BlackjackHand::where('usuario_id', Auth::id())->where('variante', $variant)->where('estado', 'jugando')->latest()->first();

        return view('games.blackjack', [
            'partidas' => $partidas,
            'variant' => $variant,
            'gameName' => $variant === 'vip' ? 'Blackjack VIP' : 'Blackjack Classic',
            'dealRoute' => $variant === 'vip' ? route('games.blackjack.vip.deal') : route('games.blackjack.classic.deal'),
            'hitRoute' => $variant === 'vip' ? route('games.blackjack.vip.hit') : route('games.blackjack.classic.hit'),
            'standRoute' => $variant === 'vip' ? route('games.blackjack.vip.stand') : route('games.blackjack.classic.stand'),
            'activeHand' => $active ? $this->handData($active) : null,
        ]);
    }

    public function deal(Request $request, string $variant = 'vip'): JsonResponse
    {
        $this->validateVariant($variant);
        $validated = $request->validate(['apuesta' => [
            'required', 'numeric', $variant === 'vip' ? 'min:5' : 'min:1', $variant === 'vip' ? 'max:5000' : 'max:2000',
        ]]);

        $hand = DB::transaction(function () use ($request, $variant, $validated) {
            if (BlackjackHand::where('usuario_id', $request->user()->id)->where('variante', $variant)->where('estado', 'jugando')->lockForUpdate()->exists()) {
                abort(409, 'Ya tienes una mano en juego. Termínala antes de volver a repartir.');
            }

            $wallet = Cartera::where('usuario_id', $request->user()->id)->lockForUpdate()->first();
            $bet = round((float) $validated['apuesta'], 2);
            if (! $wallet || $wallet->saldo < $bet) {
                abort(422, 'Saldo insuficiente.');
            }
            $deck = $this->crearBaraja();
            shuffle($deck);
            $player = [$this->draw($deck), $this->draw($deck)];
            $dealer = [$this->draw($deck), $this->draw($deck)];
            $hand = BlackjackHand::create([
                'usuario_id' => $request->user()->id, 'variante' => $variant, 'apuesta' => $bet,
                'baraja' => $deck, 'mano_jugador' => $player, 'mano_dealer' => $dealer,
            ]);
            abort_unless($wallet->apostar($bet, 'apuesta_blackjack', ['variante' => $variant], $hand), 422, 'Saldo insuficiente.');

            $playerBlackjack = $this->calcularPuntos($player) === 21;
            $dealerBlackjack = $this->calcularPuntos($dealer) === 21;
            if ($playerBlackjack || $dealerBlackjack) {
                $state = $playerBlackjack && $dealerBlackjack ? 'push' : ($playerBlackjack ? 'blackjack' : 'lose');
                $payout = $state === 'push' ? $bet : ($state === 'blackjack' ? round($bet * 2.5, 2) : 0);
                $this->finish($hand, $state, $payout, $wallet);
            }

            return $hand->fresh();
        });

        return response()->json($this->handData($hand, true));
    }

    public function hit(Request $request, string $variant = 'vip'): JsonResponse
    {
        $this->validateVariant($variant);

        $hand = DB::transaction(function () use ($request, $variant) {
            $hand = $this->activeHand($request->user()->id, $variant);
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
        $hand = DB::transaction(function () use ($request, $variant) {
            $hand = $this->activeHand($request->user()->id, $variant);
            $this->resolveDealer($hand);

            return $hand->fresh();
        });

        return response()->json($this->handData($hand, true));
    }

    private function activeHand(int $userId, string $variant): BlackjackHand
    {
        $hand = BlackjackHand::where('usuario_id', $userId)->where('variante', $variant)->where('estado', 'jugando')->lockForUpdate()->first();
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

    private function finish(BlackjackHand $hand, string $state, float $payout, ?Cartera $wallet = null): void
    {
        abort_unless($hand->estado === 'jugando', 409, 'Esta mano ya está finalizada.');
        if ($payout > 0) {
            ($wallet ?? Cartera::where('usuario_id', $hand->usuario_id)->lockForUpdate()->firstOrFail())
                ->ganar($payout, 'premio_blackjack', ['resultado' => $state], $hand);
        }
        $hand->update(['estado' => $state, 'ganancia' => $payout, 'finalizada_at' => now()]);
        Partida::create([
            'usuario_id' => $hand->usuario_id, 'juego' => 'blackjack_'.$hand->variante,
            'apuesta' => $hand->apuesta, 'ganancia' => $payout,
            'detalles' => [
                'mano_jugador' => $hand->mano_jugador, 'mano_dealer' => $hand->mano_dealer,
                'puntos_jugador' => $this->calcularPuntos($hand->mano_jugador),
                'puntos_dealer' => $this->calcularPuntos($hand->mano_dealer), 'resultado' => $state,
            ],
        ]);
    }

    private function handData(BlackjackHand $hand, bool $revealDealer = false): array
    {
        $finished = $hand->estado !== 'jugando';

        return [
            'mano_jugador' => $hand->mano_jugador,
            'mano_dealer' => $revealDealer || $finished ? $hand->mano_dealer : [$hand->mano_dealer[0], ['oculta' => true]],
            'puntos_jugador' => $this->calcularPuntos($hand->mano_jugador),
            'puntos_dealer' => $revealDealer || $finished ? $this->calcularPuntos($hand->mano_dealer) : $this->calcularPuntos([$hand->mano_dealer[0]]),
            'estado' => $hand->estado, 'ganancia' => $hand->ganancia, 'apuesta' => $hand->apuesta,
            'saldo' => (float) (Cartera::where('usuario_id', $hand->usuario_id)->value('saldo') ?? 0),
        ];
    }

    private function validateVariant(string $variant): void
    {
        abort_unless(in_array($variant, ['vip', 'classic'], true), 404);
    }

    private function crearBaraja(): array
    {
        $deck = [];
        foreach ($this->palos as $suit) {
            foreach ($this->valores as $value) {
                $deck[] = ['palo' => $suit, 'valor' => $value];
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
