<?php

namespace Tests\Feature;

use App\Models\Usuario;
use App\Services\TotpService;
use Carbon\Carbon;
use Database\Seeders\RolesPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Tests\TestCase;

class AdminMfaSecurityTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesPermissionsSeeder::class);
        config(['security.admin_mfa_required' => true]);
    }

    public function test_totp_matches_rfc6238_six_digit_vector(): void
    {
        $secret = 'GEZDGNBVGY3TQOJQGEZDGNBVGY3TQOJQ';

        $this->assertTrue(app(TotpService::class)->verify($secret, '287082', Carbon::createFromTimestamp(59)));
        $this->assertFalse(app(TotpService::class)->verify($secret, '287083', Carbon::createFromTimestamp(59)));
    }

    public function test_production_admin_cannot_use_panel_until_mfa_is_enabled_and_verified(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)->get(route('admin.dashboard'))
            ->assertRedirect(route('admin.mfa.setup'));

        $secret = 'GEZDGNBVGY3TQOJQGEZDGNBVGY3TQOJQ';
        $this->withSession(['admin_mfa_pending_secret' => $secret])
            ->actingAs($admin)
            ->post(route('admin.mfa.enable'), [
                'code' => $this->totpCode($secret, now()->timestamp),
                'password' => 'password123',
            ])->assertRedirect(route('admin.dashboard'));

        $this->assertNotNull($admin->fresh()->admin_mfa_enabled_at);
        $this->actingAs($admin)->get(route('admin.dashboard'))->assertOk();

        $this->withSession(['admin_mfa_verified_at' => 0])
            ->actingAs($admin)
            ->get(route('admin.dashboard'))
            ->assertRedirect(route('admin.mfa.challenge'));

        $this->withSession(['admin_mfa_verified_at' => 0])
            ->actingAs($admin)
            ->post(route('admin.mfa.verify'), ['code' => '000000'])
            ->assertSessionHasErrors('code');

        $this->withSession(['admin_mfa_verified_at' => 0])
            ->actingAs($admin)
            ->post(route('admin.mfa.verify'), ['code' => $this->totpCode($secret, now()->timestamp)])
            ->assertRedirect(route('admin.dashboard'));
    }

    public function test_the_same_totp_counter_cannot_be_replayed(): void
    {
        $admin = $this->admin();
        $secret = 'GEZDGNBVGY3TQOJQGEZDGNBVGY3TQOJQ';
        $timestamp = 600;
        $admin->forceFill([
            'admin_mfa_secret' => $secret,
            'admin_mfa_enabled_at' => now(),
            'admin_mfa_last_counter' => null,
        ])->save();

        Carbon::setTestNow(Carbon::createFromTimestamp($timestamp));
        try {
            $payload = ['code' => $this->totpCode($secret, $timestamp)];
            $this->actingAs($admin)->post(route('admin.mfa.verify'), $payload)->assertRedirect();

            $this->actingAs($admin)->post(route('admin.mfa.verify'), $payload)
                ->assertSessionHasErrors('code');
        } finally {
            Carbon::setTestNow();
        }
    }

    private function admin(): Usuario
    {
        $admin = Usuario::create([
            'name' => 'Security Admin',
            'email' => Str::uuid().'@example.test',
            'password' => Hash::make('password123'),
            'email_verified_at' => now(),
        ]);
        $admin->cartera()->create(['saldo' => 0]);
        $admin->assignRole('admin');

        return $admin;
    }

    private function totpCode(string $secret, int $timestamp): string
    {
        $alphabet = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';
        $bits = '';
        foreach (str_split($secret) as $character) {
            $bits .= str_pad(decbin(strpos($alphabet, $character)), 5, '0', STR_PAD_LEFT);
        }
        $key = '';
        foreach (str_split($bits, 8) as $chunk) {
            if (strlen($chunk) === 8) {
                $key .= chr(bindec($chunk));
            }
        }
        $hash = hash_hmac('sha1', pack('J', intdiv($timestamp, 30)), $key, true);
        $offset = ord($hash[19]) & 0x0F;
        $value = ((ord($hash[$offset]) & 0x7F) << 24)
            | ((ord($hash[$offset + 1]) & 0xFF) << 16)
            | ((ord($hash[$offset + 2]) & 0xFF) << 8)
            | (ord($hash[$offset + 3]) & 0xFF);

        return str_pad((string) ($value % 1_000_000), 6, '0', STR_PAD_LEFT);
    }
}
