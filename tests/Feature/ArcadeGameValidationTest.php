<?php

namespace Tests\Feature;

use App\Models\Partida;
use App\Models\Usuario;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class ArcadeGameValidationTest extends TestCase
{
    use RefreshDatabase;

    public function test_invalid_choices_are_rejected_without_charging_the_wallet(): void
    {
        $user = $this->player();

        $this->actingAs($user)->postJson(route('arcade.play', 'dice-arena'), [
            'apuesta' => 10, 'choice' => 'always-win', 'request_token' => Str::uuid()->toString(),
        ])->assertUnprocessable();
        $this->actingAs($user)->postJson(route('arcade.play', 'cosmic-keno'), [
            'apuesta' => 10, 'numbers' => [1, 1, 2, 3, 4], 'request_token' => Str::uuid()->toString(),
        ])->assertUnprocessable();

        $this->assertSame(100.0, (float) $user->cartera()->value('saldo'));
        $this->assertDatabaseCount('partidas', 0);
    }

    public function test_repeated_request_token_returns_the_same_round_without_a_second_charge(): void
    {
        $user = $this->player();
        $token = Str::uuid()->toString();
        $payload = ['apuesta' => 10, 'choice' => 'heads', 'request_token' => $token];

        $first = $this->actingAs($user)->postJson(route('arcade.play', 'coin-duel'), $payload)->assertOk();
        $second = $this->actingAs($user)->postJson(route('arcade.play', 'coin-duel'), $payload)->assertOk();

        $this->assertSame($first->json('landed'), $second->json('landed'));
        $this->assertSame(1, Partida::where('request_token', $token)->count());
        $this->assertSame((float) $first->json('saldo'), (float) $second->json('saldo'));
    }

    private function player(): Usuario
    {
        $user = Usuario::create(['name' => 'Arcade Security', 'email' => 'arcade-security@example.com', 'password' => bcrypt('password')]);
        $user->cartera()->create(['saldo' => 100]);

        return $user;
    }
}
