<?php

namespace App\Services;

use App\Models\ApuestaDeportiva;
use App\Models\Cartera;
use App\Models\PartidoDeportivo;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class SportsSimulationService
{
    public function __construct(
        private readonly AccountMailService $accountMail,
        private readonly WalletService $wallets,
    ) {}

    public function ensureFixtures(): void
    {
        Cache::lock('sports:fixture-generation', 15)->block(3, function (): void {
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
            $batch = intdiv(now()->timestamp, 480);

            foreach ($fixtures as [$league, $home, $away, $homeShort, $awayShort, $offset]) {
                $fixtureKey = hash('sha256', implode('|', [$batch, $league, $home, $away]));
                PartidoDeportivo::firstOrCreate(['fixture_key' => $fixtureKey], [
                    'liga' => $league, 'local' => $home, 'visitante' => $away,
                    'local_siglas' => $homeShort, 'visitante_siglas' => $awayShort,
                    'imagen' => '/images/lootra_visual_pack/04_backgrounds/bg_emerald_forest_1920x1080.webp',
                    'inicia_at' => now()->addSeconds($offset), 'duracion_segundos' => 360,
                    'cuota_local' => $this->odds($this->probabilities($offset)['local']),
                    'cuota_empate' => $this->odds($this->probabilities($offset)['empate']),
                    'cuota_visitante' => $this->odds($this->probabilities($offset)['visitante']),
                    'simulacion' => $this->makeSimulation($home, $away, $offset),
                ]);
            }
        });
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
                    $wallet = Cartera::where('usuario_id', $bet->usuario_id)->lockForUpdate()->firstOrFail();
                    $this->wallets->credit(
                        $wallet,
                        $payout,
                        'premio_apuesta_deportiva',
                        ['resultado' => $result],
                        $bet,
                        'sports-payout:'.$bet->id
                    );
                }
                $bet->update(['estado' => $won ? 'ganada' : 'perdida', 'ganancia' => $payout, 'liquidada_at' => now()]);
            });

            return $bets;
        });

        $settled->each(fn (ApuestaDeportiva $bet) => $this->accountMail->sportsBetSettled($bet));
    }

    private function makeSimulation(string $home, string $away, int $offset): array
    {
        $events = [];
        $probabilities = $this->probabilities($offset);
        $roll = random_int(1, 10000) / 10000;
        $result = $roll <= $probabilities['local']
            ? 'local'
            : ($roll <= $probabilities['local'] + $probabilities['empate'] ? 'empate' : 'visitante');
        $goals = $result === 'empate'
            ? $this->randomScore()
            : ($result === 'local' ? [random_int(1, 3), random_int(0, 1)] : [random_int(0, 1), random_int(1, 3)]);

        foreach ([['local', $home, $goals[0]], ['visitante', $away, $goals[1]]] as [$team, $name, $goalCount]) {
            for ($i = 0; $i < $goalCount; $i++) {
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

    /** @return array{local: float, empate: float, visitante: float} */
    private function probabilities(int $offset): array
    {
        $homeStrength = max(.35, 1.35 - ($offset / 600));
        $awayStrength = max(.35, 1.15 + ($offset / 600));
        $drawStrength = .85;
        $total = $homeStrength + $awayStrength + $drawStrength;

        return [
            'local' => $homeStrength / $total,
            'empate' => $drawStrength / $total,
            'visitante' => $awayStrength / $total,
        ];
    }

    private function odds(float $probability): float
    {
        return round(1 / max(.01, $probability * 1.06), 2);
    }

    /** @return array{0: int, 1: int} */
    private function randomScore(): array
    {
        $goals = random_int(0, 3);

        return [$goals, $goals];
    }
}
