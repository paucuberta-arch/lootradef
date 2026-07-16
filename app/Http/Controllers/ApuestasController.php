<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\ApuestaDeportiva;
use App\Models\Cartera;
use App\Models\PartidoDeportivo;
use App\Services\SportsSimulationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ApuestasController extends Controller
{
    public function __construct(private readonly SportsSimulationService $simulation) {}

    public function index(Request $request): View
    {
        $this->simulation->ensureFixtures();

        return view('apuestas.index', [
            'matches' => PartidoDeportivo::latest('inicia_at')->take(18)->get()->map(fn ($match) => $this->matchData($match))->values(),
            'bets' => $request->user() ? $this->userBets($request->user()->id) : collect(),
        ]);
    }

    public function feed(Request $request): JsonResponse
    {
        return response()->json([
            'matches' => PartidoDeportivo::latest('inicia_at')->take(18)->get()->map(fn ($match) => $this->matchData($match))->values(),
            'bets' => $request->user() ? $this->userBets($request->user()->id) : [],
            'balance' => $request->user()?->cartera?->fresh()->saldo,
            'server_time' => now()->toIso8601String(),
        ]);
    }

    public function place(Request $request, PartidoDeportivo $partido): JsonResponse
    {
        $validated = $request->validate([
            'seleccion' => ['required', 'in:local,empate,visitante'],
            'importe' => ['required', 'numeric', 'min:1', 'max:5000'],
        ]);

        $this->simulation->syncMatch($partido);

        $bet = DB::transaction(function () use ($request, $partido, $validated) {
            $match = PartidoDeportivo::lockForUpdate()->findOrFail($partido->id);
            if (! in_array($match->estado, ['programado', 'en_vivo'], true) || $match->minuto >= 80) {
                abort(422, 'Las apuestas para este partido ya están cerradas.');
            }

            $wallet = Cartera::where('usuario_id', $request->user()->id)->lockForUpdate()->first();
            if (! $wallet || $wallet->saldo < $validated['importe']) {
                abort(422, 'No tienes saldo suficiente para realizar esta apuesta.');
            }

            $odds = [
                'local' => $match->cuota_local,
                'empate' => $match->cuota_empate,
                'visitante' => $match->cuota_visitante,
            ];
            $bet = ApuestaDeportiva::create([
                'usuario_id' => $request->user()->id,
                'partido_id' => $match->id,
                'seleccion' => $validated['seleccion'],
                'cuota' => $odds[$validated['seleccion']],
                'importe' => $validated['importe'],
            ]);
            abort_unless($wallet->apostar($validated['importe'], 'apuesta_deportiva', ['seleccion' => $validated['seleccion']], $bet), 422, 'No tienes saldo suficiente para realizar esta apuesta.');

            return $bet;
        });

        ActivityLog::log('apuesta_deportiva_creada', 'ApuestaDeportiva', $bet->id, [
            'partido_id' => $partido->id,
            'importe' => (float) $bet->importe,
            'seleccion' => $bet->seleccion,
        ]);

        return response()->json([
            'message' => 'Apuesta registrada correctamente.',
            'bet' => $this->betData($bet->load('partido')),
            'balance' => $request->user()->cartera->fresh()->saldo,
        ], 201);
    }

    private function matchData(PartidoDeportivo $match): array
    {
        return [
            'id' => $match->id,
            'league' => $match->liga,
            'home' => $match->local,
            'away' => $match->visitante,
            'home_short' => $match->local_siglas,
            'away_short' => $match->visitante_siglas,
            'image' => $match->imagen,
            'status' => $match->estado,
            'minute' => $match->minuto,
            'home_score' => $match->goles_local,
            'away_score' => $match->goles_visitante,
            'starts_at' => $match->inicia_at->toIso8601String(),
            'starts_label' => $match->inicia_at->isFuture() ? $match->inicia_at->diffForHumans() : 'Comenzado',
            'odds' => ['local' => $match->cuota_local, 'empate' => $match->cuota_empate, 'visitante' => $match->cuota_visitante],
            'events' => collect($match->eventos ?? [])->sortByDesc('minute')->take(5)->values()->all(),
            'betting_open' => in_array($match->estado, ['programado', 'en_vivo'], true) && $match->minuto < 80,
        ];
    }

    private function userBets(int $userId)
    {
        return ApuestaDeportiva::with('partido')->where('usuario_id', $userId)->latest()->take(12)->get()->map(fn ($bet) => $this->betData($bet))->values();
    }

    private function betData(ApuestaDeportiva $bet): array
    {
        $labels = ['local' => $bet->partido->local, 'empate' => 'Empate', 'visitante' => $bet->partido->visitante];

        return [
            'id' => $bet->id,
            'match' => $bet->partido->local.' — '.$bet->partido->visitante,
            'selection' => $labels[$bet->seleccion],
            'odds' => $bet->cuota,
            'amount' => $bet->importe,
            'potential' => round($bet->importe * $bet->cuota, 2),
            'winnings' => $bet->ganancia,
            'status' => $bet->estado,
        ];
    }
}
