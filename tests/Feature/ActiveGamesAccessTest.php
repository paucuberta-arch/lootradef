<?php

namespace Tests\Feature;

use App\Models\Review;
use App\Models\Usuario;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ActiveGamesAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_open_every_game_detail_including_games_with_reviews(): void
    {
        $usuario = $this->createPlayer();

        foreach ($this->gameSlugs() as $slug) {
            Review::create([
                'usuario_id' => $usuario->id,
                'juego_slug' => $slug,
                'titulo' => 'Review del juego',
                'contenido' => 'Contenido de prueba para comprobar la ficha.',
                'tipo' => 'juego',
                'estado' => 'aprobado',
                'puntuacion' => 4,
            ]);

            $this->actingAs($usuario)
                ->get(route('juego.show', $slug))
                ->assertOk()
                ->assertSee('Review del juego');
        }
    }

    public function test_authenticated_user_can_open_every_active_game_screen(): void
    {
        $usuario = $this->createPlayer();

        $routes = [
            route('slots', ['game' => 'gates-of-olympus']),
            route('slots', ['game' => 'sweet-bonanza']),
            route('slots', ['game' => 'book-of-dead']),
            route('slots', ['game' => 'starburst']),
            route('slots', ['game' => 'big-bass-bonanza']),
            route('ruleta'),
            route('ruleta.lightning'),
            route('blackjack'),
            route('crash'),
        ];

        foreach ($routes as $route) {
            $this->actingAs($usuario)->get($route)->assertOk();
        }
    }

    private function createPlayer(): Usuario
    {
        $usuario = Usuario::create([
            'name' => 'Game Tester',
            'email' => 'games@example.com',
            'password' => bcrypt('password'),
        ]);
        $usuario->cartera()->create(['saldo' => 1000]);

        return $usuario;
    }

    private function gameSlugs(): array
    {
        return array_values(array_unique([
            'gates-of-olympus', 'crazy-time', 'sweet-bonanza', 'european-roulette',
            'blackjack-vip', 'book-of-dead', 'crash-rocket', 'texas-holdem',
            'starburst', 'lightning-roulette', 'big-bass-bonanza', 'blackjack-classic',
            ...array_keys(config('arcade_games')),
        ]));
    }
}
