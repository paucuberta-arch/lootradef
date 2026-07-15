<?php

namespace Tests\Feature;

use App\Models\ApuestaDeportiva;
use App\Models\InventarioItem;
use App\Models\Partida;
use App\Models\PartidoDeportivo;
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

        $match = PartidoDeportivo::create([
            'liga' => 'Liga Admin', 'local' => 'Local', 'visitante' => 'Visitante',
            'local_siglas' => 'LOC', 'visitante_siglas' => 'VIS', 'inicia_at' => now()->addHour(),
            'duracion_segundos' => 360, 'cuota_local' => 2, 'cuota_empate' => 3, 'cuota_visitante' => 2.5,
            'simulacion' => [],
        ]);
        ApuestaDeportiva::create([
            'usuario_id' => $admin->id, 'partido_id' => $match->id, 'seleccion' => 'local',
            'cuota' => 2, 'importe' => 40, 'ganancia' => 80, 'estado' => 'ganada',
        ]);
        InventarioItem::create([
            'usuario_id' => $admin->id, 'caja' => 'neon', 'nombre' => 'Premio test',
            'imagen' => 'https://example.com/prize.png', 'rareza' => 'epico', 'precio_caja' => 25,
            'valor_canje' => 15, 'estado' => 'canjeado', 'canjeado_at' => now(),
        ]);

        $this->actingAs($admin)
            ->get(route('admin.dashboard', ['period' => 7]))
            ->assertOk()
            ->assertSee('Visión general del negocio')
            ->assertSee('Volumen apostado (7d)')
            ->assertSee('100.00')
            ->assertSee('Payout real')
            ->assertSee('70.0%')
            ->assertSee('Apuestas deportivas')
            ->assertSee('Rendimiento de cajas');

        $this->actingAs($admin)
            ->get(route('admin.charts', ['period' => 7]))
            ->assertOk()
            ->assertSee('Toda la plataforma, de un vistazo')
            ->assertSee('Flujo económico diario')
            ->assertSee('Estado de apuestas')
            ->assertSee('Rareza de premios');
    }
}
