<?php

namespace Tests\Feature;

use App\Models\DemoWithdrawal;
use App\Models\LedgerEntry;
use App\Models\LedgerTransaction;
use App\Models\Usuario;
use Database\Seeders\RolesPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class LedgerAndDemoWithdrawalTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesPermissionsSeeder::class);
        config(['features.economy.enabled' => true]);
    }

    public function test_wallet_movement_creates_a_balanced_double_entry_transaction(): void
    {
        $user = $this->player(100);
        $user->cartera->apostar(25, 'apuesta_ledger', [], null, 'ledger-test-debit');

        $transaction = LedgerTransaction::where('type', 'apuesta_ledger')->latest()->firstOrFail();
        $this->assertSame(2, $transaction->entries()->count());
        $this->assertSame(
            (float) $transaction->entries()->where('direction', 'debit')->sum('amount'),
            (float) $transaction->entries()->where('direction', 'credit')->sum('amount')
        );
        $this->assertSame(2, LedgerEntry::where('ledger_transaction_id', $transaction->id)->count());
    }

    public function test_demo_withdrawal_reserves_balance_and_can_be_cancelled_idempotently(): void
    {
        $user = $this->player(100, demo: true);
        $token = (string) Str::uuid();
        $payload = ['amount' => 25, 'method_key' => 'demo_wallet', 'request_token' => $token];

        $first = $this->actingAs($user)->postJson(route('wallet.demo-withdrawals.store'), $payload)
            ->assertCreated()->json('withdrawal');
        $this->assertSame(75.0, (float) $user->cartera()->value('saldo'));
        $this->assertSame(25.0, (float) $this->app->make(\App\Services\DemoTreasuryService::class)->current()->reserved_balance);

        $second = $this->actingAs($user)->postJson(route('wallet.demo-withdrawals.store'), $payload)
            ->assertOk()->json('withdrawal');
        $this->assertSame($first['id'], $second['id']);
        $this->assertSame(1, DemoWithdrawal::where('request_token', $token)->count());

        $this->actingAs($user)->postJson(route('wallet.demo-withdrawals.cancel', $first['id']))->assertOk();
        $this->assertSame(100.0, (float) $user->cartera()->first()->fresh()->saldo);
        $this->assertSame(0.0, (float) $this->app->make(\App\Services\DemoTreasuryService::class)->current()->reserved_balance);
    }

    public function test_demo_withdrawal_rejection_releases_reserve_and_restores_balance(): void
    {
        $user = $this->player(100, demo: true);
        $admin = $this->player(100, demo: false);
        $admin->assignRole('admin');
        $admin->update(['admin_mfa_enabled_at' => now(), 'admin_mfa_verified_at' => now()]);
        $token = (string) Str::uuid();

        $this->actingAs($user)->postJson(route('wallet.demo-withdrawals.store'), [
            'amount' => 25, 'method_key' => 'demo_card', 'request_token' => $token,
        ])->assertCreated();
        $withdrawal = DemoWithdrawal::where('request_token', $token)->firstOrFail();

        $this->actingAs($admin)->postJson(route('admin.demo-withdrawals.review', $withdrawal), [
            'action' => 'reject', 'reason' => 'Prueba de revisión demo',
        ])->assertOk();

        $this->assertSame(DemoWithdrawal::REJECTED, $withdrawal->fresh()->status);
        $this->assertSame(100.0, (float) $user->cartera()->first()->fresh()->saldo);
    }

    public function test_approved_demo_withdrawal_completes_once_and_consumes_the_reserve(): void
    {
        $user = $this->player(100, demo: true);
        $admin = $this->player(100, demo: false);
        $admin->assignRole('admin');
        $admin->update(['admin_mfa_enabled_at' => now(), 'admin_mfa_verified_at' => now()]);
        $token = (string) Str::uuid();

        $this->actingAs($user)->postJson(route('wallet.demo-withdrawals.store'), [
            'amount' => 25, 'method_key' => 'demo_transfer', 'request_token' => $token,
        ])->assertCreated();
        $withdrawal = DemoWithdrawal::where('request_token', $token)->firstOrFail();

        $this->actingAs($admin)->postJson(route('admin.demo-withdrawals.review', $withdrawal), [
            'action' => 'approve',
        ])->assertOk();
        $this->actingAs($admin)->postJson(route('admin.demo-withdrawals.review', $withdrawal), [
            'action' => 'complete',
        ])->assertOk();

        $this->assertSame(DemoWithdrawal::COMPLETED, $withdrawal->fresh()->status);
        $this->assertSame(0.0, (float) $this->app->make(\App\Services\DemoTreasuryService::class)->current()->reserved_balance);
        $this->assertSame(75.0, (float) $user->cartera()->value('saldo'));
        $this->actingAs($admin)->postJson(route('admin.demo-withdrawals.review', $withdrawal), [
            'action' => 'complete',
        ])->assertConflict();
    }

    private function player(float $balance, bool $demo = false): Usuario
    {
        $user = Usuario::create([
            'name' => 'Ledger Player '.Str::random(5),
            'email' => Str::uuid().'@example.test',
            'email_verified_at' => now(),
            'password' => bcrypt('password123'),
            'is_demo' => $demo,
            'data_origin' => $demo ? 'simulated' : 'test',
        ]);
        $user->cartera()->create(['saldo' => $balance]);
        $user->assignRole('user');

        return $user;
    }
}
