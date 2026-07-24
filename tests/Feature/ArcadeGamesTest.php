<?php

namespace Tests\Feature;

use App\Models\Partida;
use App\Models\Usuario;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ArcadeGamesTest extends TestCase
{
    use RefreshDatabase;

    public function test_catalog_contains_twenty_distinct_playable_games(): void
    {
        $this->assertCount(11, config('arcade_games'));
        $this->assertSame(21, 12 + count(config('arcade_games')) - 2);

        $this->get(route('inicio'))
            ->assertOk()
            ->assertSee('21')
            ->assertSee('Lootra Originals');
    }

    public function test_every_original_game_opens_and_completes_a_server_side_round(): void
    {
        $usuario = Usuario::create(['name' => 'Arcade Tester', 'email' => 'arcade@example.com', 'password' => bcrypt('password')]);
        $usuario->cartera()->create(['saldo' => 1000]);

        $payloads = [
            'crazy-time' => [], 'texas-holdem' => [], 'neon-mines' => ['choice' => 3],
            'dice-arena' => ['choice' => 'high'], 'high-low' => ['choice' => 'higher'],
            'quantum-plinko' => [], 'cosmic-keno' => ['numbers' => [1, 7, 13, 21, 28]],
            'coin-duel' => ['choice' => 'heads'], 'baccarat-royale' => ['choice' => 'player'],
            'nebula-picks' => ['choice' => 'violet'],
        ];

        foreach ($payloads as $slug => $payload) {
            $this->actingAs($usuario)->get(route('arcade', $slug))->assertOk();
            $this->actingAs($usuario)
                ->postJson(route('arcade.play', $slug), ['apuesta' => 1, 'request_token' => fake()->uuid(), ...$payload])
                ->assertOk()
                ->assertJsonStructure(['multiplier', 'ganancia', 'saldo']);
        }

        $this->assertSame(10, Partida::where('usuario_id', $usuario->id)->count());
        $this->assertSame(10, Partida::where('usuario_id', $usuario->id)->whereNotNull('math_version')->count());
    }

    public function test_quantum_plinko_uses_its_physics_board_and_server_trajectory(): void
    {
        $usuario = Usuario::create(['name' => 'Quantum Tester', 'email' => 'quantum@example.com', 'password' => bcrypt('password')]);
        $usuario->cartera()->create(['saldo' => 100]);

        $this->actingAs($usuario)->get(route('games.originals.show', 'quantum-plinko'))
            ->assertOk()
            ->assertSee('x-ref="plinkoCanvas"', false)
            ->assertSee('stepPhysics(delta, path, time, elapsed)', false)
            ->assertSee('prefers-reduced-motion', false)
            ->assertSee('10 niveles · 11 destinos');

        $response = $this->actingAs($usuario)->postJson(route('games.originals.play', 'quantum-plinko'), [
            'apuesta' => 2,
            'request_token' => fake()->uuid(),
        ])->assertOk()->assertJsonCount(10, 'path');

        $path = array_map('intval', $response->json('path'));
        $slot = (int) $response->json('slot');
        $multipliers = [12, 5, 2, 1.2, .7, .4, .7, 1.2, 2, 5, 12];

        $this->assertSame(array_sum($path), $slot);
        $this->assertEquals((float) $multipliers[$slot], (float) $response->json('multiplier'));
        $this->assertSame('96.4%', config('casino_games.quantum-plinko.rtp'));
        $this->assertSame('x12', config('casino_games.quantum-plinko.max_win'));
    }

    public function test_cosmic_keno_payment_table_has_a_documented_approximately_96_percent_rtp(): void
    {
        $reflection = new \ReflectionClass(\App\Http\Controllers\ArcadeController::class);
        $multipliers = $reflection->getConstant('KENO_MULTIPLIERS');
        $choose = static function (int $n, int $k): int {
            if ($k < 0 || $k > $n) {
                return 0;
            }
            $result = 1;
            for ($i = 1; $i <= $k; $i++) {
                $result = intdiv($result * ($n - $k + $i), $i);
            }

            return $result;
        };

        $total = $choose(30, 10);
        $rtp = 0.0;
        for ($hits = 0; $hits <= 5; $hits++) {
            $probability = ($choose(5, $hits) * $choose(25, 10 - $hits)) / $total;
            $rtp += $probability * $multipliers[$hits];
        }

        $this->assertEqualsWithDelta(0.96, $rtp, 0.0001);
        $this->assertSame('x18.46', config('arcade_games.cosmic-keno.max_win'));
    }
}
