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

    public function test_hi_lo_uses_probability_adjusted_payouts_instead_of_a_fixed_exploitable_multiplier(): void
    {
        $user = $this->player();
        $winningResponse = null;

        for ($attempt = 0; $attempt < 30; $attempt++) {
            $response = $this->actingAs($user)
                ->withSession(['hilo_card_game' => 1])
                ->postJson(route('arcade.play', 'high-low'), [
                    'apuesta' => 1,
                    'choice' => 'higher',
                    'request_token' => Str::uuid()->toString(),
                ])->assertOk();

            if ((int) $response->json('next') > 1) {
                $winningResponse = $response;
                break;
            }
        }

        $this->assertNotNull($winningResponse, 'No se obtuvo una tirada ganadora para validar el pago Hi-Lo.');
        $this->assertSame(0.9567, (float) $winningResponse->json('multiplier'));
        $this->assertNotSame(1.9, (float) $winningResponse->json('multiplier'));
    }

    public function test_refreshing_hi_lo_does_not_replace_an_existing_visible_card(): void
    {
        $user = $this->player();

        $this->actingAs($user)
            ->withSession(['hilo_card_game' => 4])
            ->get(route('arcade', 'high-low'))
            ->assertOk()
            ->assertSessionHas('hilo_card_game', 4);
    }

    public function test_mines_payout_is_derived_from_the_selected_risk_at_the_declared_rtp(): void
    {
        $user = $this->player();
        $expectedMultipliers = [1 => 1.0473, 3 => 1.28, 5 => 1.6457, 8 => 2.88];

        foreach ($expectedMultipliers as $mines => $expected) {
            $winningResponse = null;

            for ($attempt = 0; $attempt < 50; $attempt++) {
                $response = $this->actingAs($user)->postJson(route('arcade.play', 'neon-mines'), [
                    'apuesta' => 0.2,
                    'choice' => $mines,
                    'request_token' => Str::uuid()->toString(),
                ])->assertOk();

                if ($response->json('safe')) {
                    $winningResponse = $response;
                    break;
                }
            }

            $this->assertNotNull($winningResponse, "No se obtuvo una tirada segura con {$mines} minas.");
            $this->assertSame($expected, (float) $winningResponse->json('multiplier'));
        }
    }

    private function player(): Usuario
    {
        $user = Usuario::create(['name' => 'Arcade Security', 'email' => 'arcade-security@example.com', 'password' => bcrypt('password')]);
        $user->cartera()->create(['saldo' => 100]);

        return $user;
    }
}
