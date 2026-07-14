<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Permission;
use App\Models\Role;
use Illuminate\Http\Request;

class AdminRoleController extends Controller
{
    public function index()
    {
        $roles = Role::with('permissions')->get();
        $permissions = Permission::all()->groupBy('group');
        return view('admin.roles.index', compact('roles', 'permissions'));
    }

    public function store(Request $request)
    {
        $datos = $request->validate([
            'name' => 'required|string|unique:roles,name',
            'label' => 'required|string|max:50',
            'color' => 'required|string',
        ]);

        $role = Role::create($datos);

        if ($request->has('permissions')) {
            $role->permissions()->sync($request->permissions);
        }

        return redirect()->route('admin.roles')->with('success', "Rol {$role->label} creado.");
    }

    public function update(Request $request, Role $role)
    {
        $datos = $request->validate([
            'label' => 'required|string|max:50',
            'color' => 'required|string',
        ]);

        $role->update($datos);
        $role->permissions()->sync($request->permissions ?? []);

        return redirect()->route('admin.roles')->with('success', "Rol {$role->label} actualizado.");
    }

    public function destroy(Role $role)
    {
        if ($role->name === 'super_admin') {
            return back()->withErrors(['error' => 'No se puede eliminar el rol Super Admin.']);
        }

        $usuariosConRol = $role->usuarios()->count();
        if ($usuariosConRol > 0) {
            return back()->withErrors(['error' => "Hay {$usuariosConRol} usuarios con este rol. Reasignalos primero."]);
        }

        $role->delete();
        return redirect()->route('admin.roles')->with('success', 'Rol eliminado.');
    }
}
