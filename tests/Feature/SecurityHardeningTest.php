<?php

namespace Tests\Feature;

use App\Models\CampaignAttribution;
use App\Models\PartidoDeportivo;
use App\Models\Review;
use App\Models\Usuario;
use App\Providers\AppServiceProvider;
use App\Services\SecureRandom;
use App\Services\SportsSimulationService;
use Database\Seeders\RolesPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use RuntimeException;
use Tests\TestCase;

class SecurityHardeningTest extends TestCase
{
    use RefreshDatabase;

    public function test_secure_random_shuffle_preserves_every_item_and_pick_stays_in_range(): void
    {
        $random = app(SecureRandom::class);
        $source = range(1, 52);
        $shuffled = $random->shuffle($source);

        $this->assertCount(52, $shuffled);
        sort($shuffled);
        $this->assertSame($source, $shuffled);
        $this->assertContains($random->pick([50, 100, 200, 500]), [50, 100, 200, 500]);
    }

    public function test_campaign_attribution_bounds_and_normalizes_untrusted_query_data(): void
    {
        config([
            'campaigns.rickyedit.enabled' => true,
            'campaigns.rickyedit.start_at' => null,
            'campaigns.rickyedit.end_at' => null,
        ]);

        $this->get('/rickyedit?utm_source='.urlencode(str_repeat('A', 400)."\r\n"));

        $attribution = CampaignAttribution::firstOrFail();
        $this->assertSame(255, mb_strlen($attribution->utm_source));
        $this->assertStringNotContainsString("\r", $attribution->utm_source);
        $this->assertStringNotContainsString("\n", $attribution->utm_source);
    }

    public function test_new_campaign_sessions_are_limited_before_database_writes(): void
    {
        config([
            'campaigns.rickyedit.enabled' => true,
            'campaigns.rickyedit.start_at' => null,
            'campaigns.rickyedit.end_at' => null,
        ]);
        $ip = '203.0.113.45';
        $key = 'campaign-attribution:'.hash('sha256', $ip);
        RateLimiter::clear($key);
        for ($attempt = 0; $attempt < 60; $attempt++) {
            RateLimiter::hit($key, 60);
        }

        $this->withServerVariables(['REMOTE_ADDR' => $ip])
            ->get(route('rickyedit.landing'))
            ->assertTooManyRequests();

        $this->assertDatabaseCount('campaign_attributions', 0);
        $this->assertDatabaseCount('campaign_events', 0);
    }

    public function test_fixture_generation_is_idempotent(): void
    {
        $sports = app(SportsSimulationService::class);
        $sports->ensureFixtures();
        $sports->ensureFixtures();

        $this->assertSame(6, PartidoDeportivo::count());
        $this->assertSame(6, PartidoDeportivo::whereNotNull('fixture_key')->distinct()->count('fixture_key'));
    }

    public function test_analytics_requires_consent_and_consent_cookie_is_hardened(): void
    {
        config(['services.google_analytics.measurement_id' => 'G-TEST123456']);

        $this->get(route('inicio'))->assertOk()
            ->assertDontSee('googletagmanager.com', false)
            ->assertSee('No se cargará hasta que la aceptes.');

        $response = $this->post(route('privacy.analytics-consent'), ['choice' => 'granted'])
            ->assertRedirect(route('inicio'));
        $cookie = collect($response->headers->getCookies())->first(fn ($item) => $item->getName() === 'lootra_analytics_consent');
        $this->assertNotNull($cookie);
        $this->assertTrue($cookie->isHttpOnly());
        $this->assertSame('lax', strtolower((string) $cookie->getSameSite()));

        $this->withCookie('lootra_analytics_consent', 'granted')->get(route('inicio'))
            ->assertOk()->assertSee('googletagmanager.com', false);
    }

    public function test_reviews_are_moderated_and_unknown_game_slugs_are_rejected(): void
    {
        $user = $this->player();

        $this->actingAs($user)->postJson(route('reviews.store'), [
            'juego_slug' => 'not-a-game', 'contenido' => 'Contenido válido',
            'puntuacion' => 4, 'tipo' => 'juego',
        ])->assertUnprocessable();
        $this->assertDatabaseCount('reviews', 0);

        $this->actingAs($user)->postJson(route('reviews.store'), [
            'juego_slug' => 'starburst', 'contenido' => 'Contenido válido',
            'puntuacion' => 4, 'tipo' => 'juego',
        ])->assertOk();
        $this->assertSame('pendiente', Review::firstOrFail()->estado);
    }

    public function test_security_headers_and_private_cache_policy_are_present(): void
    {
        $public = $this->get(route('inicio'))->assertOk();
        $public->assertHeader('X-Content-Type-Options', 'nosniff');
        $public->assertHeader('X-Frame-Options', 'DENY');
        $public->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin');
        $csp = (string) $public->headers->get('Content-Security-Policy');
        $this->assertStringContainsString("default-src 'self'", $csp);
        $this->assertStringContainsString("frame-ancestors 'none'", $csp);
        $this->assertStringContainsString("object-src 'none'", $csp);
        $this->assertStringContainsString("connect-src 'self'", $csp);

        $private = $this->actingAs($this->player())->get(route('profile.show'))->assertOk();
        $this->assertStringContainsString('no-store', (string) $private->headers->get('Cache-Control'));
    }

    public function test_production_boot_fails_closed_for_insecure_configuration(): void
    {
        $original = $this->app->environment();
        $this->app->detectEnvironment(fn () => 'production');
        config([
            'app.debug' => true,
            'app.key' => '',
            'app.url' => 'http://localhost',
            'session.secure' => false,
            'security.admin_mfa_required' => false,
            'features.demo_deposits.enabled' => true,
        ]);

        $thrown = null;
        try {
            (new AppServiceProvider($this->app))->boot();
        } catch (RuntimeException $exception) {
            $thrown = $exception;
        } finally {
            $this->app->detectEnvironment(fn () => $original);
        }

        $this->assertInstanceOf(RuntimeException::class, $thrown);
        $this->assertStringNotContainsString('APP_KEY=', (string) $thrown?->getMessage());
    }

    public function test_case_prize_images_are_served_from_first_party_storage(): void
    {
        foreach (config('cajas') as $case) {
            foreach ($case['premios'] as $prize) {
                $this->assertStringStartsWith('/', $prize['imagen']);
                $this->assertFileExists(public_path(ltrim($prize['imagen'], '/')));
            }
        }
    }

    public function test_login_rate_limit_blocks_repeated_password_guessing(): void
    {
        $user = $this->player();
        for ($attempt = 0; $attempt < 5; $attempt++) {
            $this->post(route('login.store'), ['email' => $user->email, 'password' => 'incorrect-password'])
                ->assertSessionHasErrors('email');
        }

        $this->post(route('login.store'), ['email' => $user->email, 'password' => 'incorrect-password'])
            ->assertTooManyRequests();
    }

    public function test_registration_does_not_disclose_existing_email_addresses(): void
    {
        Mail::fake();
        $existing = $this->player();

        $this->from(route('registro'))->post(route('registro.store'), [
            'name' => 'Duplicate',
            'email' => $existing->email,
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ])->assertRedirect(route('registro'))
            ->assertSessionHasErrors('email');

        $this->assertStringNotContainsString('Ya existe', (string) session('errors')?->first('email'));
    }

    private function player(): Usuario
    {
        if (! app('db')->table('roles')->where('name', 'user')->exists()) {
            $this->seed(RolesPermissionsSeeder::class);
        }
        $user = Usuario::create([
            'name' => 'Hardened Player '.Str::random(4),
            'email' => Str::uuid().'@example.test',
            'email_verified_at' => now(),
            'password' => Hash::make('password123'),
        ]);
        $user->cartera()->create(['saldo' => 100]);
        $user->assignRole('user');

        return $user;
    }
}
