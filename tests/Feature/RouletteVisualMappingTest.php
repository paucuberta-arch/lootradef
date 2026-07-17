<?php

namespace Tests\Feature;

use App\Models\Usuario;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RouletteVisualMappingTest extends TestCase
{
    use RefreshDatabase;

    public function test_frontend_receives_the_same_european_mapping_used_by_the_server(): void
    {
        $user = Usuario::create([
            'name' => 'Roulette Tester',
            'email' => 'roulette-map@example.com',
            'password' => bcrypt('password'),
        ]);
        $user->cartera()->create(['saldo' => 100]);

        $response = $this->actingAs($user)->get(route('games.roulette.european'));

        $response->assertOk();
        foreach (config('roulette.red_numbers') as $number) {
            $response->assertSee('data-pocket="'.$number.'"', false);
        }
        $response->assertSee(json_encode(config('roulette.red_numbers')), false);
        $response->assertSee('roulette-ball-track', false);
        $response->assertSee('roulette-spindle', false);
        $response->assertSee('European wheel · single zero', false);
        $response->assertSee('Promise.allSettled([wheelAnimation.finished,ballAnimation.finished])', false);
        $response->assertSee('this.animations.forEach(animation=>animation.cancel())', false);
        $response->assertDontSee('6500+Math.floor(Math.random()*1800)', false);
        $this->assertCount(37, array_unique(config('roulette.wheel_order')));
        $this->assertSame(0, config('roulette.wheel_order')[0]);
    }
}
