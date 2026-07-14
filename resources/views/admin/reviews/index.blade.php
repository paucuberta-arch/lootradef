@extends('layouts.admin')
@section('admin-title', 'Reviews')

@section('admin-content')
{{-- Counts --}}
<div class="flex flex-wrap gap-2 mb-6">
    <a href="{{ route('admin.reviews') }}" class="px-3 py-1.5 rounded-lg text-xs font-medium {{ !request('estado') ? 'bg-brand-500/10 text-brand-400 border border-brand-500/20' : 'text-slate-400 bg-white/5 border border-white/5' }}">Todos ({{ $counts['total'] }})</a>
    <a href="{{ route('admin.reviews', ['estado' => 'pendiente']) }}" class="px-3 py-1.5 rounded-lg text-xs font-medium {{ request('estado') === 'pendiente' ? 'bg-amber-500/10 text-amber-400 border border-amber-500/20' : 'text-slate-400 bg-white/5 border border-white/5' }}">Pendientes ({{ $counts['pendiente'] }})</a>
    <a href="{{ route('admin.reviews', ['estado' => 'aprobado']) }}" class="px-3 py-1.5 rounded-lg text-xs font-medium {{ request('estado') === 'aprobado' ? 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/20' : 'text-slate-400 bg-white/5 border border-white/5' }}">Aprobados ({{ $counts['aprobado'] }})</a>
    <a href="{{ route('admin.reviews', ['estado' => 'rechazado']) }}" class="px-3 py-1.5 rounded-lg text-xs font-medium {{ request('estado') === 'rechazado' ? 'bg-red-500/10 text-red-400 border border-red-500/20' : 'text-slate-400 bg-white/5 border border-white/5' }}">Rechazados ({{ $counts['rechazado'] }})</a>
</div>

<div class="rounded-2xl bg-white/[0.03] border border-white/5 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-white/5">
                    <th class="text-left px-5 py-3 text-xs font-bold text-slate-500 uppercase">Autor</th>
                    <th class="text-left px-5 py-3 text-xs font-bold text-slate-500 uppercase">Tipo</th>
                    <th class="text-left px-5 py-3 text-xs font-bold text-slate-500 uppercase">Contenido</th>
                    <th class="text-left px-5 py-3 text-xs font-bold text-slate-500 uppercase">Estado</th>
                    <th class="text-right px-5 py-3 text-xs font-bold text-slate-500 uppercase">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse($reviews as $r)
                    <tr class="border-b border-white/[0.03] hover:bg-white/[0.02] transition">
                        <td class="px-5 py-3">
                            <p class="font-semibold text-white">{{ $r->usuario->name ?? 'Eliminado' }}</p>
                            <p class="text-xs text-slate-500">{{ $r->created_at->diffForHumans() }}</p>
                        </td>
                        <td class="px-5 py-3">
                            <span class="px-2 py-1 rounded-lg text-xs font-semibold {{ $r->tipo === 'juego' ? 'bg-blue-500/10 text-blue-400' : 'bg-purple-500/10 text-purple-400' }}">
                                {{ ucfirst($r->tipo) }}{{ $r->juego_slug ? ': ' . $r->juego_slug : '' }}
                            </span>
                        </td>
                        <td class="px-5 py-3 max-w-xs">
                            @if($r->titulo)<p class="font-semibold text-white text-xs">{{ $r->titulo }}</p>@endif
                            <p class="text-xs text-slate-400 truncate">{{ Str::limit($r->contenido, 100) }}</p>
                            @if($r->puntuacion)
                                <div class="flex gap-0.5 mt-1">
                                    @for($i = 1; $i <= 5; $i++)
                                        <span class="text-xs {{ $i <= $r->puntuacion ? 'text-brand-400' : 'text-slate-700' }}">★</span>
                                    @endfor
                                </div>
                            @endif
                        </td>
                        <td class="px-5 py-3">
                            @php
                                $estadoColors = ['pendiente' => 'amber', 'aprobado' => 'emerald', 'rechazado' => 'red'];
                            @endphp
                            <span class="px-2 py-1 rounded-lg text-xs font-semibold bg-{{ $estadoColors[$r->estado] ?? 'slate' }}-500/10 text-{{ $estadoColors[$r->estado] ?? 'slate' }}-400">
                                {{ ucfirst($r->estado) }}
                            </span>
                        </td>
                        <td class="px-5 py-3 text-right space-x-2">
                            @if($r->estado !== 'aprobado')
                                <form method="POST" action="{{ route('admin.reviews.update', $r) }}" class="inline">
                                    @csrf @method('PUT')
                                    <input type="hidden" name="estado" value="aprobado">
                                    <button class="text-xs text-emerald-400 hover:text-emerald-300">Aprobar</button>
                                </form>
                            @endif
                            @if($r->estado !== 'rechazado')
                                <form method="POST" action="{{ route('admin.reviews.update', $r) }}" class="inline">
                                    @csrf @method('PUT')
                                    <input type="hidden" name="estado" value="rechazado">
                                    <button class="text-xs text-amber-400 hover:text-amber-300">Rechazar</button>
                                </form>
                            @endif
                            <form method="POST" action="{{ route('admin.reviews.destroy', $r) }}" class="inline" onsubmit="return confirm('Eliminar esta review?')">
                                @csrf @method('DELETE')
                                <button class="text-xs text-red-400 hover:text-red-300">Eliminar</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="px-5 py-10 text-center text-slate-600">Sin reviews.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="mt-6">{{ $reviews->withQueryString()->links() }}</div>
@endsection
