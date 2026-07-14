<?php

namespace App\Http\Controllers;

use App\Models\Partida;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SlotsController extends Controller
{
    private array $symbols = ['🍒', '🍋', '🍊', '🍇', '💎', '⭐', '7️⃣', '🔔'];

    private array $weights = [
        '🍒' => 25,
        '🍋' => 25,
        '🍊' => 20,
        '🍇' => 15,
        '💎' => 8,
        '⭐' => 5,
        '7️⃣' => 1,
        '🔔' => 1,
    ];

    public function index()
    {
        $partidas = Partida::where('usuario_id', Auth::id())
            ->where('juego', 'slots')
            ->latest()
            ->take(10)
            ->get();

        return view('games.slots', ['partidas' => $partidas]);
    }

    public function play(Request $request)
    {
        $request->validate([
            'apuesta' => 'required|numeric|min:0.10|max:500',
        ]);

        $user = Auth::user();
        $apuesta = round($request->apuesta, 2);

        if (!$user->cartera->apostar($apuesta)) {
            return back()->withErrors(['apuesta' => 'Saldo insuficiente.']);
        }

        $reels = [
            $this->spin(),
            $this->spin(),
            $this->spin(),
        ];

        $ganancia = $this->calculateWin($reels, $apuesta);
        $resultado = $ganancia > 0 ? 'win' : 'lose';

        if ($ganancia > 0) {
            $user->cartera->ganar($ganancia);
        }

        Partida::create([
            'usuario_id' => $user->id,
            'juego' => 'slots',
            'apuesta' => $apuesta,
            'ganancia' => $ganancia,
            'detalles' => [
                'reels' => $reels,
                'resultado' => $resultado,
            ],
        ]);

        return response()->json([
            'reels' => $reels,
            'ganancia' => $ganancia,
            'resultado' => $resultado,
            'saldo' => $user->cartera->saldo,
        ]);
    }

    private function spin(): string
    {
        $totalWeight = array_sum($this->weights);
        $rand = random_int(1, $totalWeight);
        $accum = 0;

        foreach ($this->weights as $symbol => $weight) {
            $accum += $weight;
            if ($rand <= $accum) {
                return $symbol;
            }
        }

        return $this->symbols[0];
    }

    private function calculateWin(array $reels, float $apuesta): float
    {
        if ($reels[0] === $reels[1] && $reels[1] === $reels[2]) {
            return match ($reels[0]) {
                '7️⃣' => $apuesta * 50,
                '💎' => $apuesta * 25,
                '⭐' => $apuesta * 15,
                '🔔' => $apuesta * 20,
                default => $apuesta * 10,
            };
        }

        if ($reels[0] === $reels[1] || $reels[1] === $reels[2]) {
            return $apuesta * 2;
        }

        return 0;
    }
}
