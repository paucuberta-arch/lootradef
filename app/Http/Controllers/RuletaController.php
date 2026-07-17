<?php

namespace App\Http\Controllers;

use App\Models\Partida;
use App\Services\CampaignManager;
use App\Services\GameBalanceService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class RuletaController extends Controller
{
    public function __construct(private readonly GameBalanceService $balances) {}

    public function index(string $variant = 'european')
    {
        abort_unless(in_array($variant, ['european', 'lightning'], true), 404);

        $partidas = Partida::where('usuario_id', Auth::id())
            ->where('juego', 'ruleta_'.$variant)
            ->latest()
            ->take(10)
            ->get();

        return view('games.ruleta', [
            'partidas' => $partidas,
            'variant' => $variant,
            'gameName' => $variant === 'lightning' ? 'Lightning Roulette' : 'Ruleta Europea',
            'playRoute' => $variant === 'lightning' ? route('games.roulette.lightning.play') : route('games.roulette.european.play'),
            'rouletteConfig' => config('roulette'),
            'gameBalance' => $this->balances->balance(Auth::user(), 'ruleta_'.$variant),
        ]);
    }

    public function play(Request $request, string $variant = 'european')
    {
        abort_unless(in_array($variant, ['european', 'lightning'], true), 404);
        if (! $request->filled('request_token')) {
            $request->merge(['request_token' => (string) Str::uuid()]);
        }
        $request->validate([
            'apuesta' => 'required|numeric|min:0.10|max:500',
            'tipo' => 'required|string|in:numero,rojo,negro,par,impar,docena1,docena2,docena3',
            'valor' => 'nullable|integer|min:0|max:36',
            'request_token' => 'required|uuid',
        ]);

        $user = Auth::user();
        $apuesta = round($request->apuesta, 2);

        $gameKey = 'ruleta_'.$variant;
        $round = DB::transaction(function () use ($request, $user, $apuesta, $variant, $gameKey) {
            $existing = Partida::where('usuario_id', $user->id)->where('juego', $gameKey)
                ->where('request_token', $request->request_token)->lockForUpdate()->first();
            if ($existing) {
                return $existing;
            }
            $campaignId = $this->balances->campaignId($user, $gameKey);
            abort_unless($this->balances->debit($user, $gameKey, $apuesta, 'apuesta_ruleta', ['variante' => $variant], null, $request->request_token, $campaignId), 422, 'Saldo insuficiente.');
            $numero = random_int(0, 36);
            $color = $numero === 0 ? 'verde' : (in_array($numero, config('roulette.red_numbers'), true) ? 'rojo' : 'negro');
            $multipliers = $variant === 'lightning' ? $this->lightningNumbers() : [];
            $ganancia = $this->calculateWin($request->tipo, $request->valor, $numero, $color, $apuesta);
            if ($variant === 'lightning' && $request->tipo === 'numero' && $request->valor === $numero && isset($multipliers[$numero])) {
                $ganancia = round($apuesta * $multipliers[$numero], 2);
            }
            if ($ganancia > 0) {
                $this->balances->credit($user, $gameKey, $ganancia, 'premio_ruleta', ['variante' => $variant], null, $campaignId);
            }
            $round = Partida::create([
                'usuario_id' => $user->id, 'juego' => $gameKey, 'request_token' => $request->request_token,
                'apuesta' => $apuesta, 'ganancia' => $ganancia,
                'campaign_challenge_id' => $campaignId, 'campaign_key' => $campaignId ? CampaignManager::KEY : null,
                'detalles' => [
                    'numero' => $numero, 'color' => $color, 'tipo_apuesta' => $request->tipo,
                    'valor_apuesta' => $request->valor, 'resultado' => $ganancia > 0 ? 'win' : 'lose',
                    'variante' => $variant, 'multiplicadores' => $multipliers,
                ],
            ]);
            $this->balances->recordGame($round);

            return $round;
        });

        $details = $round->detalles;

        return response()->json([
            'numero' => $details['numero'], 'color' => $details['color'],
            'ganancia' => (float) $round->ganancia, 'resultado' => $details['resultado'],
            'saldo' => $this->balances->balance($user, $gameKey, $round->campaign_challenge_id),
            'multipliers' => $details['multiplicadores'],
        ]);
    }

    private function lightningNumbers(): array
    {
        $numbers = range(0, 36);
        shuffle($numbers);
        $boosts = [];
        foreach (array_slice($numbers, 0, 5) as $number) {
            $boosts[$number] = [50, 100, 200, 500][array_rand([50, 100, 200, 500])];
        }

        return $boosts;
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
