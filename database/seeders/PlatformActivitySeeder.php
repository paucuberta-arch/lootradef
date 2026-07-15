<?php

namespace Database\Seeders;

use App\Models\Usuario;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PlatformActivitySeeder extends Seeder
{
    private const CASINO_ROUNDS = 900;

    private const SPORTS_BETS = 420;

    private const BOX_OPENINGS = 320;

    public function run(): void
    {
        mt_srand(26072026);

        $users = Usuario::where('email', 'like', '%@demo.lootra.test')->get();
        if ($users->isEmpty()) {
            $this->call(DemoDataSeeder::class);
            $users = Usuario::where('email', 'like', '%@demo.lootra.test')->get();
        }

        DB::transaction(function () use ($users): void {
            $this->removePreviousData();
            $this->seedCasino($users);
            $this->seedSports($users);
            $this->seedBoxes($users);
        });

        $this->command?->info('Actividad adicional creada para las 3 categorías: 900 partidas, 420 apuestas deportivas y 320 cajas.');
    }

    private function removePreviousData(): void
    {
        DB::table('partidas')->where('detalles', 'like', '%platform_activity%')->delete();
        DB::table('partidos_deportivos')->where('liga', 'like', '[SEED]%')->delete();
        DB::table('inventario_items')->where('caja', 'like', 'seed_%')->delete();
    }

    private function seedCasino($users): void
    {
        $games = ['slots', 'ruleta', 'blackjack', 'crash', 'minas-neon', 'dados-dorados', 'penaltis', 'carrera-caballos'];
        $rows = [];

        for ($i = 0; $i < self::CASINO_ROUNDS; $i++) {
            $bet = mt_rand(100, 15000) / 100;
            $won = mt_rand(1, 100) <= 44;
            $multiplier = $won ? mt_rand(105, 520) / 100 : 0;
            $at = now()->subDays(mt_rand(0, 89))->subMinutes(mt_rand(0, 1439));
            $rows[] = [
                'usuario_id' => $users->random()->id,
                'juego' => $games[array_rand($games)],
                'apuesta' => $bet,
                'ganancia' => round($bet * $multiplier, 2),
                'detalles' => json_encode(['seeder' => 'platform_activity', 'resultado' => $won ? 'win' : 'lose']),
                'created_at' => $at,
                'updated_at' => $at,
            ];
        }

        foreach (array_chunk($rows, 300) as $chunk) {
            DB::table('partidas')->insert($chunk);
        }
    }

    private function seedSports($users): void
    {
        $fixtures = [
            ['Champions League', 'Real Madrid', 'Manchester City', 'RMA', 'MCI'],
            ['Premier League', 'Liverpool', 'Arsenal', 'LIV', 'ARS'],
            ['La Liga', 'Barcelona', 'Atlético', 'FCB', 'ATM'],
            ['Serie A', 'Inter', 'Juventus', 'INT', 'JUV'],
            ['Bundesliga', 'Bayern', 'Dortmund', 'BAY', 'BVB'],
            ['Europa League', 'Sevilla', 'Roma', 'SEV', 'ROM'],
        ];
        $matches = [];

        for ($i = 0; $i < 54; $i++) {
            [$league, $home, $away, $homeShort, $awayShort] = $fixtures[$i % count($fixtures)];
            $future = $i >= 48;
            $homeGoals = $future ? 0 : mt_rand(0, 4);
            $awayGoals = $future ? 0 : mt_rand(0, 4);
            $at = $future ? now()->addMinutes(($i - 47) * 25) : now()->subDays(mt_rand(0, 89))->subMinutes(mt_rand(10, 1400));
            $events = $this->goalEvents($homeGoals, $awayGoals, $home, $away);
            $id = DB::table('partidos_deportivos')->insertGetId([
                'deporte' => 'futbol', 'liga' => '[SEED] '.$league, 'local' => $home, 'visitante' => $away,
                'local_siglas' => $homeShort, 'visitante_siglas' => $awayShort, 'estado' => $future ? 'programado' : 'finalizado',
                'inicia_at' => $at, 'duracion_segundos' => 360, 'minuto' => $future ? 0 : 90,
                'goles_local' => $homeGoals, 'goles_visitante' => $awayGoals,
                'cuota_local' => mt_rand(165, 285) / 100, 'cuota_empate' => mt_rand(280, 400) / 100,
                'cuota_visitante' => mt_rand(175, 320) / 100, 'simulacion' => json_encode($events),
                'eventos' => json_encode($future ? [] : $events), 'created_at' => $at, 'updated_at' => $at,
            ]);
            $matches[] = compact('id', 'future', 'homeGoals', 'awayGoals', 'at');
        }

        $rows = [];
        for ($i = 0; $i < self::SPORTS_BETS; $i++) {
            $match = $matches[array_rand($matches)];
            $selection = ['local', 'empate', 'visitante'][mt_rand(0, 2)];
            $result = $match['homeGoals'] === $match['awayGoals'] ? 'empate' : ($match['homeGoals'] > $match['awayGoals'] ? 'local' : 'visitante');
            $odd = $selection === 'empate' ? mt_rand(280, 400) / 100 : mt_rand(165, 320) / 100;
            $amount = mt_rand(200, 20000) / 100;
            $won = ! $match['future'] && $selection === $result;
            $created = $match['future'] ? now()->subMinutes(mt_rand(1, 120)) : $match['at']->copy()->subMinutes(mt_rand(5, 240));
            $rows[] = [
                'usuario_id' => $users->random()->id, 'partido_id' => $match['id'], 'seleccion' => $selection,
                'cuota' => $odd, 'importe' => $amount, 'ganancia' => $won ? round($amount * $odd, 2) : 0,
                'estado' => $match['future'] ? 'pendiente' : ($won ? 'ganada' : 'perdida'),
                'liquidada_at' => $match['future'] ? null : $match['at']->copy()->addMinutes(100),
                'created_at' => $created, 'updated_at' => $created,
            ];
        }
        foreach (array_chunk($rows, 300) as $chunk) {
            DB::table('apuestas_deportivas')->insert($chunk);
        }
    }

    private function seedBoxes($users): void
    {
        $boxes = config('cajas');
        $keys = array_keys($boxes);
        $rows = [];

        for ($i = 0; $i < self::BOX_OPENINGS; $i++) {
            $key = $keys[array_rand($keys)];
            $box = $boxes[$key];
            $prize = $this->weightedPrize($box['premios']);
            $redeemed = mt_rand(1, 100) <= 68;
            $at = now()->subDays(mt_rand(0, 89))->subMinutes(mt_rand(0, 1439));
            $rows[] = [
                'usuario_id' => $users->random()->id, 'caja' => 'seed_'.$key, 'nombre' => $prize['nombre'],
                'imagen' => $prize['imagen'], 'rareza' => $prize['rareza'], 'precio_caja' => $box['precio'],
                'valor_canje' => $prize['valor'], 'estado' => $redeemed ? 'canjeado' : 'disponible',
                'canjeado_at' => $redeemed ? $at->copy()->addHours(mt_rand(1, 72)) : null,
                'created_at' => $at, 'updated_at' => $at,
            ];
        }
        foreach (array_chunk($rows, 300) as $chunk) {
            DB::table('inventario_items')->insert($chunk);
        }
    }

    private function goalEvents(int $homeGoals, int $awayGoals, string $home, string $away): array
    {
        $events = [];
        foreach ([['local', $home, $homeGoals], ['visitante', $away, $awayGoals]] as [$team, $name, $goals]) {
            for ($i = 0; $i < $goals; $i++) {
                $events[] = ['minute' => mt_rand(4, 89), 'type' => 'goal', 'team' => $team, 'text' => 'Gol de '.$name];
            }
        }
        usort($events, fn ($a, $b) => $a['minute'] <=> $b['minute']);

        return $events;
    }

    private function weightedPrize(array $prizes): array
    {
        $roll = mt_rand(1, array_sum(array_column($prizes, 'peso')));
        foreach ($prizes as $prize) {
            $roll -= $prize['peso'];
            if ($roll <= 0) {
                return $prize;
            }
        }

        return $prizes[array_key_last($prizes)];
    }
}
