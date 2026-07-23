<?php

namespace Tests\Feature;

use App\Models\Feedback;
use App\Models\Review;
use App\Models\Usuario;
use Database\Seeders\RolesPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminPanelIntegrityTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesPermissionsSeeder::class);
    }

    public function test_moderator_does_not_see_a_charts_link_that_the_role_cannot_open(): void
    {
        $moderator = $this->user('moderator@example.com', 'moderator');

        $this->actingAs($moderator)->get(route('admin.dashboard'))
            ->assertOk()
            ->assertDontSee(route('admin.charts'));
        $this->actingAs($moderator)->get(route('admin.charts'))->assertForbidden();
    }

    public function test_read_only_permissions_do_not_render_mutation_buttons(): void
    {
        $admin = $this->user('readonly@example.com', 'admin');
        $admin->roles->first()->syncPermissions(['roles.view', 'reviews.view', 'feedback.view']);
        Review::create(['usuario_id' => $admin->id, 'contenido' => 'Pendiente', 'tipo' => 'web']);
        Feedback::create(['usuario_id' => $admin->id, 'asunto' => 'Consulta', 'contenido' => 'Contenido']);

        $this->actingAs($admin)->get(route('admin.roles'))->assertOk()
            ->assertDontSee('Crear nuevo rol')->assertDontSee('>Guardar<', false);
        $this->actingAs($admin)->get(route('admin.reviews'))->assertOk()
            ->assertDontSee('Aprobar')->assertDontSee('Rechazar')
            ->assertDontSee(route('admin.reviews.destroy', Review::first()));
        $this->actingAs($admin)->get(route('admin.feedback'))->assertOk()
            ->assertDontSee('Responder')
            ->assertDontSee(route('admin.feedback.destroy', Feedback::first()));
    }

    public function test_regular_admin_cannot_promote_a_user_to_super_admin(): void
    {
        $admin = $this->user('admin@example.com', 'admin');
        $player = $this->user('player@example.com', 'user');

        $this->actingAs($admin)->put(route('admin.users.update', $player), [
            'name' => $player->name,
            'email' => $player->email,
            'rol' => 'super_admin',
        ])->assertForbidden();

        $this->assertTrue($player->fresh()->hasRole('user'));
    }

    public function test_regular_admin_cannot_view_or_modify_a_super_admin_account(): void
    {
        $admin = $this->user('admin-hierarchy@example.com', 'admin');
        $superAdmin = $this->user('super-hierarchy@example.com', 'super_admin');

        $this->actingAs($admin)->get(route('admin.users.edit', $superAdmin))->assertForbidden();
        $this->actingAs($admin)->put(route('admin.users.update', $superAdmin), [
            'name' => 'Nombre manipulado',
            'email' => 'changed-super@example.com',
            'rol' => 'super_admin',
        ])->assertForbidden();

        $superAdmin->refresh();
        $this->assertSame('Super_admin', $superAdmin->name);
        $this->assertSame('super-hierarchy@example.com', $superAdmin->email);
    }

    public function test_admin_cannot_delete_a_super_admin_even_with_direct_delete_permission(): void
    {
        $admin = $this->user('admin-delete@example.com', 'admin');
        $admin->givePermissionTo('users.delete');
        $superAdmin = $this->user('super-delete@example.com', 'super_admin');

        $this->actingAs($admin)->delete(route('admin.users.destroy', $superAdmin))->assertForbidden();

        $this->assertDatabaseHas('usuarios', ['id' => $superAdmin->id]);
    }

    public function test_last_super_admin_cannot_demote_their_own_account(): void
    {
        $superAdmin = $this->user('last-super@example.com', 'super_admin');

        $this->actingAs($superAdmin)->put(route('admin.users.update', $superAdmin), [
            'name' => $superAdmin->name,
            'email' => $superAdmin->email,
            'rol' => 'admin',
        ])->assertConflict();

        $this->assertTrue($superAdmin->fresh()->hasRole('super_admin'));
    }

    public function test_users_can_be_filtered_by_role_and_combined_with_search(): void
    {
        $admin = $this->user('filter-admin@example.com', 'admin');
        $moderator = $this->user('visible-moderator@example.com', 'moderator');
        $this->user('hidden-player@example.com', 'user');

        $this->actingAs($admin)->get(route('admin.users', ['role' => 'moderator']))
            ->assertOk()
            ->assertSee($moderator->email)
            ->assertDontSee('hidden-player@example.com');

        $this->actingAs($admin)->get(route('admin.users', ['role' => 'moderator', 'search' => 'visible']))
            ->assertOk()
            ->assertSee($moderator->email);

        $this->actingAs($admin)->get(route('admin.users', ['role' => 'rol-inexistente']))
            ->assertOk()
            ->assertSee('Sin resultados.');
    }

    private function user(string $email, string $role): Usuario
    {
        $user = Usuario::create(['name' => ucfirst($role), 'email' => $email, 'password' => bcrypt('password')]);
        $user->cartera()->create(['saldo' => 100]);
        $user->assignRole($role);

        return $user;
    }
}
