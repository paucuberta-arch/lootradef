<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolesPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $permissions = [
            'users.view'      => ['label' => 'Ver usuarios',       'group' => 'Usuarios'],
            'users.create'    => ['label' => 'Crear usuarios',     'group' => 'Usuarios'],
            'users.edit'      => ['label' => 'Editar usuarios',    'group' => 'Usuarios'],
            'users.delete'    => ['label' => 'Eliminar usuarios',  'group' => 'Usuarios'],
            'users.ban'       => ['label' => 'Banear usuarios',    'group' => 'Usuarios'],

            'games.view'      => ['label' => 'Ver juegos',         'group' => 'Juegos'],
            'games.create'    => ['label' => 'Crear juegos',       'group' => 'Juegos'],
            'games.edit'      => ['label' => 'Editar juegos',      'group' => 'Juegos'],
            'games.delete'    => ['label' => 'Eliminar juegos',    'group' => 'Juegos'],

            'reviews.view'    => ['label' => 'Ver reviews',        'group' => 'Reviews'],
            'reviews.moderate'=> ['label' => 'Moderar reviews',    'group' => 'Reviews'],
            'reviews.delete'  => ['label' => 'Eliminar reviews',   'group' => 'Reviews'],

            'feedback.view'   => ['label' => 'Ver feedback',       'group' => 'Feedback'],
            'feedback.respond'=> ['label' => 'Responder feedback', 'group' => 'Feedback'],
            'feedback.close'  => ['label' => 'Cerrar feedback',    'group' => 'Feedback'],

            'roles.view'      => ['label' => 'Ver roles',          'group' => 'Roles'],
            'roles.manage'    => ['label' => 'Gestionar roles',    'group' => 'Roles'],

            'stats.view'      => ['label' => 'Ver estadisticas',   'group' => 'Estadisticas'],
            'stats.revenue'   => ['label' => 'Ver ingresos/beneficios', 'group' => 'Estadisticas'],
            'stats.growth'    => ['label' => 'Ver grafico de crecimiento', 'group' => 'Estadisticas'],
            'logs.view'       => ['label' => 'Ver logs de actividad', 'group' => 'Estadisticas'],

            'wallet.manage'   => ['label' => 'Gestionar carteras', 'group' => 'Cartera'],
        ];

        foreach ($permissions as $name => $attrs) {
            Permission::updateOrCreate(
                ['name' => $name, 'guard_name' => 'web'],
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
                    'stats.view',
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
                    'stats.view',
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
                ['name' => $name, 'guard_name' => 'web'],
                ['label' => $attrs['label'], 'color' => $attrs['color']]
            );

            $role->syncPermissions($attrs['permissions']);
        }
    }
}
