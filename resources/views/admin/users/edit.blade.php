@extends('layouts.admin')
@section('admin-title', 'Editar usuario: ' . $usuario->name)

@section('admin-content')
<div class="max-w-2xl">
    <form method="POST" action="{{ route('admin.users.update', $usuario) }}" class="space-y-6">
        @csrf @method('PUT')

        <div class="rounded-2xl bg-white/[0.03] border border-white/5 p-4 sm:p-6 space-y-5">
            <div class="flex items-center gap-3 border-b border-white/5 pb-4">
                <x-ui.user-avatar :user="$usuario" size="md" />
                <div class="min-w-0">
                    <h3 class="truncate text-sm font-bold uppercase tracking-wider text-white">Datos del usuario</h3>
                    <p class="mt-1 truncate text-xs text-slate-500">{{ $usuario->name }}</p>
                </div>
            </div>

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
                @can('roles.manage')
                <select name="rol" class="w-full px-4 py-3 rounded-xl bg-white/5 border border-white/10 text-white text-sm outline-none focus:border-brand-500 transition">
                    @foreach($roles as $role)
                        <option value="{{ $role->name }}" {{ $usuario->roles->first()?->name === $role->name ? 'selected' : '' }}>{{ $role->label }}</option>
                    @endforeach
                </select>
                @else
                    <p class="rounded-xl border border-white/10 bg-white/5 px-4 py-3 text-sm text-slate-300">{{ $usuario->role_badge }}</p>
                @endcan
            </div>

            @can('wallet.manage')
            <div>
                <label class="text-xs text-slate-500 mb-1 block">Saldo (EUR Demo)</label>
                <input type="number" name="saldo" value="{{ old('saldo', number_format($usuario->cartera->saldo ?? 0, 2, '.', '')) }}" step="0.01" min="0"
                       class="w-full px-4 py-3 rounded-xl bg-white/5 border border-white/10 text-white text-sm outline-none focus:border-brand-500 transition">
            </div>
            @endcan
        </div>

        <div class="flex flex-col min-[420px]:flex-row min-[420px]:items-center gap-3">
            <button class="w-full min-[420px]:w-auto px-6 py-3 rounded-xl bg-brand-500 hover:bg-brand-400 text-black font-bold transition">Guardar</button>
            <a href="{{ route('admin.users') }}" class="w-full min-[420px]:w-auto px-6 py-3 rounded-xl bg-white/5 border border-white/10 text-center text-sm text-slate-400 hover:text-white transition">Cancelar</a>
        </div>
    </form>
</div>
@endsection
