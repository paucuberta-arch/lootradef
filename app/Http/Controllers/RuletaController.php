<?php

namespace App\Http\Controllers;

use App\Models\Partida;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RuletaController extends Controller
{
    private array $colores = [
        0 => 'verde',
        1 => 'rojo', 2 => 'negro', 3 => 'rojo', 4 => 'negro', 5 => 'rojo', 6 => 'negro',
        7 => 'rojo', 8 => 'negro', 9 => 'rojo', 10 => 'negro', 11 => 'rojo', 12 => 'negro',
        13 => 'rojo', 14 => 'negro', 15 => 'rojo', 16 => 'negro', 17 => 'rojo', 18 => 'negro',
        19 => 'rojo', 20 => 'negro', 21 => 'rojo', 22 => 'negro', 23 => 'rojo', 24 => 'negro',
        25 => 'rojo', 26 => 'negro', 27 => 'rojo', 28 => 'negro', 29 => 'negro', 30 => 'rojo',
        31 => 'negro', 32 => 'rojo', 33 => 'negro', 34 => 'rojo', 35 => 'negro', 36 => 'rojo',
    ];

    public function index()
    {
        $partidas = Partida::where('usuario_id', Auth::id())
            ->where('juego', 'ruleta')
            ->latest()
            ->take(10)
            ->get();

        return view('games.ruleta', ['partidas' => $partidas]);
    }

    public function play(Request $request)
    {
        $request->validate([
            'apuesta' => 'required|numeric|min:0.10|max:500',
            'tipo' => 'required|string|in:numero,rojo,negro,par,impar,docena1,docena2,docena3',
            'valor' => 'nullable|integer|min:0|max:36',
        ]);

        $user = Auth::user();
        $apuesta = round($request->apuesta, 2);

        if (!$user->cartera || !$user->cartera->apostar($apuesta)) {
            return response()->json(['error' => 'Saldo insuficiente.'], 422);
        }

        $numero = random_int(0, 36);
        $color = $this->colores[$numero];

        $ganancia = $this->calculateWin($request->tipo, $request->valor, $numero, $color, $apuesta);
        $resultado = $ganancia > 0 ? 'win' : 'lose';

        if ($ganancia > 0) {
            $user->cartera->ganar($ganancia);
        }

        Partida::create([
            'usuario_id' => $user->id,
            'juego' => 'ruleta',
            'apuesta' => $apuesta,
            'ganancia' => $ganancia,
            'detalles' => [
                'numero' => $numero,
                'color' => $color,
                'tipo_apuesta' => $request->tipo,
                'valor_apuesta' => $request->valor,
                'resultado' => $resultado,
            ],
        ]);

        return response()->json([
            'numero' => $numero,
            'color' => $color,
            'ganancia' => $ganancia,
            'resultado' => $resultado,
            'saldo' => $user->cartera?->saldo ?? 0,
        ]);
    }

    private function calculateWin(string $tipo, ?int $valor, int $numero, string $color, float $apuesta): float
    {
        $ganado = match ($tipo) {
            'numero' => $numero === $valor ? $apuesta * 35 : 0,
            'rojo' => $color === 'rojo' ? $apuesta * 2 : 0,
            'negro' => $color === 'negro' ? $apuesta * 2 : 0,
            'par' => $numero !== 0 && $numero % 2 === 0 ? $apuesta * 2 : 0,
            'impar' => $numero !== 0 && $numero % 2 !== 0 ? $apuesta * 2 : 0,
            'docena1' => $numero >= 1 && $numero <= 12 ? $apuesta * 3 : 0,
            'docena2' => $numero >= 13 && $numero <= 24 ? $apuesta * 3 : 0,
            'docena3' => $numero >= 25 && $numero <= 36 ? $apuesta * 3 : 0,
            default => 0,
        };

        return round($ganado, 2);
    }
}
