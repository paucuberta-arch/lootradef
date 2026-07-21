@extends('layouts.admin')
@section('admin-title', 'Usuarios')

@section('admin-content')
<div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 mb-6">
    <div class="flex gap-2">
        <a href="{{ route('admin.users', array_filter(['search' => request('search')])) }}" class="px-3 py-1.5 rounded-lg text-xs font-medium {{ !request('role') ? 'bg-brand-500/10 text-brand-400 border border-brand-500/20' : 'text-slate-400 hover:text-white bg-white/5 border border-white/5' }}">Todos</a>
        @foreach($roles as $r)
            <a href="{{ route('admin.users', array_filter(['role' => $r->name, 'search' => request('search')])) }}" class="px-3 py-1.5 rounded-lg text-xs font-medium {{ request('role') === $r->name ? 'bg-brand-500/10 text-brand-400 border border-brand-500/20' : 'text-slate-400 hover:text-white bg-white/5 border border-white/5' }}">{{ $r->label }}</a>
        @endforeach
    </div>
    <form method="GET" class="flex gap-2">
        @if(request('role'))<input type="hidden" name="role" value="{{ request('role') }}">@endif
        <input type="text" name="search" value="{{ request('search') }}" placeholder="Buscar..."
               class="px-4 py-2 rounded-xl bg-white/5 border border-white/10 text-sm text-white placeholder-slate-500 outline-none focus:border-brand-500 transition w-48">
        <button class="px-4 py-2 rounded-xl bg-white/5 border border-white/10 text-sm text-slate-400 hover:text-white transition">Buscar</button>
    </form>
</div>

<div class="rounded-2xl bg-white/[0.03] border border-white/5 overflow-hidden">
    <div class="admin-table-wrap overflow-x-auto">
        <table class="admin-table w-full text-sm">
            <thead>
                <tr class="border-b border-white/5">
                    <th class="text-left px-5 py-3 text-xs font-bold text-slate-500 uppercase">Usuario</th>
                    <th class="text-left px-5 py-3 text-xs font-bold text-slate-500 uppercase">Rol</th>
                    <th class="text-left px-5 py-3 text-xs font-bold text-slate-500 uppercase">Saldo</th>
                    <th class="text-left px-5 py-3 text-xs font-bold text-slate-500 uppercase">Registro</th>
                    <th class="text-right px-5 py-3 text-xs font-bold text-slate-500 uppercase">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse($usuarios as $u)
                    <tr class="border-b border-white/[0.03] hover:bg-white/[0.02] transition">
                        <td data-primary class="px-5 py-3">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-full bg-gradient-to-br from-brand-400 to-brand-600 flex items-center justify-center text-black text-xs font-bold shrink-0">
                                    {{ strtoupper(substr($u->name, 0, 1)) }}
                                </div>
                                <div>
                                    <p class="font-semibold text-white">{{ $u->name }}</p>
                                    <p class="text-xs text-slate-500">{{ $u->email }}</p>
                                </div>
                            </div>
                        </td>
                        <td data-label="Rol" class="px-5 py-3">
                            @php $role = $u->roles->first(); @endphp
                            <span class="tone-badge tone-{{ $role->color ?? 'slate' }} px-2 py-1 rounded-lg text-xs font-semibold">
                                {{ $role->label ?? 'Sin rol' }}
                            </span>
                        </td>
                        <td data-label="Saldo" class="px-5 py-3 text-sm text-white font-semibold">
                            €{{ number_format($u->cartera->saldo ?? 0, 2) }}
                        </td>
                        <td data-label="Registro" class="px-5 py-3 text-xs text-slate-500">{{ $u->created_at->format('d/m/Y') }}</td>
                        <td data-label="Acciones" class="px-5 py-3 text-right">
                            @can('users.edit')
                            <a href="{{ route('admin.users.edit', $u) }}" class="text-xs text-brand-400 hover:text-brand-300 transition mr-3">Editar</a>
                            @endcan
                            @can('users.delete')
                            <form method="POST" action="{{ route('admin.users.destroy', $u) }}" class="inline" @submit.prevent="requestDelete($el, 'Se eliminará la cuenta de {{ addslashes($u->name) }} y sus datos asociados.')">
                                @csrf @method('DELETE')
                                <button class="text-xs text-red-400 hover:text-red-300 transition">Eliminar</button>
                            </form>
                            @endcan
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="px-5 py-10 text-center text-slate-600">Sin resultados.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="mt-6">{{ $usuarios->withQueryString()->links() }}</div>
@endsection
