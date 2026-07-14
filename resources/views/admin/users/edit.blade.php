@extends('layouts.admin')
@section('admin-title', 'Editar usuario: ' . $usuario->name)

@section('admin-content')
<div class="max-w-2xl">
    <form method="POST" action="{{ route('admin.users.update', $usuario) }}" class="space-y-6">
        @csrf @method('PUT')

        <div class="rounded-2xl bg-white/[0.03] border border-white/5 p-6 space-y-5">
            <h3 class="text-sm font-bold text-white uppercase tracking-wider">Datos del usuario</h3>

            <div>
                <label class="text-xs text-slate-500 mb-1 block">Nombre</label>
                <input type="text" name="name" value="{{ old('name', $usuario->name) }}"
                       class="w-full px-4 py-3 rounded-xl bg-white/5 border border-white/10 text-white text-sm outline-none focus:border-brand-500 transition">
            </div>

            <div>
                <label class="text-xs text-slate-500 mb-1 block">Email</label>
                <input type="email" name="email" value="{{ old('email', $usuario->email) }}"
                       class="w-full px-4 py-3 rounded-xl bg-white/5 border border-white/10 text-white text-sm outline-none focus:border-brand-500 transition">
            </div>

            <div>
                <label class="text-xs text-slate-500 mb-1 block">Rol</label>
                <select name="rol" class="w-full px-4 py-3 rounded-xl bg-white/5 border border-white/10 text-white text-sm outline-none focus:border-brand-500 transition">
                    @foreach($roles as $role)
                        <option value="{{ $role->name }}" {{ $usuario->roles->first()?->name === $role->name ? 'selected' : '' }}>{{ $role->label }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="text-xs text-slate-500 mb-1 block">Saldo (€)</label>
                <input type="number" name="saldo" value="{{ old('saldo', number_format($usuario->cartera->saldo ?? 0, 2, '.', '')) }}" step="0.01" min="0"
                       class="w-full px-4 py-3 rounded-xl bg-white/5 border border-white/10 text-white text-sm outline-none focus:border-brand-500 transition">
            </div>
        </div>

        <div class="flex items-center gap-3">
            <button class="px-6 py-3 rounded-xl bg-brand-500 hover:bg-brand-400 text-black font-bold transition">Guardar</button>
            <a href="{{ route('admin.users') }}" class="px-6 py-3 rounded-xl bg-white/5 border border-white/10 text-sm text-slate-400 hover:text-white transition">Cancelar</a>
        </div>
    </form>
</div>
@endsection
