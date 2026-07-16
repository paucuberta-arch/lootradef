<?php

namespace App\Services;

use App\Models\ApuestaDeportiva;
use App\Models\Cartera;
use App\Models\PartidoDeportivo;
use Illuminate\Support\Facades\DB;

class SportsSimulationService
{
    public function __construct(private readonly AccountMailService $accountMail) {}

    public function ensureFixtures(): void
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
                'liga' => $league, 'local' => $home, 'visitante' => $away,
                'local_siglas' => $homeShort, 'visitante_siglas' => $awayShort,
                'imagen' => 'https://images.unsplash.com/photo-1522778119026-d647f0596c20?w=1200&h=600&fit=crop',
                'inicia_at' => now()->addSeconds($offset), 'duracion_segundos' => 360,
                'cuota_local' => random_int(165, 275) / 100,
                'cuota_empate' => random_int(280, 390) / 100,
                'cuota_visitante' => random_int(175, 310) / 100,
                'simulacion' => $this->makeSimulation($home, $away),
            ]);
        }
    }

    public function syncAll(): int
    {
        $this->ensureFixtures();
        $matches = PartidoDeportivo::whereIn('estado', ['programado', 'en_vivo'])->get();
        $matches->each(fn (PartidoDeportivo $match) => $this->syncMatch($match));

        return $matches->count();
    }

    public function syncMatch(PartidoDeportivo $match): void
    {
        $elapsed = $match->inicia_at->diffInSeconds(now(), false);
        if ($elapsed < 0) {
            return;
        }

        $minute = min(90, (int) floor(($elapsed / max(1, $match->duracion_segundos)) * 90));
        $visible = collect($match->simulacion)->filter(fn ($event) => $event['minute'] <= $minute)->values();
        $status = $minute >= 90 ? 'finalizado' : 'en_vivo';
        $match->update([
            'estado' => $status, 'minuto' => $minute,
            'goles_local' => $visible->where('type', 'goal')->where('team', 'local')->count(),
            'goles_visitante' => $visible->where('type', 'goal')->where('team', 'visitante')->count(),
            'eventos' => $visible->all(),
        ]);

        if ($status === 'finalizado') {
            $this->settle($match);
        }
    }

    private function settle(PartidoDeportivo $match): void
    {
        $settled = DB::transaction(function () use ($match) {
            $locked = PartidoDeportivo::lockForUpdate()->findOrFail($match->id);
            $result = $locked->goles_local === $locked->goles_visitante ? 'empate' : ($locked->goles_local > $locked->goles_visitante ? 'local' : 'visitante');
            $bets = ApuestaDeportiva::where('partido_id', $locked->id)->where('estado', 'pendiente')->lockForUpdate()->get();
            $bets->each(function (ApuestaDeportiva $bet) use ($result) {
                $won = $bet->seleccion === $result;
                $payout = $won ? round($bet->importe * $bet->cuota, 2) : 0;
                if ($won) {
                    Cartera::where('usuario_id', $bet->usuario_id)->lockForUpdate()->firstOrFail()
                        ->ganar($payout, 'premio_apuesta_deportiva', ['resultado' => $result], $bet);
                }
                $bet->update(['estado' => $won ? 'ganada' : 'perdida', 'ganancia' => $payout, 'liquidada_at' => now()]);
            });

            return $bets;
        });

        $settled->each(fn (ApuestaDeportiva $bet) => $this->accountMail->sportsBetSettled($bet));
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
}
