<?php

namespace Tests\Feature;

use App\Models\Usuario;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GameRouteCompatibilityTest extends TestCase
{
    use RefreshDatabase;

    public function test_canonical_game_routes_require_authentication(): void
    {
        foreach ([
            route('games.crash.show'),
            route('games.slots.show', 'sweet-bonanza'),
            route('games.roulette.european'),
            route('games.blackjack.classic'),
            route('games.poker.dealer'),
            route('games.originals.show', 'coin-duel'),
        ] as $url) {
            $this->get($url)->assertRedirect(route('login'));
        }
    }

    public function test_canonical_and_legacy_get_urls_remain_available(): void
    {
        $user = $this->createPlayer('compatibility@example.com');

        foreach ([
            [route('games.crash.show'), route('crash')],
            [route('games.slots.show', 'sweet-bonanza'), route('slots', ['game' => 'sweet-bonanza'])],
            [route('games.roulette.european'), route('ruleta')],
            [route('games.blackjack.vip'), route('blackjack')],
            [route('games.poker.dealer'), route('poker.dealer')],
            [route('games.originals.show', 'coin-duel'), route('arcade', 'coin-duel')],
        ] as [$canonical, $legacy]) {
            $this->actingAs($user)->get($canonical)->assertOk();
            $this->actingAs($user)->get($legacy)->assertOk();
        }

        $this->get(route('games.show', 'sweet-bonanza'))->assertOk();
        $this->get(route('juego.show', 'sweet-bonanza'))->assertOk();
    }

    public function test_new_navigation_urls_are_available(): void
    {
        $user = $this->createPlayer('navigation@example.com');

        $this->get(route('sports.index'))->assertOk();
        $this->get(route('cases.index'))->assertOk();
        $this->actingAs($user)->get(route('profile.show'))->assertOk();
        $this->actingAs($user)->get(route('wallet.history'))->assertOk();
    }

    private function createPlayer(string $email): Usuario
    {
        $user = Usuario::create(['name' => 'Route Tester', 'email' => $email, 'password' => bcrypt('password')]);
        $user->cartera()->create(['saldo' => 100]);

        return $user;
    }
}
