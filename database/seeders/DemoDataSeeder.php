<?php

namespace Database\Seeders;

use App\Models\ActivityLog;
use App\Models\Feedback;
use App\Models\Review;
use App\Models\Usuario;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use RuntimeException;
use Spatie\Permission\Models\Role;

class DemoDataSeeder extends Seeder
{
    private const USER_COUNT = 80;

    private const GAME_COUNT = 2400;

    public function run(): void
    {
        if (! app()->environment(['local', 'testing'])) {
            throw new RuntimeException('Los datos demo sólo pueden generarse en entornos local o testing.');
        }

        mt_srand(15072026);

        if (! Role::where('name', 'super_admin')->exists()) {
            $this->call(RolesPermissionsSeeder::class);
        }

        DB::transaction(function (): void {
            $this->removePreviousDemoData();

            $admin = Usuario::updateOrCreate(
                ['email' => 'admin@lootra.test'],
                [
                    'name' => 'Lootra Admin',
                    'password' => Hash::make(Str::random(64)),
                    'email_verified_at' => now(),
                    'is_demo' => true,
                    'data_origin' => 'simulated',
                ]
            );
            $admin->cartera()->updateOrCreate([], ['saldo' => 25000]);
            $admin->syncRoles('super_admin');

            $users = $this->createUsers();
            $this->createGames($users);
            $reviews = $this->createRatingsAndReviews($users);
            $this->createVotes($users, $reviews);
            $this->createFeedback($users);
            $this->createActivity($users, $admin);
        });

        $this->command?->info('Datos demo creados: 80 usuarios, 2.400 partidas y actividad de 90 días.');
    }

    private function removePreviousDemoData(): void
    {
        ActivityLog::where('accion', 'like', 'demo_%')->delete();
        Feedback::where('asunto', 'like', '[DEMO]%')->delete();
        Usuario::withTrashed()->where('email', 'like', '%@demo.lootra.test')->forceDelete();
    }

    private function createUsers()
    {
        $names = ['Lucía', 'Hugo', 'Sofía', 'Mateo', 'Martina', 'Leo', 'Valeria', 'Daniel', 'Carla', 'Alejandro', 'Emma', 'Pablo', 'Nora', 'Adrián', 'Claudia', 'Mario'];
        $surnames = ['García', 'Martín', 'López', 'Sánchez', 'Romero', 'Navarro', 'Torres', 'Vega', 'Castro', 'Molina'];
        $users = collect();

        for ($i = 1; $i <= self::USER_COUNT; $i++) {
            $createdAt = now()->subDays(mt_rand(0, 120))->subMinutes(mt_rand(0, 1440));
            $usuario = Usuario::create([
                'name' => $names[array_rand($names)].' '.$surnames[array_rand($surnames)],
                'email' => sprintf('jugador%03d@demo.lootra.test', $i),
                'password' => Hash::make(Str::random(64)),
                'email_verified_at' => now(),
                'is_demo' => true,
                'data_origin' => 'simulated',
            ]);
            $usuario->timestamps = false;
            $usuario->forceFill(['created_at' => $createdAt, 'updated_at' => $createdAt])->saveQuietly();
            $usuario->timestamps = true;
            $usuario->cartera()->create(['saldo' => mt_rand(5000, 450000) / 100]);
            $usuario->assignRole($i <= 3 ? 'moderator' : 'user');
            $users->push($usuario);
        }

        return $users;
    }

    private function createGames($users): void
    {
        $games = ['slots', 'ruleta', 'blackjack', 'crash'];
        $rows = [];

        for ($i = 0; $i < self::GAME_COUNT; $i++) {
            $user = $users->random();
            $game = $games[array_rand($games)];
            $bet = mt_rand(1, 100) <= 78 ? mt_rand(100, 2500) / 100 : mt_rand(2500, 20000) / 100;
            $won = mt_rand(1, 100) <= 43;
            $multiplier = $won ? mt_rand(105, 650) / 100 : 0;
            $playedAt = now()->subDays(mt_rand(0, 89))->startOfDay()->addMinutes(mt_rand(0, 1439));

            $rows[] = [
                'usuario_id' => $user->id,
                'juego' => $game,
                'apuesta' => round($bet, 2),
                'ganancia' => round($bet * $multiplier, 2),
                'detalles' => json_encode(['demo' => true, 'resultado' => $won ? 'win' : 'lose', 'multiplicador' => $multiplier]),
                'created_at' => $playedAt,
                'updated_at' => $playedAt,
            ];
        }

        foreach (array_chunk($rows, 400) as $chunk) {
            DB::table('partidas')->insert($chunk);
        }
    }

    private function createRatingsAndReviews($users)
    {
        $games = ['gates-of-olympus', 'sweet-bonanza', 'book-of-dead', 'starburst', 'big-bass-bonanza', 'european-roulette', 'blackjack-vip', 'crash-rocket'];
        $titles = ['Muy entretenido', 'Buena experiencia', 'Tiene potencial', 'Mi favorito', 'Rondas muy fluidas', 'Buen diseño'];
        $contents = [
            'Las animaciones se sienten fluidas y la partida mantiene un buen ritmo.',
            'Me gusta la presentación y la facilidad para entender los controles.',
            'La experiencia general es buena, aunque añadiría más opciones de apuesta.',
            'He jugado varias sesiones y el funcionamiento ha sido estable.',
            'Visualmente resulta atractivo y los resultados se muestran con claridad.',
        ];
        $reviewRows = [];
        $ratingRows = [];

        foreach ($users as $user) {
            foreach (collect($games)->shuffle()->take(mt_rand(3, 6)) as $game) {
                $at = now()->subDays(mt_rand(0, 75));
                $rating = mt_rand(1, 100) <= 72 ? mt_rand(4, 5) : mt_rand(2, 4);
                $ratingRows[] = ['usuario_id' => $user->id, 'juego_slug' => $game, 'puntuacion' => $rating, 'created_at' => $at, 'updated_at' => $at];
            }

            if (mt_rand(1, 100) <= 72) {
                $at = now()->subDays(mt_rand(0, 60));
                $reviewRows[] = [
                    'usuario_id' => $user->id,
                    'juego_slug' => $games[array_rand($games)],
                    'titulo' => $titles[array_rand($titles)],
                    'contenido' => $contents[array_rand($contents)],
                    'tipo' => 'juego',
                    'estado' => $this->weighted(['aprobado' => 72, 'pendiente' => 20, 'rechazado' => 8]),
                    'puntuacion' => mt_rand(3, 5),
                    'likes' => 0,
                    'dislikes' => 0,
                    'created_at' => $at,
                    'updated_at' => $at,
                ];
            }
        }

        DB::table('ratings')->insert($ratingRows);
        DB::table('reviews')->insert($reviewRows);

        return Review::whereIn('usuario_id', $users->pluck('id'))->get();
    }

    private function createVotes($users, $reviews): void
    {
        foreach ($reviews as $review) {
            $likes = 0;
            $dislikes = 0;
            foreach ($users->where('id', '!=', $review->usuario_id)->shuffle()->take(mt_rand(2, 12)) as $user) {
                $upvote = mt_rand(1, 100) <= 82;
                DB::table('review_votes')->insert([
                    'usuario_id' => $user->id,
                    'review_id' => $review->id,
                    'upvote' => $upvote,
                    'created_at' => now()->subDays(mt_rand(0, 45)),
                    'updated_at' => now(),
                ]);
                $upvote ? $likes++ : $dislikes++;
            }
            $review->update(['likes' => $likes, 'dislikes' => $dislikes]);
        }
    }

    private function createFeedback($users): void
    {
        $subjects = ['Mejora del historial', 'Problema al mostrar el saldo', 'Nueva opción de apuesta', 'Sugerencia para el perfil', 'Animación del juego'];

        for ($i = 0; $i < 65; $i++) {
            $state = $this->weighted(['abierto' => 22, 'en_progreso' => 18, 'resuelto' => 45, 'cerrado' => 15]);
            $at = now()->subDays(mt_rand(0, 80));
            DB::table('feedback')->insert([
                'usuario_id' => $users->random()->id,
                'tipo' => $this->weighted(['sugerencia' => 35, 'bug' => 30, 'mejora' => 25, 'otro' => 10]),
                'asunto' => '[DEMO] '.$subjects[array_rand($subjects)],
                'contenido' => 'Mensaje de demostración para representar una solicitud real enviada por un usuario.',
                'estado' => $state,
                'prioridad' => $this->weighted(['baja' => 15, 'normal' => 55, 'alta' => 23, 'urgente' => 7]),
                'respuesta' => in_array($state, ['resuelto', 'cerrado'], true) ? 'Solicitud revisada por el equipo de Lootra.' : null,
                'created_at' => $at,
                'updated_at' => $at->copy()->addHours(mt_rand(1, 72)),
            ]);
        }
    }

    private function createActivity($users, Usuario $admin): void
    {
        $actions = ['demo_login', 'demo_partida_completada', 'demo_perfil_actualizado', 'demo_review_creada', 'demo_deposito'];

        for ($i = 0; $i < 300; $i++) {
            $user = mt_rand(1, 100) <= 94 ? $users->random() : $admin;
            $at = now()->subDays(mt_rand(0, 89))->startOfDay()->addMinutes(mt_rand(0, 1439));
            DB::table('activity_logs')->insert([
                'usuario_id' => $user->id,
                'accion' => $actions[array_rand($actions)],
                'modelo' => 'Usuario',
                'modelo_id' => $user->id,
                'detalles' => json_encode(['demo' => true]),
                'ip' => '127.0.0.'.mt_rand(2, 254),
                'created_at' => $at,
                'updated_at' => $at,
            ]);
        }
    }

    private function weighted(array $values): string
    {
        $roll = mt_rand(1, array_sum($values));
        foreach ($values as $value => $weight) {
            $roll -= $weight;
            if ($roll <= 0) {
                return $value;
            }
        }

        return array_key_first($values);
    }
}
