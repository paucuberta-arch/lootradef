<?php

namespace App\Http\Controllers;

use App\Models\Partida;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CrashController extends Controller
{
    public function index()
    {
        $partidas = Partida::where('usuario_id', Auth::id())
            ->where('juego', 'crash')
            ->latest()
            ->take(10)
            ->get();

        return view('games.crash', ['partidas' => $partidas]);
    }

    public function play(Request $request)
    {
        $request->validate([
            'apuesta' => 'required|numeric|min:0.10|max:1000',
        ]);

        $user = Auth::user();
        $apuesta = round($request->apuesta, 2);

        if (!$user->cartera->apostar($apuesta)) {
            return back()->withErrors(['apuesta' => 'Saldo insuficiente.']);
        }

        $crashPoint = $this->generateCrashPoint();
        $cashoutAt = $request->cashout_at ? (float) $request->cashout_at : null;

        if ($cashoutAt && $cashoutAt >= $crashPoint) {
            $ganancia = 0;
            $resultado = 'crash';
        } elseif ($cashoutAt && $cashoutAt > 1) {
            $ganancia = round($apuesta * $cashoutAt, 2);
            $resultado = 'cobrado';
            $user->cartera->ganar($ganancia);
        } else {
            $ganancia = 0;
            $resultado = 'crash';
        }

        Partida::create([
            'usuario_id' => $user->id,
            'juego' => 'crash',
            'apuesta' => $apuesta,
            'ganancia' => $ganancia,
            'detalles' => [
                'crash_point' => $crashPoint,
                'cashout_at' => $cashoutAt,
                'resultado' => $resultado,
            ],
        ]);

        return response()->json([
            'crash_point' => $crashPoint,
            'cashout_at' => $cashoutAt,
            'ganancia' => $ganancia,
            'resultado' => $resultado,
            'saldo' => $user->cartera->saldo,
        ]);
    }

    private function generateCrashPoint(): float
    {
        $houseEdge = 0.03;
        $max = 1000000;
        $e = 2 ** 32;
        $h = $e * (1 - $houseEdge);

        if ($h <= 0) return 1.0;

        $r = random_int(1, $e - 1);

        if ($r >= $h) return 1.0;

        return round($e / ($e - $r), 2);
    }
}
