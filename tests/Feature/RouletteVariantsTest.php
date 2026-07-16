<?php

namespace Tests\Feature;

use App\Models\Usuario;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RouletteVariantsTest extends TestCase
{
    use RefreshDatabase;

    public function test_each_roulette_has_its_own_screen_and_play_route(): void
    {
        $user = Usuario::create(['name' => 'Roulette Tester', 'email' => 'roulette@example.com', 'password' => bcrypt('password')]);
        $user->cartera()->create(['saldo' => 100]);

        $this->actingAs($user)->get(route('ruleta'))->assertOk()->assertSee('Ruleta Europea');
        $this->actingAs($user)->get(route('ruleta.lightning'))->assertOk()->assertSee('Lightning Roulette')->assertSee('Números Lightning');

        $payload = ['apuesta' => 1, 'tipo' => 'numero', 'valor' => 7];
        $this->actingAs($user)->postJson(route('ruleta.play'), $payload)->assertOk()->assertJsonPath('multipliers', []);
        $this->actingAs($user)->postJson(route('ruleta.lightning.play'), $payload)->assertOk()->assertJsonCount(5, 'multipliers');

        $this->assertDatabaseHas('partidas', ['usuario_id' => $user->id, 'juego' => 'ruleta_european']);
        $this->assertDatabaseHas('partidas', ['usuario_id' => $user->id, 'juego' => 'ruleta_lightning']);
    }

    public function test_game_details_link_to_different_roulette_urls(): void
    {
        $user = Usuario::create(['name' => 'Link Tester', 'email' => 'links@example.com', 'password' => bcrypt('password')]);

        $european = $this->actingAs($user)->get(route('juego.show', 'european-roulette'))->assertOk();
        $lightning = $this->actingAs($user)->get(route('juego.show', 'lightning-roulette'))->assertOk();

        $european->assertSee(route('games.roulette.european'), false);
        $lightning->assertSee(route('games.roulette.lightning'), false);
        $this->assertNotSame(route('games.roulette.european'), route('games.roulette.lightning'));
    }
}
