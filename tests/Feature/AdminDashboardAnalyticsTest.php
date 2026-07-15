<?php

namespace Tests\Feature;

use App\Models\Partida;
use App\Models\Usuario;
use Database\Seeders\RolesPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminDashboardAnalyticsTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_filter_and_view_operational_kpis(): void
    {
        $this->seed(RolesPermissionsSeeder::class);

        $admin = Usuario::create([
            'name' => 'Admin',
            'email' => 'analytics@example.com',
            'password' => bcrypt('password'),
        ]);
        $admin->assignRole('super_admin');
        $admin->cartera()->create(['saldo' => 1000]);

        Partida::create([
            'usuario_id' => $admin->id,
            'juego' => 'slots',
            'apuesta' => 100,
            'ganancia' => 70,
            'detalles' => ['resultado' => 'win'],
        ]);

        $this->actingAs($admin)
            ->get(route('admin.dashboard', ['period' => 7]))
            ->assertOk()
            ->assertSee('Visión general del negocio')
            ->assertSee('Volumen apostado (7d)')
            ->assertSee('100.00')
            ->assertSee('Payout real')
            ->assertSee('70.0%');
    }
}
