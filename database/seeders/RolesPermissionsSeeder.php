<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\Permission;
use Illuminate\Database\Seeder;

class RolesPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            // Users
            'users.view' => ['label' => 'Ver usuarios', 'group' => 'Usuarios'],
            'users.create' => ['label' => 'Crear usuarios', 'group' => 'Usuarios'],
            'users.edit' => ['label' => 'Editar usuarios', 'group' => 'Usuarios'],
            'users.delete' => ['label' => 'Eliminar usuarios', 'group' => 'Usuarios'],
            'users.ban' => ['label' => 'Banear usuarios', 'group' => 'Usuarios'],

            // Games
            'games.view' => ['label' => 'Ver juegos', 'group' => 'Juegos'],
            'games.create' => ['label' => 'Crear juegos', 'group' => 'Juegos'],
            'games.edit' => ['label' => 'Editar juegos', 'group' => 'Juegos'],
            'games.delete' => ['label' => 'Eliminar juegos', 'group' => 'Juegos'],

            // Reviews
            'reviews.view' => ['label' => 'Ver reviews', 'group' => 'Reviews'],
            'reviews.moderate' => ['label' => 'Moderar reviews', 'group' => 'Reviews'],
            'reviews.delete' => ['label' => 'Eliminar reviews', 'group' => 'Reviews'],

            // Feedback
            'feedback.view' => ['label' => 'Ver feedback', 'group' => 'Feedback'],
            'feedback.respond' => ['label' => 'Responder feedback', 'group' => 'Feedback'],
            'feedback.close' => ['label' => 'Cerrar feedback', 'group' => 'Feedback'],

            // Roles
            'roles.view' => ['label' => 'Ver roles', 'group' => 'Roles'],
            'roles.manage' => ['label' => 'Gestionar roles', 'group' => 'Roles'],

            // Stats
            'stats.view' => ['label' => 'Ver estadisticas', 'group' => 'Estadisticas'],
            'logs.view' => ['label' => 'Ver logs de actividad', 'group' => 'Estadisticas'],

            // Wallet
            'wallet.manage' => ['label' => 'Gestionar carteras', 'group' => 'Cartera'],
        ];

        foreach ($permissions as $name => $attrs) {
            Permission::updateOrCreate(
                ['name' => $name],
                ['label' => $attrs['label'], 'group' => $attrs['group']]
            );
        }

        $roles = [
            'super_admin' => [
                'label' => 'Super Admin',
                'color' => 'red',
                'permissions' => array_keys($permissions),
            ],
            'admin' => [
                'label' => 'Administrador',
                'color' => 'purple',
                'permissions' => [
                    'users.view', 'users.create', 'users.edit', 'users.ban',
                    'games.view', 'games.create', 'games.edit', 'games.delete',
                    'reviews.view', 'reviews.moderate', 'reviews.delete',
                    'feedback.view', 'feedback.respond', 'feedback.close',
                    'stats.view', 'logs.view',
                    'wallet.manage',
                ],
            ],
            'creator' => [
                'label' => 'Creador',
                'color' => 'blue',
                'permissions' => [
                    'games.view', 'games.create', 'games.edit',
                    'reviews.view',
                    'stats.view',
                ],
            ],
            'moderator' => [
                'label' => 'Moderador',
                'color' => 'amber',
                'permissions' => [
                    'users.view',
                    'games.view',
                    'reviews.view', 'reviews.moderate', 'reviews.delete',
                    'feedback.view', 'feedback.respond', 'feedback.close',
                ],
            ],
            'user' => [
                'label' => 'Jugador',
                'color' => 'emerald',
                'permissions' => [
                    'games.view',
                    'reviews.view',
                ],
            ],
        ];

        foreach ($roles as $name => $attrs) {
            $role = Role::updateOrCreate(
                ['name' => $name],
                ['label' => $attrs['label'], 'color' => $attrs['color']]
            );
            $role->permissions()->sync(
                Permission::whereIn('name', $attrs['permissions'])->pluck('id')
            );
        }
    }
}
