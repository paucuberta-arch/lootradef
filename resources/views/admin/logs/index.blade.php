@extends('layouts.admin')
@section('admin-title', 'Activity Log')

@section('admin-content')
<div class="rounded-2xl bg-white/[0.03] border border-white/5 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-white/5">
                    <th class="text-left px-5 py-3 text-xs font-bold text-slate-500 uppercase">Fecha</th>
                    <th class="text-left px-5 py-3 text-xs font-bold text-slate-500 uppercase">Usuario</th>
                    <th class="text-left px-5 py-3 text-xs font-bold text-slate-500 uppercase">Accion</th>
                    <th class="text-left px-5 py-3 text-xs font-bold text-slate-500 uppercase">Modelo</th>
                    <th class="text-left px-5 py-3 text-xs font-bold text-slate-500 uppercase">IP</th>
                </tr>
            </thead>
            <tbody>
                @forelse($logs as $log)
                    <tr class="border-b border-white/[0.03] hover:bg-white/[0.02] transition">
                        <td class="px-5 py-3 text-xs text-slate-500">{{ $log->created_at->format('d/m/Y H:i') }}</td>
                        <td class="px-5 py-3 text-sm text-white font-medium">{{ $log->usuario?->name ?? 'Sistema' }}</td>
                        <td class="px-5 py-3">
                            <span class="px-2 py-1 rounded-lg text-xs font-semibold bg-brand-500/10 text-brand-400">{{ $log->accion }}</span>
                        </td>
                        <td class="px-5 py-3 text-xs text-slate-400">
                            @if($log->modelo)
                                {{ $log->modelo }} #{{ $log->modelo_id }}
                            @else
                                —
                            @endif
                        </td>
                        <td class="px-5 py-3 text-xs text-slate-600 font-mono">{{ $log->ip ?? '—' }}</td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="px-5 py-10 text-center text-slate-600">Sin actividad registrada.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="mt-6">{{ $logs->links() }}</div>
@endsection
