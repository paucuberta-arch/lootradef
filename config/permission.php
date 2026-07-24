<?php

return [
    'models' => [
        'role' => Spatie\Permission\Models\Role::class,
        'permission' => Spatie\Permission\Models\Permission::class,
    ],

    'table_names' => [
        'roles' => 'roles',
        'permissions' => 'permissions',
        'model_has_roles' => 'model_has_roles',
        'model_has_permissions' => 'model_has_permissions',
        'role_has_permissions' => 'role_has_permissions',
    ],

    'column_names' => [
        'model_morph_key' => 'model_id',
    ],

    'register_permission_check_method' => true,

    'register_octane_reset_listener' => false,

    'cache' => [
        'store' => env('PERMISSION_CACHE_STORE', 'default'),
        'key' => 'spatie.permission.cache',
        'prefix' => 'spatie.permission.cache.',
    ],
];
