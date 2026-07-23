<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class AdminRoleController extends Controller
{
    public function index()
    {
        $roles = Role::withCount('users')->get();
        $permissions = Permission::all()->groupBy('group');
        return view('admin.roles.index', compact('roles', 'permissions'));
    }

    public function store(Request $request)
    {
        $datos = $request->validate([
            'name' => ['required', 'string', 'max:50', 'regex:/^[a-z][a-z0-9_-]*$/', 'unique:roles,name'],
            'label' => 'required|string|max:50',
            'color' => 'required|string|max:20',
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['string', 'exists:permissions,name'],
        ]);

        $role = Role::create([
            'name' => $datos['name'],
            'label' => $datos['label'],
            'color' => $datos['color'],
            'guard_name' => 'web',
        ]);

        if ($request->has('permissions')) {
            $role->syncPermissions($request->permissions);
        }
        ActivityLog::log('rol_creado', Role::class, $role->id, ['name' => $role->name]);

        return redirect()->route('admin.roles')->with('success', "Rol {$role->label} creado.");
    }

    public function update(Request $request, Role $role)
    {
        $datos = $request->validate([
            'label' => 'required|string|max:50',
            'color' => 'required|string|max:20',
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['string', 'exists:permissions,name'],
        ]);

        $role->update(['label' => $datos['label'], 'color' => $datos['color']]);
        if ($role->name !== 'super_admin') {
            $role->syncPermissions($request->permissions ?? []);
        }
        ActivityLog::log('rol_actualizado', Role::class, $role->id, ['name' => $role->name]);

        return redirect()->route('admin.roles')->with('success', "Rol {$role->label} actualizado.");
    }

    public function destroy(Role $role)
    {
        if ($role->name === 'super_admin') {
            return back()->withErrors(['error' => 'No se puede eliminar el rol Super Admin.']);
        }

        $usuariosConRol = $role->users()->count();
        if ($usuariosConRol > 0) {
            return back()->withErrors(['error' => "Hay {$usuariosConRol} usuarios con este rol. Reasignalos primero."]);
        }

        $roleId = $role->id;
        $roleName = $role->name;
        $role->delete();
        ActivityLog::log('rol_eliminado', Role::class, $roleId, ['name' => $roleName]);
        return redirect()->route('admin.roles')->with('success', 'Rol eliminado.');
    }
}
