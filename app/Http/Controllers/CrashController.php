<?php

namespace App\Http\Controllers;

use App\Models\Partida;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

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

        if (!$user->cartera || !$user->cartera->apostar($apuesta)) {
            return response()->json(['error' => 'Saldo insuficiente.'], 422);
        }

        $crashPoint = $this->generateCrashPoint();

        Session::put('crash_round', [
            'crash_point' => $crashPoint,
            'apuesta' => $apuesta,
            'started_at' => now()->timestamp,
        ]);

        return response()->json([
            'ok' => true,
            'saldo' => $user->cartera?->saldo ?? 0,
            'crash_point' => $crashPoint,
        ]);
    }

    public function cashout(Request $request)
    {
        $request->validate([
            'multiplier' => 'required|numeric|min:1.01',
        ]);

        $user = Auth::user();
        $round = Session::get('crash_round');

        if (!$round) {
            return response()->json([
                'error' => 'No hay ronda activa.',
                'crash_point' => 0,
                'ganancia' => 0,
                'resultado' => 'no_round',
                'saldo' => $user->cartera?->saldo ?? 0,
            ], 422);
        }

        Session::forget('crash_round');

        $crashPoint = $round['crash_point'];
        $apuesta = $round['apuesta'];
        $multiplier = round($request->multiplier, 2);

        if ($multiplier >= $crashPoint) {
            $ganancia = 0;
            $resultado = 'crash';
        } else {
            $ganancia = round($apuesta * $multiplier, 2);
            $resultado = 'cobrado';
            $user->cartera->ganar($ganancia);
        }

        Partida::create([
            'usuario_id' => $user->id,
            'juego' => 'crash',
            'apuesta' => $apuesta,
            'ganancia' => $ganancia,
            'detalles' => [
                'crash_point' => $crashPoint,
                'cashout_at' => $multiplier,
                'resultado' => $resultado,
            ],
        ]);

        return response()->json([
            'crash_point' => $crashPoint,
            'ganancia' => $ganancia,
            'resultado' => $resultado,
            'saldo' => $user->cartera?->saldo ?? 0,
        ]);
    }

    public function crash(Request $request)
    {
        $user = Auth::user();
        $round = Session::get('crash_round');

        if (!$round) {
            return response()->json([
                'crash_point' => 0,
                'ganancia' => 0,
                'resultado' => 'no_round',
                'saldo' => $user->cartera?->saldo ?? 0,
            ]);
        }

        Session::forget('crash_round');

        $apuesta = $round['apuesta'];
        $crashPoint = $round['crash_point'];

        Partida::create([
            'usuario_id' => $user->id,
            'juego' => 'crash',
            'apuesta' => $apuesta,
            'ganancia' => 0,
            'detalles' => [
                'crash_point' => $crashPoint,
                'cashout_at' => null,
                'resultado' => 'crash',
            ],
        ]);

        return response()->json([
            'crash_point' => $crashPoint,
            'ganancia' => 0,
            'resultado' => 'crash',
            'saldo' => $user->cartera?->saldo ?? 0,
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
