<?php

namespace App\Http\Controllers;

use App\Models\Partida;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BlackjackController extends Controller
{
    private array $palos = ['♠', '♥', '♦', '♣'];
    private array $valores = ['A', '2', '3', '4', '5', '6', '7', '8', '9', '10', 'J', 'Q', 'K'];

    public function index()
    {
        $partidas = Partida::where('usuario_id', Auth::id())
            ->where('juego', 'blackjack')
            ->latest()
            ->take(10)
            ->get();

        return view('games.blackjack', ['partidas' => $partidas]);
    }

    public function deal(Request $request)
    {
        $request->validate([
            'apuesta' => 'required|numeric|min:1|max:5000',
        ]);

        $user = Auth::user();
        $apuesta = round($request->apuesta, 2);

        if (!$user->cartera->apostar($apuesta)) {
            return back()->withErrors(['apuesta' => 'Saldo insuficiente.']);
        }

        $baraja = $this->crearBaraja();
        shuffle($baraja);

        $manoJugador = [$this->draw($baraja), $this->draw($baraja)];
        $manoDealer = [$this->draw($baraja), $this->draw($baraja)];

        $jugadorPts = $this->calcularPuntos($manoJugador);
        $dealerPts = $this->calcularPuntos($manoDealer);

        $blackjackJugador = $jugadorPts === 21 && count($manoJugador) === 2;
        $blackjackDealer = $dealerPts === 21 && count($manoDealer) === 2;

        $ganancia = 0;
        $estado = 'jugando';

        if ($blackjackJugador && !$blackjackDealer) {
            $ganancia = round($apuesta * 2.5, 2);
            $estado = 'blackjack';
            $user->cartera->ganar($ganancia);
        } elseif ($blackjackJugador && $blackjackDealer) {
            $ganancia = $apuesta;
            $estado = 'push';
            $user->cartera->ganar($ganancia);
        } elseif ($jugadorPts > 21) {
            $ganancia = 0;
            $estado = 'bust';
        }

        return response()->json([
            'mano_jugador' => $manoJugador,
            'mano_dealer' => $manoDealer,
            'puntos_jugador' => $jugadorPts,
            'puntos_dealer' => $dealerPts,
            'dealer_visible' => [$manoDealer[0]],
            'estado' => $estado,
            'ganancia' => $ganancia,
            'saldo' => $user->cartera->saldo,
            'apuesta' => $apuesta,
            'baraja' => $baraja,
        ]);
    }

    public function hit(Request $request)
    {
        $request->validate([
            'baraja' => 'required|array',
            'mano_jugador' => 'required|array',
            'apuesta' => 'required|numeric',
        ]);

        $baraja = $request->baraja;
        $manoJugador = $request->mano_jugador;
        $apuesta = $request->apuesta;

        $manoJugador[] = $this->draw($baraja);
        $puntos = $this->calcularPuntos($manoJugador);

        $estado = 'jugando';
        $ganancia = 0;

        if ($puntos > 21) {
            $estado = 'bust';
        } elseif ($puntos === 21) {
            return $this->stand($request->merge(['mano_jugador' => $manoJugador, 'baraja' => $baraja]));
        }

        return response()->json([
            'mano_jugador' => $manoJugador,
            'puntos_jugador' => $puntos,
            'baraja' => $baraja,
            'estado' => $estado,
        ]);
    }

    public function stand(Request $request)
    {
        $request->validate([
            'mano_jugador' => 'required|array',
            'baraja' => 'required|array',
            'apuesta' => 'required|numeric',
        ]);

        $user = Auth::user();
        $manoJugador = $request->mano_jugador;
        $baraja = $request->baraja;
        $apuesta = $request->apuesta;

        $manoDealer = $request->mano_dealer ?? [];
        if (empty($manoDealer)) {
            $manoDealer = [$this->draw($baraja), $this->draw($baraja)];
        }

        while ($this->calcularPuntos($manoDealer) < 17) {
            $manoDealer[] = $this->draw($baraja);
        }

        $jugadorPts = $this->calcularPuntos($manoJugador);
        $dealerPts = $this->calcularPuntos($manoDealer);

        if ($dealerPts > 21) {
            $ganancia = round($apuesta * 2, 2);
            $estado = 'win';
        } elseif ($jugadorPts > $dealerPts) {
            $ganancia = round($apuesta * 2, 2);
            $estado = 'win';
        } elseif ($jugadorPts === $dealerPts) {
            $ganancia = $apuesta;
            $estado = 'push';
        } else {
            $ganancia = 0;
            $estado = 'lose';
        }

        if ($ganancia > 0) {
            $user->cartera->ganar($ganancia);
        }

        Partida::create([
            'usuario_id' => $user->id,
            'juego' => 'blackjack',
            'apuesta' => $apuesta,
            'ganancia' => $ganancia,
            'detalles' => [
                'mano_jugador' => $manoJugador,
                'mano_dealer' => $manoDealer,
                'puntos_jugador' => $jugadorPts,
                'puntos_dealer' => $dealerPts,
                'resultado' => $estado,
            ],
        ]);

        return response()->json([
            'mano_jugador' => $manoJugador,
            'mano_dealer' => $manoDealer,
            'puntos_jugador' => $jugadorPts,
            'puntos_dealer' => $dealerPts,
            'estado' => $estado,
            'ganancia' => $ganancia,
            'saldo' => $user->cartera->saldo,
        ]);
    }

    private function crearBaraja(): array
    {
        $baraja = [];
        foreach ($this->palos as $palo) {
            foreach ($this->valores as $valor) {
                $baraja[] = ['palo' => $palo, 'valor' => $valor];
            }
        }
        return $baraja;
    }

    private function draw(array &$baraja): array
    {
        return array_pop($baraja);
    }

    private function calcularPuntos(array $mano): int
    {
        $puntos = 0;
        $ases = 0;

        foreach ($mano as $carta) {
            $valor = $carta['valor'];
            if (in_array($valor, ['J', 'Q', 'K'])) {
                $puntos += 10;
            } elseif ($valor === 'A') {
                $puntos += 11;
                $ases++;
            } else {
                $puntos += (int) $valor;
            }
        }

        while ($puntos > 21 && $ases > 0) {
            $puntos -= 10;
            $ases--;
        }

        return $puntos;
    }
}
