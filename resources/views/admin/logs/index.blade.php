@extends('layouts.admin')
@section('admin-title', 'Activity Log')

@section('admin-content')
<div class="rounded-2xl bg-white/[0.03] border border-white/5 overflow-hidden">
    <div class="admin-table-wrap overflow-x-auto">
        <table class="admin-table w-full text-sm">
            <thead>
                <tr class="border-b border-white/5">
                    <th class="text-left px-5 py-3 text-xs font-bold text-slate-500 uppercase">Fecha</th>
                    <th class="text-left px-5 py-3 text-xs font-bold text-slate-500 uppercase">Usuario</th>
                    <th class="text-left px-5 py-3 text-xs font-bold text-slate-500 uppercase">Accion</th>
                    <th class="text-left px-5 py-3 text-xs font-bold text-slate-500 uppercase">Modelo</th>
                    <th class="text-left px-5 py-3 text-xs font-bold text-slate-500 uppercase">Detalles</th>
                    <th class="text-left px-5 py-3 text-xs font-bold text-slate-500 uppercase">IP</th>
                </tr>
            </thead>
            <tbody>
                @forelse($logs as $log)
                    <tr class="border-b border-white/[0.03] hover:bg-white/[0.02] transition">
                        <td data-label="Fecha" class="px-5 py-3 text-xs text-slate-500">{{ $log->created_at->format('d/m/Y H:i') }}</td>
                        <td data-label="Usuario" class="px-5 py-3 text-sm text-white font-medium">{{ $log->usuario?->name ?? 'Sistema' }}</td>
                        <td data-label="Acción" class="px-5 py-3">
                            <span class="px-2 py-1 rounded-lg text-xs font-semibold bg-brand-500/10 text-brand-400">{{ $log->accion }}</span>
                        </td>
                        <td data-label="Modelo" class="px-5 py-3 text-xs text-slate-400">
                            @if($log->modelo)
                                {{ $log->modelo }} #{{ $log->modelo_id }}
                            @else
                                —
                            @endif
                        </td>
                        <td data-label="Detalles" class="max-w-xs px-5 py-3 text-xs text-slate-500">
                            {{ $log->detalles ? \Illuminate\Support\Str::limit(collect($log->detalles)->map(fn ($value, $key) => $key.': '.(is_scalar($value) ? $value : json_encode($value)))->implode(' · '), 180) : '—' }}
                        </td>
                        <td data-label="IP" class="px-5 py-3 text-xs text-slate-600 font-mono">{{ $log->ip ?? '—' }}</td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="px-5 py-10 text-center text-slate-600">Sin actividad registrada.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="mt-6">{{ $logs->links() }}</div>
@endsection
