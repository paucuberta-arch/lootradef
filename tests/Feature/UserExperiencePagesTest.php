<?php

namespace Tests\Feature;

use App\Models\Partida;
use App\Models\Usuario;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserExperiencePagesTest extends TestCase
{
    use RefreshDatabase;

    public function test_navigation_has_canonical_sections_and_a_single_wallet_entry(): void
    {
        $user = $this->player();

        $response = $this->actingAs($user)->get(route('games.index'))->assertOk();

        $response->assertSee(route('sports.index'), false)
            ->assertSee(route('cases.index'), false)
            ->assertSee(route('wallet.show'), false)
            ->assertDontSee('quickDeposit', false)
            ->assertDontSee('Depositar rapido');
    }

    public function test_authentication_pages_have_accessible_password_controls_and_field_errors(): void
    {
        $this->get(route('login'))->assertOk()
            ->assertSee('Mostrar contraseña')
            ->assertSee('aria-invalid', false);

        $this->from(route('registro'))->post(route('registro.store'), [
            'name' => '', 'email' => 'incorrecto', 'password' => 'short', 'password_confirmation' => 'different',
        ])->assertRedirect(route('registro'))->assertSessionHasErrors(['name', 'email', 'password']);
    }

    public function test_profile_displays_operational_summary_and_recent_activity(): void
    {
        $user = $this->player();
        Partida::create(['usuario_id' => $user->id, 'juego' => 'slots', 'apuesta' => 10, 'ganancia' => 20]);
        $user->inventario()->create([
            'caja' => 'starter', 'nombre' => 'Premio de prueba', 'imagen' => '/images/game-fallback.svg', 'rareza' => 'raro',
            'precio_caja' => 5, 'valor_canje' => 12, 'estado' => 'disponible',
        ]);

        $this->actingAs($user)->get(route('profile.show'))->assertOk()
            ->assertSee('Inventario disponible')
            ->assertSee('Últimas partidas')
            ->assertSee('Premio de prueba')
            ->assertSee('Slots');
    }

    public function test_sports_page_uses_the_dedicated_visual_pack(): void
    {
        $this->get(route('sports.index'))->assertOk()
            ->assertSee('sports_hero_960x540.webp')
            ->assertSee('sports_stadium_1200x675.webp')
            ->assertSee('sports_ball_768x512.webp');
    }

    private function player(): Usuario
    {
        $user = Usuario::create(['name' => 'UX Tester', 'email' => fake()->unique()->safeEmail(), 'password' => bcrypt('password')]);
        $user->cartera()->create(['saldo' => 100]);

        return $user;
    }
}
