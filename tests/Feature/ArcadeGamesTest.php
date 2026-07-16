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
                ->postJson(route('arcade.play', $slug), ['apuesta' => 1, ...$payload])
                ->assertOk()
                ->assertJsonStructure(['multiplier', 'ganancia', 'saldo']);
        }

        $this->assertSame(10, Partida::where('usuario_id', $usuario->id)->count());
    }
}
