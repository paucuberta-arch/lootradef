<?php

namespace Tests\Feature;

use App\Models\Cartera;
use App\Models\Usuario;
use App\Services\WalletService;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class WalletLedgerTest extends TestCase
{
    use RefreshDatabase;

    public function test_debits_and_credits_record_balances_without_going_negative(): void
    {
        $user = $this->player();

        $this->assertTrue($user->cartera->apostar(25, 'apuesta_prueba'));
        $user->cartera->ganar(40, 'premio_prueba');
        $this->assertFalse($user->cartera->apostar(1000, 'apuesta_rechazada'));

        $this->assertSame(115.0, (float) $user->cartera->fresh()->saldo);
        $this->assertDatabaseHas('wallet_movements', [
            'usuario_id' => $user->id, 'tipo' => 'apuesta_prueba', 'direccion' => 'debito',
            'importe' => 25, 'saldo_anterior' => 100, 'saldo_posterior' => 75,
        ]);
        $this->assertDatabaseHas('wallet_movements', [
            'usuario_id' => $user->id, 'tipo' => 'premio_prueba', 'direccion' => 'credito',
            'importe' => 40, 'saldo_anterior' => 75, 'saldo_posterior' => 115,
        ]);
        $this->assertDatabaseMissing('wallet_movements', ['tipo' => 'apuesta_rechazada']);
    }

    public function test_idempotency_key_prevents_a_credit_from_being_applied_twice(): void
    {
        $user = $this->player();
        $service = app(WalletService::class);

        $service->credit($user->cartera, 20, 'premio_idempotente', [], null, 'award:123');
        $service->credit($user->cartera, 20, 'premio_idempotente', [], null, 'award:123');

        $this->assertSame(120.0, (float) $user->cartera->fresh()->saldo);
        $this->assertDatabaseCount('wallet_movements', 1);
    }

    public function test_demo_deposit_is_visible_in_profile_history(): void
    {
        Mail::fake();
        $user = $this->player();

        $this->actingAs($user)->postJson(route('perfil.deposit'), ['amount' => 25])
            ->assertOk()->assertJsonPath('saldo', 125);

        $this->actingAs($user)->get(route('perfil'))
            ->assertOk()->assertSee('Depósito demo')->assertSee('25,00 €');
        $this->assertDatabaseHas('wallet_movements', ['tipo' => 'deposito_demo', 'importe' => 25]);
    }

    public function test_each_user_can_only_have_one_wallet(): void
    {
        $user = $this->player();

        $this->expectException(QueryException::class);
        Cartera::create(['usuario_id' => $user->id, 'saldo' => 10]);
    }

    private function player(): Usuario
    {
        $user = Usuario::create([
            'name' => 'Wallet Tester',
            'email' => uniqid().'@example.com',
            'password' => bcrypt('password'),
        ]);
        $user->cartera()->create(['saldo' => 100]);

        return $user;
    }
}
