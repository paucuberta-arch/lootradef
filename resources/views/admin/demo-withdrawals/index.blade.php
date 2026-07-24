@extends('layouts.admin')

@section('admin-title', 'Retiradas demo')

@section('admin-content')
<div class="mx-auto max-w-[1400px] space-y-6">
    <header>
        <p class="text-xs font-black uppercase tracking-[.2em] text-amber-300">Operativa ficticia</p>
        <h2 class="mt-2 text-2xl font-black text-white">Retiradas EUR Demo</h2>
        <p class="mt-2 text-sm text-slate-400">Este panel solo mueve créditos de demostración. No conecta con bancos ni procesa dinero real.</p>
    </header>

    <div class="grid gap-3 sm:grid-cols-3">
        <div class="rounded-2xl border border-white/10 bg-white/[.03] p-4"><p class="text-xs text-slate-500">Disponible</p><p class="mt-2 text-xl font-black text-emerald-300">{{ number_format($treasury->available_balance, 2, ',', '.') }} EUR Demo</p></div>
        <div class="rounded-2xl border border-white/10 bg-white/[.03] p-4"><p class="text-xs text-slate-500">Reservado</p><p class="mt-2 text-xl font-black text-amber-300">{{ number_format($treasury->reserved_balance, 2, ',', '.') }} EUR Demo</p></div>
        <div class="rounded-2xl border border-white/10 bg-white/[.03] p-4"><p class="text-xs text-slate-500">Solicitudes</p><p class="mt-2 text-xl font-black text-cyan-300">{{ $withdrawals->count() }}</p></div>
    </div>

    <section class="overflow-hidden rounded-2xl border border-white/10 bg-white/[.025]">
        <div class="admin-table-wrap overflow-x-auto">
            <table class="admin-table w-full min-w-[900px] text-left text-sm">
                <thead class="bg-black/20 text-[10px] uppercase tracking-wider text-slate-500"><tr><th class="px-5 py-3">Fecha</th><th class="px-4 py-3">Usuario</th><th class="px-4 py-3">Importe</th><th class="px-4 py-3">Método demo</th><th class="px-4 py-3">Estado</th><th class="px-4 py-3 text-right">Acciones</th></tr></thead>
                <tbody class="divide-y divide-white/5">
                @forelse($withdrawals as $withdrawal)
                    <tr><td class="px-5 py-3 text-xs text-slate-400">{{ $withdrawal->created_at?->format('d/m/Y H:i') }}</td><td class="px-4 py-3"><p class="font-semibold text-white">{{ $withdrawal->usuario?->name }}</p><p class="text-xs text-slate-500">{{ $withdrawal->usuario?->email }}</p></td><td class="px-4 py-3 font-bold text-amber-200">{{ number_format($withdrawal->amount, 2, ',', '.') }} EUR Demo</td><td class="px-4 py-3 text-slate-300">{{ str($withdrawal->method_key)->replace('_', ' ')->title() }}</td><td class="px-4 py-3"><span class="rounded-lg border border-white/10 bg-white/5 px-2 py-1 text-xs text-slate-300">{{ str($withdrawal->status)->replace('_', ' ')->title() }}</span></td><td class="px-4 py-3 text-right"><div class="flex justify-end gap-2"><form method="POST" action="{{ route('admin.demo-withdrawals.review', $withdrawal) }}">@csrf<input type="hidden" name="action" value="approve"><button class="rounded-lg bg-emerald-400/15 px-2 py-1 text-xs font-bold text-emerald-300">Aprobar</button></form><form method="POST" action="{{ route('admin.demo-withdrawals.review', $withdrawal) }}">@csrf<input type="hidden" name="action" value="complete"><button class="rounded-lg bg-cyan-400/15 px-2 py-1 text-xs font-bold text-cyan-300">Completar</button></form></div></td></tr>
                @empty
                    <tr><td colspan="6" class="px-5 py-12 text-center text-sm text-slate-500">No hay retiradas demo.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </section>
</div>
@endsection
