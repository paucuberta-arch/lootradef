@extends('layouts.admin')
@section('admin-title', 'Roles y Permisos')

@section('admin-content')
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

    {{-- Roles --}}
    <div class="lg:col-span-2 space-y-4">
        @foreach($roles as $role)
            <div class="rounded-2xl bg-white/[0.03] border border-white/5 p-5">
                <div class="flex items-center justify-between mb-3">
                    <div class="flex items-center gap-3">
                        <span class="tone-badge tone-{{ $role->color }} px-3 py-1 rounded-lg text-xs font-bold">{{ $role->label }}</span>
                        <span class="text-xs text-slate-500">{{ $role->users_count }} usuarios</span>
                    </div>
                    @if($role->name !== 'super_admin')
                        <form method="POST" action="{{ route('admin.roles.destroy', $role) }}" @submit.prevent="requestDelete($el, 'Se eliminará el rol {{ addslashes($role->label) }}.')">
                            @csrf @method('DELETE')
                            <button class="text-xs text-red-400 hover:text-red-300">Eliminar</button>
                        </form>
                    @endif
                </div>

                <form method="POST" action="{{ route('admin.roles.update', $role) }}" class="space-y-3">
                    @csrf @method('PUT')
                    <div class="flex gap-3">
                        <input type="text" name="label" value="{{ $role->label }}" placeholder="Label"
                               class="flex-1 px-3 py-2 rounded-xl bg-white/5 border border-white/10 text-sm text-white outline-none focus:border-brand-500">
                        <select name="color" class="px-3 py-2 rounded-xl bg-white/5 border border-white/10 text-sm text-white outline-none focus:border-brand-500">
                            @foreach(['red','purple','blue','amber','emerald','slate','pink','cyan'] as $c)
                                <option value="{{ $c }}" {{ $role->color === $c ? 'selected' : '' }} class="bg-[#14142a]">{{ ucfirst($c) }}</option>
                            @endforeach
                        </select>
                        <button class="px-4 py-2 rounded-xl bg-brand-500 hover:bg-brand-400 text-black text-sm font-bold transition">Guardar</button>
                    </div>

                    <div class="space-y-2">
                        @foreach($permissions as $group => $perms)
                            <div>
                                <p class="text-xs text-slate-600 font-semibold mb-1.5">{{ $group }}</p>
                                <div class="flex flex-wrap gap-1.5">
                                    @foreach($perms as $perm)
                                        <label class="flex items-center gap-1.5 px-2 py-1 rounded-lg bg-white/[0.02] border border-white/5 cursor-pointer hover:bg-white/5 transition">
                                            <input type="checkbox" name="permissions[]" value="{{ $perm->name }}"
                                                   {{ $role->hasPermissionTo($perm->name) ? 'checked' : '' }}
                                                   class="rounded border-white/20 bg-white/5 text-brand-500 focus:ring-brand-500">
                                            <span class="text-[11px] text-slate-400">{{ $perm->label }}</span>
                                        </label>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    </div>
                </form>
            </div>
        @endforeach
    </div>

    {{-- Nuevo rol --}}
    <div>
        <div class="rounded-2xl bg-white/[0.03] border border-white/5 p-5 sticky top-20">
            <h3 class="text-sm font-bold text-white uppercase tracking-wider mb-4">Crear nuevo rol</h3>
            <form method="POST" action="{{ route('admin.roles.store') }}" class="space-y-4">
                @csrf
                <div>
                    <label class="text-xs text-slate-500 mb-1 block">Nombre (ID)</label>
                    <input type="text" name="name" required placeholder="ej: editor"
                           class="w-full px-4 py-2.5 rounded-xl bg-white/5 border border-white/10 text-sm text-white placeholder-slate-600 outline-none focus:border-brand-500 transition">
                </div>
                <div>
                    <label class="text-xs text-slate-500 mb-1 block">Label (visible)</label>
                    <input type="text" name="label" required placeholder="ej: Editor"
                           class="w-full px-4 py-2.5 rounded-xl bg-white/5 border border-white/10 text-sm text-white placeholder-slate-600 outline-none focus:border-brand-500 transition">
                </div>
                <div>
                    <label class="text-xs text-slate-500 mb-1 block">Color</label>
                    <select name="color" class="w-full px-4 py-2.5 rounded-xl bg-white/5 border border-white/10 text-sm text-white outline-none focus:border-brand-500 transition">
                        @foreach(['slate','blue','purple','amber','emerald','red','pink','cyan'] as $c)
                            <option value="{{ $c }}" class="bg-[#14142a]">{{ ucfirst($c) }}</option>
                        @endforeach
                    </select>
                </div>
                <button class="w-full py-2.5 rounded-xl bg-brand-500 hover:bg-brand-400 text-black text-sm font-bold transition">Crear rol</button>
            </form>
        </div>
    </div>
</div>
@endsection
