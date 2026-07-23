<?php

namespace Tests\Feature;

use App\Http\Controllers\RuletaController;
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

    public function test_lightning_number_configuration_stays_below_one_hundred_percent_expected_return(): void
    {
        $reflection = new \ReflectionClass(RuletaController::class);
        $baseMultiplier = (float) $reflection->getConstant('LIGHTNING_BASE_MULTIPLIER');
        $boosts = $reflection->getConstant('LIGHTNING_BOOST_MULTIPLIERS');
        $averageBoost = array_sum($boosts) / count($boosts);

        $expectedReturn = ((5 * $averageBoost) + (32 * $baseMultiplier)) / (37 * 37);

        $this->assertGreaterThanOrEqual(0.97, $expectedReturn);
        $this->assertLessThan(1, $expectedReturn);
        $this->assertEqualsWithDelta(0.973, $expectedReturn, 0.001);
    }

    public function test_european_single_number_win_returns_the_stake_plus_thirty_five_to_one(): void
    {
        $controller = app(RuletaController::class);
        $method = new \ReflectionMethod($controller, 'calculateWin');

        $this->assertSame(36.0, $method->invoke($controller, 'numero', 17, 17, 'negro', 1.0, 36.0));
    }
}
