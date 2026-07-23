<?php

namespace Tests\Feature;

use App\Models\ApuestaDeportiva;
use App\Models\Cartera;
use App\Models\PartidoDeportivo;
use App\Models\Usuario;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class SportsBettingSimulationTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_place_a_bet_and_balance_is_deducted(): void
    {
        [$user, $match] = $this->scenario();

        $token = (string) Str::uuid();
        $this->actingAs($user)->postJson(route('apuestas.place', $match), [
            'seleccion' => 'local',
            'importe' => 25,
            'request_token' => $token,
        ])->assertCreated()->assertJsonPath('balance', 75);

        $this->assertDatabaseHas('apuestas_deportivas', ['usuario_id' => $user->id, 'importe' => 25, 'cuota' => 2.0]);
        $this->assertEquals(75, $user->cartera->fresh()->saldo);
    }

    public function test_live_feed_advances_score_and_minute(): void
    {
        [, $match] = $this->scenario(now()->subSeconds(180));

        $this->artisan('sports:sync')->assertSuccessful();
        $this->getJson(route('apuestas.feed'))->assertOk()
            ->assertJsonPath('matches.0.status', 'en_vivo')
            ->assertJsonPath('matches.0.home_score', 1);

        $this->assertGreaterThanOrEqual(44, $match->fresh()->minuto);
    }

    public function test_repeated_sports_bet_token_returns_the_original_bet_without_a_second_debit(): void
    {
        [$user, $match] = $this->scenario();
        $token = (string) Str::uuid();
        $payload = ['seleccion' => 'local', 'importe' => 25, 'request_token' => $token];

        $firstResponse = $this->actingAs($user)->postJson(route('apuestas.place', $match), $payload)->assertCreated();
        $originalBetId = $firstResponse->json('bet.id');
        $this->actingAs($user)->postJson(route('apuestas.place', $match), $payload)
            ->assertOk()
            ->assertJsonPath('bet.id', $originalBetId)
            ->assertJsonPath('balance', 75);

        $this->assertDatabaseCount('apuestas_deportivas', 1);
        $this->assertDatabaseCount('wallet_movements', 1);
    }

    public function test_sports_bet_token_cannot_be_reused_with_different_parameters(): void
    {
        [$user, $match] = $this->scenario();
        $token = (string) Str::uuid();
        $payload = ['seleccion' => 'local', 'importe' => 25, 'request_token' => $token];

        $this->actingAs($user)->postJson(route('apuestas.place', $match), $payload)->assertCreated();
        $this->actingAs($user)->postJson(route('apuestas.place', $match), [
            ...$payload,
            'importe' => 30,
        ])->assertConflict();

        $this->assertDatabaseCount('apuestas_deportivas', 1);
        $this->assertEquals(75, $user->cartera->fresh()->saldo);
    }

    public function test_winning_bet_is_paid_only_once(): void
    {
        [$user, $match] = $this->scenario(now()->subSeconds(400));
        $user->cartera->update(['saldo' => 90]);
        ApuestaDeportiva::create([
            'usuario_id' => $user->id, 'partido_id' => $match->id, 'seleccion' => 'local',
            'cuota' => 2, 'importe' => 10,
        ]);

        $this->artisan('sports:sync')->assertSuccessful();
        $this->artisan('sports:sync')->assertSuccessful();

        $this->assertEquals(110, $user->cartera->fresh()->saldo);
        $this->assertDatabaseHas('apuestas_deportivas', ['partido_id' => $match->id, 'estado' => 'ganada', 'ganancia' => 20]);
    }

    public function test_betting_is_closed_after_minute_eighty(): void
    {
        [$user, $match] = $this->scenario(now()->subSeconds(330));

        $this->actingAs($user)->postJson(route('apuestas.place', $match), ['seleccion' => 'empate', 'importe' => 10, 'request_token' => (string) Str::uuid()])
            ->assertUnprocessable();

        $this->assertEquals(100, $user->cartera->fresh()->saldo);
    }

    private function scenario($startsAt = null): array
    {
        $user = Usuario::create([
            'name' => 'Sports Tester',
            'email' => 'sports'.uniqid().'@example.com',
            'password' => bcrypt('password'),
        ]);
        Cartera::create(['usuario_id' => $user->id, 'saldo' => 100]);
        $match = PartidoDeportivo::create([
            'liga' => 'Liga Test', 'local' => 'Equipo Local', 'visitante' => 'Equipo Visitante',
            'local_siglas' => 'LOC', 'visitante_siglas' => 'VIS', 'inicia_at' => $startsAt ?? now()->addMinute(),
            'duracion_segundos' => 360, 'cuota_local' => 2, 'cuota_empate' => 3, 'cuota_visitante' => 2.5,
            'simulacion' => [['minute' => 20, 'type' => 'goal', 'team' => 'local', 'text' => 'Gol local']],
        ]);

        return [$user, $match];
    }
}
