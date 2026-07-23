<?php

namespace Tests\Feature;

use App\Models\InventarioItem;
use App\Models\Usuario;
use Database\Seeders\RolesPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminCaseHistoryTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesPermissionsSeeder::class);
    }

    public function test_admin_can_review_and_filter_case_opening_history(): void
    {
        $admin = $this->user('history-admin@example.com', 'admin');
        $player = $this->user('case-player@example.com', 'user');

        InventarioItem::create([
            'usuario_id' => $player->id,
            'caja' => 'starter',
            'nombre' => 'Altavoz Mini',
            'imagen' => '/test.webp',
            'rareza' => 'epico',
            'precio_caja' => 2.99,
            'valor_canje' => 14,
            'estado' => 'canjeado',
            'canjeado_at' => now(),
        ]);
        InventarioItem::create([
            'usuario_id' => $player->id,
            'caja' => 'gaming',
            'nombre' => 'Ratón Gaming Pro',
            'imagen' => '/test.webp',
            'rareza' => 'raro',
            'precio_caja' => 9.99,
            'valor_canje' => 18,
            'estado' => 'disponible',
        ]);

        $this->actingAs($admin)->get(route('admin.case-history.index', [
            'case' => 'starter',
            'status' => 'canjeado',
            'search' => 'case-player',
        ]))->assertOk()
            ->assertSee('Aperturas y premios entregados')
            ->assertSee('Altavoz Mini')
            ->assertSee('case-player@example.com')
            ->assertSee('2,99 €')
            ->assertSee('14,00 €')
            ->assertDontSee('Ratón Gaming Pro');

        $this->actingAs($admin)->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee(route('admin.case-history.index'), false);
    }

    public function test_case_history_requires_the_case_prize_permission(): void
    {
        $moderator = $this->user('history-moderator@example.com', 'moderator');

        $this->actingAs($moderator)->get(route('admin.case-history.index'))->assertForbidden();
    }

    private function user(string $email, string $role): Usuario
    {
        $user = Usuario::create([
            'name' => ucfirst($role),
            'email' => $email,
            'password' => bcrypt('password'),
        ]);
        $user->cartera()->create(['saldo' => 100]);
        $user->assignRole($role);

        return $user;
    }
}
