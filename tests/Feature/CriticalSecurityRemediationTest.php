<?php

namespace Tests\Feature;

use App\Mail\AccountActivityMail;
use App\Models\CampaignChallengeMovement;
use App\Models\Usuario;
use App\Services\CampaignChallengeService;
use App\Services\GameBalanceService;
use Database\Seeders\DemoDataSeeder;
use Database\Seeders\PlatformActivitySeeder;
use Database\Seeders\RolesPermissionsSeeder;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use RuntimeException;
use Tests\TestCase;

class CriticalSecurityRemediationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesPermissionsSeeder::class);
        config([
            'campaigns.rickyedit.enabled' => true,
            'campaigns.rickyedit.start_at' => null,
            'campaigns.rickyedit.end_at' => null,
        ]);
    }

    public function test_campaign_idempotency_is_scoped_to_the_game_and_exact_replays_remain_safe(): void
    {
        $user = $this->player(100, verified: true);
        $challenge = app(CampaignChallengeService::class)->start($user);
        $balances = app(GameBalanceService::class);
        $token = (string) Str::uuid();

        $this->assertTrue($balances->debit($user, 'slots', 10, 'apuesta_test', [], null, $token, $challenge->id));
        $this->assertTrue($balances->debit($user, 'ruleta_european', 10, 'apuesta_test', [], null, $token, $challenge->id));
        $this->assertSame(980.0, (float) $challenge->fresh()->current_balance);
        $this->assertSame(2, CampaignChallengeMovement::where('direction', 'debit')->count());

        $this->assertTrue($balances->debit($user, 'slots', 10, 'apuesta_test', [], null, $token, $challenge->id));
        $this->assertSame(980.0, (float) $challenge->fresh()->current_balance);
        $this->assertSame(2, CampaignChallengeMovement::where('direction', 'debit')->count());
    }

    public function test_demo_seeders_refuse_to_run_in_production(): void
    {
        $original = $this->app->environment();
        $this->app->detectEnvironment(fn () => 'production');

        try {
            foreach ([DemoDataSeeder::class, PlatformActivitySeeder::class] as $seeder) {
                $thrown = null;
                try {
                    app($seeder)->run();
                } catch (RuntimeException $exception) {
                    $thrown = $exception;
                }
                $this->assertInstanceOf(RuntimeException::class, $thrown);
            }
        } finally {
            $this->app->detectEnvironment(fn () => $original);
        }

        $this->assertDatabaseMissing('usuarios', ['email' => 'admin@lootra.test']);
    }

    public function test_registration_rejects_email_header_injection(): void
    {
        Mail::fake();

        $this->post(route('registro.store'), [
            'name' => 'Header Test',
            'email' => "safe@example.test\r\nBcc: attacker@example.test",
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ])->assertSessionHasErrors('email');

        $this->assertDatabaseCount('usuarios', 0);
        Mail::assertNothingSent();
    }

    public function test_registration_bonus_is_granted_once_only_after_email_verification(): void
    {
        Mail::fake();
        Notification::fake();

        $this->post(route('registro.store'), [
            'name' => 'Verified Player',
            'email' => 'verified-player@example.test',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ])->assertRedirect(route('verification.notice'));

        $user = Usuario::where('email', 'verified-player@example.test')->firstOrFail();
        $this->assertNull($user->email_verified_at);
        $this->assertSame(0.0, (float) $user->cartera()->value('saldo'));
        Notification::assertSentTo($user, VerifyEmail::class);

        $url = URL::temporarySignedRoute('verification.verify', now()->addMinutes(60), [
            'id' => $user->id,
            'hash' => sha1($user->email),
        ]);
        $this->actingAs($user)->get($url)->assertRedirect(route('profile.show'));
        $this->assertNotNull($user->fresh()->email_verified_at);
        $this->assertSame(1000.0, (float) $user->cartera()->value('saldo'));

        $this->actingAs($user)->get($url)->assertRedirect(route('profile.show'));
        $this->assertSame(1000.0, (float) $user->cartera()->value('saldo'));
        $this->assertDatabaseCount('wallet_movements', 1);
    }

    public function test_unverified_user_cannot_start_campaign(): void
    {
        $user = $this->player(100, verified: false);

        $this->actingAs($user)->post(route('rickyedit.start'))
            ->assertRedirect(route('verification.notice'));
        $this->assertDatabaseCount('campaign_challenges', 0);
    }

    public function test_demo_deposit_is_disabled_for_production_defaults_and_normal_accounts(): void
    {
        $normal = $this->player(100, verified: true);
        $payload = ['amount' => 25, 'request_token' => (string) Str::uuid()];

        $this->actingAs($normal)->postJson(route('wallet.demo-deposit'), $payload)->assertNotFound();

        config(['features.demo_deposits.enabled' => true]);
        $this->actingAs($normal)->postJson(route('wallet.demo-deposit'), $payload)->assertForbidden();
        $this->assertSame(100.0, (float) $normal->cartera()->value('saldo'));
    }

    public function test_demo_deposit_is_limited_and_idempotent_for_demo_accounts(): void
    {
        Mail::fake();
        config(['features.demo_deposits.enabled' => true, 'features.demo_deposits.max_balance' => 150]);
        $demo = $this->player(100, verified: true, demo: true);
        $payload = ['amount' => 25, 'request_token' => (string) Str::uuid()];

        $this->actingAs($demo)->postJson(route('wallet.demo-deposit'), $payload)->assertOk()->assertJsonPath('saldo', 125);
        $this->actingAs($demo)->postJson(route('wallet.demo-deposit'), $payload)->assertOk()->assertJsonPath('saldo', 125);
        $this->assertDatabaseCount('wallet_movements', 1);
        Mail::assertQueued(AccountActivityMail::class, 1);

        $this->actingAs($demo)->postJson(route('wallet.demo-deposit'), [
            'amount' => 30,
            'request_token' => (string) Str::uuid(),
        ])->assertUnprocessable();
        $this->assertSame(125.0, (float) $demo->cartera()->value('saldo'));
    }

    private function player(float $balance, bool $verified, bool $demo = false): Usuario
    {
        $user = Usuario::create([
            'name' => 'Security Player '.Str::random(5),
            'email' => Str::uuid().'@example.test',
            'email_verified_at' => $verified ? now() : null,
            'password' => Hash::make('password123'),
            'is_demo' => $demo,
            'data_origin' => $demo ? 'simulated' : 'test',
        ]);
        $user->cartera()->create(['saldo' => $balance]);
        $user->assignRole('user');

        return $user;
    }
}
