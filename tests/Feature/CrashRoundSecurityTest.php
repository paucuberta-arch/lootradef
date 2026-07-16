<?php

namespace Tests\Feature;

use App\Models\CrashRound;
use App\Models\Usuario;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class CrashRoundSecurityTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    public function test_start_hides_crash_point_and_prevents_a_second_active_round(): void
    {
        Carbon::setTestNow('2026-07-16 10:00:00');
        $user = $this->player();

        $start = $this->actingAs($user)->postJson(route('crash.play'), ['apuesta' => 10]);
        $start->assertCreated()
            ->assertJsonPath('estado', 'activa')
            ->assertJsonPath('crash_point', null)
            ->assertJsonPath('saldo', 90);

        $this->actingAs($user)->postJson(route('crash.play'), ['apuesta' => 10])
            ->assertConflict()->assertJsonPath('message', 'Ya tienes una ronda Crash activa.');

        $this->assertSame(90.0, (float) $user->cartera()->value('saldo'));
        $this->assertDatabaseCount('crash_rounds', 1);
    }

    public function test_cashout_ignores_a_manipulated_client_multiplier(): void
    {
        Carbon::setTestNow('2026-07-16 10:00:00');
        $user = $this->player();
        $roundId = $this->actingAs($user)->postJson(route('crash.play'), ['apuesta' => 10])->json('round_id');
        CrashRound::whereKey($roundId)->update(['crash_point' => 50]);

        Carbon::setTestNow('2026-07-16 10:00:01');
        $cashout = $this->actingAs($user)->postJson(route('crash.cashout'), [
            'round_id' => $roundId,
            'multiplier' => 49.99,
        ]);

        $cashout->assertOk()
            ->assertJsonPath('estado', 'cobrado')
            ->assertJsonPath('multiplier', 1.2)
            ->assertJsonPath('ganancia', 12)
            ->assertJsonPath('saldo', 102);
    }

    public function test_finished_round_is_idempotent_and_cannot_be_paid_twice(): void
    {
        Carbon::setTestNow('2026-07-16 10:00:00');
        $user = $this->player();
        $roundId = $this->actingAs($user)->postJson(route('crash.play'), ['apuesta' => 10])->json('round_id');
        CrashRound::whereKey($roundId)->update(['crash_point' => 50]);

        Carbon::setTestNow('2026-07-16 10:00:01');
        $first = $this->actingAs($user)->postJson(route('crash.cashout'), ['round_id' => $roundId])->assertOk();
        $second = $this->actingAs($user)->postJson(route('crash.cashout'), ['round_id' => $roundId])->assertOk();

        $this->assertSame($first->json('ganancia'), $second->json('ganancia'));
        $this->assertSame(102.0, (float) $user->cartera()->value('saldo'));
        $this->assertDatabaseCount('partidas', 1);
    }

    public function test_server_marks_round_as_crashed_at_its_persisted_point(): void
    {
        Carbon::setTestNow('2026-07-16 10:00:00');
        $user = $this->player();
        $roundId = $this->actingAs($user)->postJson(route('crash.play'), ['apuesta' => 10])->json('round_id');
        CrashRound::whereKey($roundId)->update(['crash_point' => 1.10]);

        Carbon::setTestNow('2026-07-16 10:00:00.500');
        $this->actingAs($user)->postJson(route('crash.status'), ['round_id' => $roundId])
            ->assertOk()
            ->assertJsonPath('estado', 'crashed')
            ->assertJsonPath('crash_point', 1.1)
            ->assertJsonPath('ganancia', 0)
            ->assertJsonPath('saldo', 90);

        $this->assertDatabaseHas('partidas', [
            'usuario_id' => $user->id,
            'juego' => 'crash',
            'apuesta' => 10,
            'ganancia' => 0,
        ]);
    }

    private function player(): Usuario
    {
        $user = Usuario::create([
            'name' => 'Crash Tester',
            'email' => uniqid().'@example.com',
            'password' => bcrypt('password'),
        ]);
        $user->cartera()->create(['saldo' => 100]);

        return $user;
    }
}
