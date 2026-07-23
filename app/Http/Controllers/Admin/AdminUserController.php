<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Usuario;
use App\Rules\SafeEmail;
use App\Services\WalletService;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;

class AdminUserController extends Controller
{
    public function __construct(private readonly WalletService $wallets) {}

    public function index(Request $request)
    {
        $query = Usuario::with('roles', 'cartera');

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($role = $request->input('role')) {
            $query->whereHas('roles', fn ($roles) => $roles
                ->where('name', $role)
                ->where('guard_name', 'web'));
        }

        $usuarios = $query->latest()->paginate(20);
        $roles = Role::all();

        return view('admin.users.index', compact('usuarios', 'roles'));
    }

    public function edit(Request $request, Usuario $usuario)
    {
        $this->assertCanManage($request, $usuario);

        $usuario->load('roles', 'cartera');
        $roles = Role::all();

        return view('admin.users.edit', compact('usuario', 'roles'));
    }

    public function update(Request $request, Usuario $usuario)
    {
        $this->assertCanManage($request, $usuario);

        $datos = $request->validate([
            'name' => 'required|string|max:100',
            'email' => ['required', new SafeEmail, 'email:rfc', 'max:255', 'unique:usuarios,email,'.$usuario->id],
            'rol' => 'nullable|exists:roles,name',
            'saldo' => 'nullable|numeric|min:0',
        ]);

        $roleChanged = isset($datos['rol']) && ! $usuario->hasRole($datos['rol']);
        if ($roleChanged) {
            abort_unless($request->user()->can('roles.manage'), 403, 'No tienes permiso para cambiar roles.');
            if ($datos['rol'] === 'super_admin' || $usuario->hasRole('super_admin')) {
                abort_unless($request->user()->hasRole('super_admin'), 403, 'Solo un superadministrador puede modificar este rol.');
            }
            if ($usuario->hasRole('super_admin') && $datos['rol'] !== 'super_admin') {
                abort_if(
                    Usuario::role('super_admin')->count() <= 1,
                    409,
                    'No se puede degradar la unica cuenta de superadministrador.'
                );
            }
        }
        if ($request->filled('saldo')) {
            abort_unless($request->user()->can('wallet.manage'), 403, 'No tienes permiso para modificar saldos.');
        }

        $emailChanged = $usuario->email !== $datos['email'];
        $usuario->update([
            'name' => $datos['name'],
            'email' => $datos['email'],
            'email_verified_at' => $emailChanged ? null : $usuario->email_verified_at,
        ]);
        if ($emailChanged) {
            $usuario->sendEmailVerificationNotification();
        }

        if ($roleChanged) {
            $usuario->syncRoles($datos['rol']);
        }

        if ($request->filled('saldo')) {
            $wallet = $usuario->cartera()->firstOrCreate(
                ['usuario_id' => $usuario->id],
                ['saldo' => 0]
            );
            $this->wallets->setBalance($wallet, (float) $datos['saldo'], 'ajuste_administrador', [
                'administrador_id' => $request->user()->id,
            ]);
        }

        ActivityLog::log('usuario_editado', 'Usuario', $usuario->id, [
            'name' => $usuario->name,
            'role_changed' => $roleChanged,
            'balance_changed' => $request->filled('saldo'),
        ]);

        return redirect()->route('admin.users')->with('success', "Usuario {$usuario->name} actualizado.");
    }

    public function destroy(Request $request, Usuario $usuario)
    {
        $this->assertCanManage($request, $usuario);

        if ($usuario->id === $request->user()->id) {
            return back()->withErrors(['error' => 'No puedes eliminarte a ti mismo.']);
        }

        $usuario->delete();

        ActivityLog::log('usuario_desactivado', 'Usuario', $usuario->id, [
            'name' => $usuario->name,
            'actor_id' => $request->user()->id,
        ]);

        return redirect()->route('admin.users')->with('success', "Usuario {$usuario->name} desactivado.");
    }

    private function assertCanManage(Request $request, Usuario $usuario): void
    {
        $actor = $request->user();

        if ($actor->id === $usuario->id || $actor->hasRole('super_admin')) {
            return;
        }

        abort_if(
            $usuario->hasAnyRole(['super_admin', 'admin']),
            403,
            'No tienes permiso para administrar una cuenta con el mismo nivel o uno superior.'
        );
    }
}
