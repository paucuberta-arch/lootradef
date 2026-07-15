<?php

namespace Tests\Feature;

use App\Models\Usuario;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class WalletAndRoleTest extends TestCase
{
    use RefreshDatabase;

    public function test_role_accessors_use_the_first_assigned_role(): void
    {
        $usuario = $this->createUsuario('admin@example.com');
        $role = Role::create([
            'name' => 'admin',
            'guard_name' => 'web',
            'label' => 'Administrador',
            'color' => 'purple',
        ]);

        $usuario->assignRole($role);

        $this->assertSame('Administrador', $usuario->role_badge);
        $this->assertSame('purple', $usuario->role_color);
    }

    public function test_authenticated_user_can_fetch_the_current_database_balance(): void
    {
        $usuario = $this->createUsuario('player@example.com');
        $usuario->cartera()->create(['saldo' => 125.50]);

        $usuario->cartera()->update(['saldo' => 275.75]);

        $this->actingAs($usuario)
            ->getJson(route('perfil.saldo'))
            ->assertOk()
            ->assertJson(['saldo' => 275.75]);
    }

    public function test_admin_balance_update_creates_a_missing_wallet(): void
    {
        $admin = $this->createUsuario('admin@example.com');
        $adminRole = Role::create([
            'name' => 'admin',
            'guard_name' => 'web',
            'label' => 'Administrador',
            'color' => 'purple',
        ]);
        $userRole = Role::create([
            'name' => 'user',
            'guard_name' => 'web',
            'label' => 'Jugador',
            'color' => 'emerald',
        ]);
        $admin->assignRole($adminRole);

        $usuario = $this->createUsuario('target@example.com');

        $this->actingAs($admin)
            ->put(route('admin.users.update', $usuario), [
                'name' => $usuario->name,
                'email' => $usuario->email,
                'rol' => $userRole->name,
                'saldo' => 350.25,
            ])
            ->assertRedirect(route('admin.users'));

        $this->assertDatabaseHas('carteras', [
            'usuario_id' => $usuario->id,
            'saldo' => 350.25,
        ]);
    }

    private function createUsuario(string $email): Usuario
    {
        return Usuario::create([
            'name' => 'Test User',
            'email' => $email,
            'password' => bcrypt('password'),
        ]);
    }
}
