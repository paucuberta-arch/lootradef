<?php

namespace Tests\Feature;

use App\Models\Usuario;
use Database\Seeders\RolesPermissionsSeeder;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AdditionalSecurityRemediationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesPermissionsSeeder::class);
    }

    public function test_user_api_response_is_an_explicit_public_allowlist(): void
    {
        $user = $this->user('api@example.test');
        $user->forceFill([
            'admin_mfa_secret' => 'GEZDGNBVGY3TQOJQGEZDGNBVGY3TQOJQ',
            'admin_mfa_enabled_at' => now(),
        ])->save();

        Sanctum::actingAs($user);

        $this->getJson('/api/user')
            ->assertOk()
            ->assertJsonStructure(['id', 'name', 'email', 'email_verified_at'])
            ->assertJsonMissing(['admin_mfa_secret'])
            ->assertJsonMissing(['password'])
            ->assertJsonMissing(['admin_mfa_enabled_at']);
    }

    public function test_password_reset_is_rate_limited_and_uses_single_use_tokens(): void
    {
        Notification::fake();
        $user = $this->user('reset@example.test');
        $user->createToken('test-session');

        $this->post(route('password.email'), ['email' => $user->email])
            ->assertSessionHas('status');
        Notification::assertSentTo($user, ResetPassword::class);

        $token = Password::broker()->createToken($user);
        $this->get(route('password.reset', ['token' => $token, 'email' => $user->email]))
            ->assertOk();

        $this->post(route('password.update'), [
            'token' => $token,
            'email' => $user->email,
            'password' => 'newpassword123',
            'password_confirmation' => 'newpassword123',
        ])->assertRedirect(route('login'));

        $this->assertTrue(Hash::check('newpassword123', $user->fresh()->password));
        $this->assertDatabaseCount('personal_access_tokens', 0);

        $this->post(route('password.update'), [
            'token' => $token,
            'email' => $user->email,
            'password' => 'anotherpassword123',
            'password_confirmation' => 'anotherpassword123',
        ])->assertSessionHasErrors('email');
    }

    public function test_admin_deactivation_keeps_financial_history(): void
    {
        config(['security.admin_mfa_required' => true]);
        $admin = $this->user('owner-admin@example.test');
        $admin->assignRole('super_admin');
        $admin->forceFill(['admin_mfa_enabled_at' => now()])->save();

        $target = $this->user('target@example.test');
        $target->cartera->apostar(10, 'test_debit');
        $movementId = $target->movimientosCartera()->value('id');

        $this->withSession([
            'admin_mfa_verified_at' => now()->timestamp,
            'admin_mfa_verified_user_id' => $admin->id,
        ])->actingAs($admin)
            ->delete(route('admin.users.destroy', $target))
            ->assertRedirect(route('admin.users'));

        $this->assertDatabaseHas('usuarios', ['id' => $target->id]);
        $this->assertNotNull($target->fresh()->deleted_at);
        $this->assertDatabaseHas('wallet_movements', ['id' => $movementId]);
    }

    private function user(string $email): Usuario
    {
        $user = Usuario::create([
            'name' => 'Security Test',
            'email' => $email,
            'email_verified_at' => now(),
            'password' => Hash::make('password123'),
        ]);
        $user->cartera()->create(['saldo' => 100]);

        return $user;
    }
}
