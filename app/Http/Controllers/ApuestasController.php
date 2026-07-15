<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\ApuestaDeportiva;
use App\Models\Cartera;
use App\Models\PartidoDeportivo;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ApuestasController extends Controller
{
    public function index(Request $request): View
    {
        $this->ensureFixtures();
        $this->syncAll();

        return view('apuestas.index', [
            'matches' => PartidoDeportivo::latest('inicia_at')->take(18)->get()->map(fn ($match) => $this->matchData($match))->values(),
            'bets' => $request->user() ? $this->userBets($request->user()->id) : collect(),
        ]);
    }

    public function feed(Request $request): JsonResponse
    {
        $this->ensureFixtures();
        $this->syncAll();

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

        $this->syncMatch($partido);

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
            $wallet->saldo = round($wallet->saldo - $validated['importe'], 2);
            $wallet->save();

            return ApuestaDeportiva::create([
                'usuario_id' => $request->user()->id,
                'partido_id' => $match->id,
                'seleccion' => $validated['seleccion'],
                'cuota' => $odds[$validated['seleccion']],
                'importe' => $validated['importe'],
            ]);
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

    private function ensureFixtures(): void
    {
        if (PartidoDeportivo::where('inicia_at', '>', now()->subMinutes(8))->exists()) {
            return;
        }

        $fixtures = [
            ['Champions League', 'Real Madrid', 'Manchester City', 'RMA', 'MCI', -150],
            ['Premier League', 'Liverpool', 'Arsenal', 'LIV', 'ARS', -55],
            ['La Liga', 'Barcelona', 'Atlético de Madrid', 'FCB', 'ATM', 45],
            ['Serie A', 'Inter de Milán', 'Juventus', 'INT', 'JUV', 150],
            ['Bundesliga', 'Bayern Múnich', 'Dortmund', 'BAY', 'BVB', 260],
            ['Europa League', 'Sevilla', 'Roma', 'SEV', 'ROM', 380],
        ];

        foreach ($fixtures as [$league, $home, $away, $homeShort, $awayShort, $offset]) {
            PartidoDeportivo::create([
                'liga' => $league,
                'local' => $home,
                'visitante' => $away,
                'local_siglas' => $homeShort,
                'visitante_siglas' => $awayShort,
                'imagen' => 'https://images.unsplash.com/photo-1522778119026-d647f0596c20?w=1200&h=600&fit=crop',
                'inicia_at' => now()->addSeconds($offset),
                'duracion_segundos' => 360,
                'cuota_local' => random_int(165, 275) / 100,
                'cuota_empate' => random_int(280, 390) / 100,
                'cuota_visitante' => random_int(175, 310) / 100,
                'simulacion' => $this->makeSimulation($home, $away),
            ]);
        }
    }

    private function makeSimulation(string $home, string $away): array
    {
        $events = [];
        foreach ([['local', $home], ['visitante', $away]] as [$team, $name]) {
            for ($i = 0, $goals = random_int(0, 3); $i < $goals; $i++) {
                $events[] = ['minute' => random_int(5, 88), 'type' => 'goal', 'team' => $team, 'text' => "¡Gol de {$name}!"];
            }
        }
        foreach ([18, 34, 58, 72] as $minute) {
            $team = random_int(0, 1) ? $home : $away;
            $events[] = ['minute' => $minute + random_int(-4, 4), 'type' => 'chance', 'team' => null, 'text' => "Gran ocasión para {$team}"];
        }
        usort($events, fn ($a, $b) => $a['minute'] <=> $b['minute']);

        return $events;
    }

    private function syncAll(): void
    {
        PartidoDeportivo::whereIn('estado', ['programado', 'en_vivo'])->get()->each(fn ($match) => $this->syncMatch($match));
    }

    private function syncMatch(PartidoDeportivo $match): void
    {
        $elapsed = $match->inicia_at->diffInSeconds(now(), false);
        if ($elapsed < 0) {
            return;
        }

        $minute = min(90, (int) floor(($elapsed / max(1, $match->duracion_segundos)) * 90));
        $visible = collect($match->simulacion)->filter(fn ($event) => $event['minute'] <= $minute)->values();
        $homeGoals = $visible->where('type', 'goal')->where('team', 'local')->count();
        $awayGoals = $visible->where('type', 'goal')->where('team', 'visitante')->count();
        $status = $minute >= 90 ? 'finalizado' : 'en_vivo';

        $match->update([
            'estado' => $status,
            'minuto' => $minute,
            'goles_local' => $homeGoals,
            'goles_visitante' => $awayGoals,
            'eventos' => $visible->all(),
        ]);

        if ($status === 'finalizado') {
            $this->settle($match);
        }
    }

    private function settle(PartidoDeportivo $match): void
    {
        DB::transaction(function () use ($match) {
            $locked = PartidoDeportivo::lockForUpdate()->find($match->id);
            $result = $locked->goles_local === $locked->goles_visitante
                ? 'empate'
                : ($locked->goles_local > $locked->goles_visitante ? 'local' : 'visitante');

            ApuestaDeportiva::where('partido_id', $locked->id)->where('estado', 'pendiente')
                ->lockForUpdate()->get()->each(function ($bet) use ($result) {
                    $won = $bet->seleccion === $result;
                    $payout = $won ? round($bet->importe * $bet->cuota, 2) : 0;
                    if ($won) {
                        Cartera::where('usuario_id', $bet->usuario_id)->increment('saldo', $payout);
                    }
                    $bet->update([
                        'estado' => $won ? 'ganada' : 'perdida',
                        'ganancia' => $payout,
                        'liquidada_at' => now(),
                    ]);
                });
        });
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
