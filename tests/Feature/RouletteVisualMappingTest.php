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
        $this->assertCount(37, array_unique(config('roulette.wheel_order')));
        $this->assertSame(0, config('roulette.wheel_order')[0]);
    }
}
