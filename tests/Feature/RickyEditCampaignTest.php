<?php

namespace Tests\Feature;

use App\Models\CampaignChallenge;
use App\Models\CampaignEvent;
use App\Models\Usuario;
use Carbon\Carbon;
use Database\Seeders\RickyEditCampaignSeeder;
use Database\Seeders\RolesPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Tests\TestCase;

class RickyEditCampaignTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config([
            'campaigns.rickyedit.enabled' => true,
            'campaigns.rickyedit.start_at' => null,
            'campaigns.rickyedit.end_at' => null,
            'campaigns.rickyedit.initial_balance' => 1000,
            'campaigns.rickyedit.duration_minutes' => 15,
        ]);
        $this->seed(RolesPermissionsSeeder::class);
    }

    public function test_campaign_visibility_obeys_configuration(): void
    {
        config(['campaigns.rickyedit.enabled' => false]);
        $this->get('/')->assertOk()
            ->assertDontSee('Reto RickyEdit')
            ->assertSee('lootra-hero.webp');

        config(['campaigns.rickyedit.enabled' => true]);
        $this->get('/')->assertOk()
            ->assertSee('Reto RickyEdit')
            ->assertSee('aifaceswap-390bed6ee726c1170c6d1df14ca5d5b4.webp')
            ->assertSee('aifaceswap-a2a4b24b66d4ab9e64b25f2d6df8e767.webp')
            ->assertDontSee('lootra-hero.webp');

        $this->get(route('rickyedit.landing'))->assertOk()
            ->assertSee('challenge-hero-v2.webp')
            ->assertSee('ricky_edit2-removebg-preview.webp')
            ->assertDontSee('ricky-edit2.webp')
            ->assertSee('Trofeo dorado rodeado de energía violeta');
    }

    public function test_registration_keeps_attribution_and_redirects_to_intro(): void
    {
        $this->get('/rickyedit?utm_source=youtube&utm_medium=creator&utm_campaign=reto&utm_content=video');
        $response = $this->post('/registrarse', [
            'name' => 'Jugador Campaña', 'email' => 'campaign@example.test',
            'password' => 'password123', 'password_confirmation' => 'password123',
        ]);

        $response->assertRedirect(route('rickyedit.intro'));
        $this->assertDatabaseHas('campaign_attributions', [
            'user_id' => Usuario::where('email', 'campaign@example.test')->value('id'),
            'utm_source' => 'youtube', 'utm_medium' => 'creator', 'utm_content' => 'video',
        ]);
        $this->assertDatabaseHas('campaign_events', ['event' => 'registration_completed']);
    }

    public function test_challenge_starts_once_and_expires_after_exactly_fifteen_minutes(): void
    {
        Carbon::setTestNow('2026-07-17 12:00:00');
        $user = $this->user();
        $this->actingAs($user)->post(route('rickyedit.start'))
            ->assertRedirect(route('games.index'))
            ->assertSessionHas('ga_reto_iniciado', true);
        $challenge = CampaignChallenge::firstOrFail();

        $this->assertSame('2026-07-17 12:15:00', $challenge->expires_at->format('Y-m-d H:i:s'));
        $this->actingAs($user)->post(route('rickyedit.start'))
            ->assertSessionMissing('ga_reto_iniciado');
        $this->assertDatabaseCount('campaign_challenges', 1);

        Carbon::setTestNow('2026-07-17 12:15:00');
        $this->actingAs($user)->get(route('rickyedit.status'))->assertOk()->assertJson(['status' => 'expired']);
        $this->assertDatabaseHas('campaign_challenges', ['id' => $challenge->id, 'status' => 'expired', 'final_balance' => 1000]);
        Carbon::setTestNow();
    }

    public function test_campaign_balance_is_separate_from_normal_wallet(): void
    {
        $user = $this->user(4200);
        $this->actingAs($user)->post(route('rickyedit.start'));

        $this->actingAs($user)->postJson(route('games.slots.play', ['slug' => 'starburst']), [
            'apuesta' => 10, 'game' => 'starburst', 'request_token' => (string) Str::uuid(),
        ])->assertOk();

        $this->assertSame(4200.0, (float) $user->cartera()->value('saldo'));
        $challenge = CampaignChallenge::firstOrFail();
        $this->assertNotSame(1000.0, (float) $challenge->current_balance);
        $this->assertDatabaseHas('partidas', ['campaign_challenge_id' => $challenge->id, 'campaign_key' => 'rickyedit']);
    }

    public function test_campaign_surfaces_render_during_an_active_challenge(): void
    {
        $user = $this->user(4200);
        $this->actingAs($user)->post(route('rickyedit.start'));

        foreach ([
            route('profile.show'), route('games.slots.show', ['slug' => 'starburst']),
            route('games.roulette.european'), route('games.blackjack.classic'),
            route('games.crash.show'), route('games.poker.dealer'),
            route('games.originals.show', ['game' => 'dice-arena']), route('cases.index'),
        ] as $url) {
            $this->actingAs($user)->get($url)->assertOk();
        }
    }

    public function test_manual_completion_freezes_the_result(): void
    {
        $user = $this->user();
        $this->actingAs($user)->post(route('rickyedit.start'));
        $this->actingAs($user)->post(route('rickyedit.finish'))->assertRedirect(route('rickyedit.ranking'));
        $challenge = CampaignChallenge::firstOrFail();

        $this->assertSame('completed', $challenge->status);
        $this->assertSame((float) $challenge->current_balance, (float) $challenge->final_balance);
        $this->actingAs($user)->postJson(route('games.slots.play', ['slug' => 'starburst']), [
            'apuesta' => 10, 'game' => 'starburst', 'request_token' => (string) Str::uuid(),
        ])->assertOk();
        $this->assertSame((float) $challenge->final_balance, (float) $challenge->fresh()->final_balance);
    }

    public function test_ranking_excludes_demo_and_administrative_entries(): void
    {
        $real = $this->user();
        $demo = $this->user();
        $demo->update(['is_demo' => true, 'data_origin' => 'simulated']);
        $admin = $this->user();
        $admin->syncRoles('admin');
        $this->completed($real, 'Real#001', 1400, false, 'real');
        $this->completed($demo, 'Demo#999', 9000, true, 'simulated');
        $this->completed($admin, 'Admin#999', 8000, false, 'real');

        $this->get(route('rickyedit.ranking'))->assertOk()
            ->assertSee('Real#001')->assertDontSee('Demo#999')->assertDontSee('Admin#999')
            ->assertDontSee($real->email);
    }

    public function test_campaign_admin_analytics_requires_dedicated_permission(): void
    {
        $player = $this->user();
        $this->actingAs($player)->get(route('admin.campaigns.rickyedit'))->assertForbidden();

        $admin = $this->user();
        $admin->syncRoles('admin');
        $this->actingAs($admin)->get(route('admin.campaigns.rickyedit'))->assertOk()->assertSee('Datos reales');
    }

    public function test_simulated_data_never_appears_in_real_admin_metrics(): void
    {
        CampaignEvent::create([
            'campaign_key' => 'rickyedit', 'event' => 'landing_view', 'dedupe_key' => 'simulated-only',
            'source' => 'test', 'is_demo' => true, 'data_origin' => 'simulated', 'occurred_at' => now(),
        ]);
        $admin = $this->user();
        $admin->syncRoles('admin');

        $this->actingAs($admin)->get(route('admin.campaigns.rickyedit', ['origin' => 'real']))
            ->assertOk()->assertViewHas('stats', fn (array $stats) => $stats['visits'] === 0);
    }

    public function test_campaign_seeder_marks_every_campaign_record_as_simulated_demo_data(): void
    {
        $this->seed(RickyEditCampaignSeeder::class);

        $this->assertGreaterThan(0, CampaignChallenge::where('data_origin', 'simulated')->count());
        $this->assertSame(0, CampaignChallenge::where('data_origin', 'simulated')->where('is_demo', false)->count());
        $this->assertSame(0, CampaignEvent::where('data_origin', 'simulated')->where('is_demo', false)->count());
        $this->assertSame(0, Usuario::where('data_origin', 'simulated')->where('is_demo', false)->count());
        $this->get(route('rickyedit.ranking'))->assertOk()->assertDontSee('Simulado#');
    }

    private function user(float $balance = 1000): Usuario
    {
        $user = Usuario::create([
            'name' => 'Test Player '.Str::random(5),
            'email' => Str::uuid().'@example.test',
            'password' => Hash::make('password'),
        ]);
        $user->cartera()->create(['saldo' => $balance]);
        $user->assignRole('user');

        return $user;
    }

    private function completed(Usuario $user, string $alias, float $score, bool $demo, string $origin): CampaignChallenge
    {
        return CampaignChallenge::create([
            'user_id' => $user->id, 'campaign_key' => 'rickyedit', 'public_alias' => $alias,
            'status' => 'completed', 'initial_balance' => 1000, 'current_balance' => $score,
            'final_balance' => $score, 'score' => $score, 'started_at' => now()->subMinutes(15),
            'expires_at' => now(), 'completed_at' => now(), 'games_played' => 10,
            'is_demo' => $demo, 'data_origin' => $origin,
        ]);
    }
}
