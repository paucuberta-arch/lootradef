@extends('layouts.admin')
@section('admin-title', 'Historial de cajas')

@section('admin-content')
@php
    $rarityStyles = [
        'comun' => 'border-slate-400/20 bg-slate-400/10 text-slate-300',
        'poco_comun' => 'border-emerald-400/20 bg-emerald-400/10 text-emerald-300',
        'raro' => 'border-cyan-400/20 bg-cyan-400/10 text-cyan-300',
        'epico' => 'border-fuchsia-400/20 bg-fuchsia-400/10 text-fuchsia-300',
        'legendario' => 'border-amber-300/25 bg-amber-300/10 text-amber-200',
    ];
    $revenue = (float) $summary->revenue;
    $redeemed = (float) $summary->redeemed_value;
    $pending = (float) $summary->pending_value;
    $margin = $revenue - $redeemed;
@endphp

<div class="mx-auto max-w-[1500px] space-y-6">
    <header class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
        <div>
            <p class="text-xs font-black uppercase tracking-[.2em] text-cyan-300">Trazabilidad completa</p>
            <h2 class="mt-2 text-2xl font-black sm:text-3xl">Aperturas y premios entregados</h2>
            <p class="mt-2 max-w-3xl text-sm leading-relaxed text-slate-400">Consulta cada apertura real registrada, su impacto económico y si el premio continúa en el inventario o ya fue canjeado.</p>
        </div>
        <a href="{{ route('admin.case-prizes.index') }}" class="inline-flex min-h-11 items-center justify-center rounded-xl border border-fuchsia-400/20 bg-fuchsia-400/10 px-4 text-sm font-bold text-fuchsia-200 transition hover:bg-fuchsia-400/15">Gestionar probabilidades</a>
    </header>

    <div class="rounded-2xl border border-violet-400/20 bg-violet-400/[.06] p-4 text-sm text-violet-100 sm:flex sm:items-center sm:justify-between sm:gap-4">
        <div><b class="block text-violet-200">Generador QA disponible</b><p class="mt-1 text-xs leading-relaxed text-slate-400">Crea 200 aperturas demo por caja, distribuidas en 30 días y respetando las probabilidades y cupos actuales.</p></div>
        <code class="mt-3 block overflow-x-auto rounded-lg bg-black/30 px-3 py-2 text-xs text-violet-200 sm:mt-0">php artisan cases:fake-history --fresh</code>
    </div>

    <section class="grid gap-3 sm:grid-cols-2 xl:grid-cols-5">
        <article class="rounded-2xl border border-white/10 bg-white/[.03] p-4"><p class="text-[10px] font-black uppercase tracking-wider text-slate-500">Aperturas</p><p class="mt-2 text-2xl font-black">{{ number_format((int) $summary->openings, 0, ',', '.') }}</p><p class="mt-1 text-xs text-slate-500">Según los filtros activos</p></article>
        <article class="rounded-2xl border border-cyan-400/15 bg-cyan-400/[.05] p-4"><p class="text-[10px] font-black uppercase tracking-wider text-cyan-300">Ingresos</p><p class="mt-2 text-2xl font-black text-cyan-100">{{ number_format($revenue, 2, ',', '.') }} €</p><p class="mt-1 text-xs text-slate-500">Precio total de cajas</p></article>
        <article class="rounded-2xl border border-emerald-400/15 bg-emerald-400/[.05] p-4"><p class="text-[10px] font-black uppercase tracking-wider text-emerald-300">Canjeado</p><p class="mt-2 text-2xl font-black text-emerald-100">{{ number_format($redeemed, 2, ',', '.') }} €</p><p class="mt-1 text-xs text-slate-500">Valor ya abonado</p></article>
        <article class="rounded-2xl border border-amber-300/15 bg-amber-300/[.05] p-4"><p class="text-[10px] font-black uppercase tracking-wider text-amber-200">Pendiente</p><p class="mt-2 text-2xl font-black text-amber-100">{{ number_format($pending, 2, ',', '.') }} €</p><p class="mt-1 text-xs text-slate-500">Exposición en inventario</p></article>
        <article class="rounded-2xl border p-4 {{ $margin >= 0 ? 'border-violet-400/15 bg-violet-400/[.05]' : 'border-red-400/20 bg-red-400/[.06]' }}"><p class="text-[10px] font-black uppercase tracking-wider {{ $margin >= 0 ? 'text-violet-300' : 'text-red-300' }}">Margen realizado</p><p class="mt-2 text-2xl font-black {{ $margin >= 0 ? 'text-violet-100' : 'text-red-200' }}">{{ number_format($margin, 2, ',', '.') }} €</p><p class="mt-1 text-xs text-slate-500">Ingresos menos canjes</p></article>
    </section>

    <form method="GET" action="{{ route('admin.case-history.index') }}" class="rounded-2xl border border-white/10 bg-white/[.025] p-4 sm:p-5">
        <div class="grid gap-3 md:grid-cols-2 xl:grid-cols-[1.4fr_repeat(4,.9fr)_repeat(2,.8fr)_auto]">
            <label class="text-xs font-bold text-slate-400">Buscar
                <input name="search" value="{{ $filters['search'] ?? '' }}" maxlength="100" placeholder="Usuario, email, premio o ID" class="mt-1 min-h-11 w-full rounded-xl border border-white/10 bg-black/25 px-3 text-white outline-none focus:border-cyan-300">
            </label>
            <label class="text-xs font-bold text-slate-400">Caja
                <select name="case" class="mt-1 min-h-11 w-full rounded-xl border border-white/10 bg-[#111827] px-3 text-white"><option value="">Todas</option>@foreach($cases as $key => $name)<option value="{{ $key }}" @selected(($filters['case'] ?? '') === $key)>{{ $name }}</option>@endforeach</select>
            </label>
            <label class="text-xs font-bold text-slate-400">Rareza
                <select name="rarity" class="mt-1 min-h-11 w-full rounded-xl border border-white/10 bg-[#111827] px-3 text-white"><option value="">Todas</option>@foreach(['comun'=>'Común','poco_comun'=>'Poco común','raro'=>'Raro','epico'=>'Épico','legendario'=>'Legendario'] as $key => $label)<option value="{{ $key }}" @selected(($filters['rarity'] ?? '') === $key)>{{ $label }}</option>@endforeach</select>
            </label>
            <label class="text-xs font-bold text-slate-400">Estado
                <select name="status" class="mt-1 min-h-11 w-full rounded-xl border border-white/10 bg-[#111827] px-3 text-white"><option value="">Todos</option><option value="disponible" @selected(($filters['status'] ?? '') === 'disponible')>Disponible</option><option value="canjeado" @selected(($filters['status'] ?? '') === 'canjeado')>Canjeado</option></select>
            </label>
            <label class="text-xs font-bold text-slate-400">Origen
                <select name="source" class="mt-1 min-h-11 w-full rounded-xl border border-white/10 bg-[#111827] px-3 text-white"><option value="">Todos</option><option value="real" @selected(($filters['source'] ?? '') === 'real')>Real</option><option value="simulated" @selected(($filters['source'] ?? '') === 'simulated')>Simulado</option></select>
            </label>
            <label class="text-xs font-bold text-slate-400">Desde<input name="from" value="{{ $filters['from'] ?? '' }}" type="date" class="mt-1 min-h-11 w-full rounded-xl border border-white/10 bg-black/25 px-3 text-white"></label>
            <label class="text-xs font-bold text-slate-400">Hasta<input name="to" value="{{ $filters['to'] ?? '' }}" type="date" class="mt-1 min-h-11 w-full rounded-xl border border-white/10 bg-black/25 px-3 text-white"></label>
            <div class="flex items-end gap-2"><button class="min-h-11 flex-1 rounded-xl bg-cyan-300 px-4 font-black text-slate-950">Filtrar</button><a href="{{ route('admin.case-history.index') }}" class="grid min-h-11 min-w-11 place-items-center rounded-xl border border-white/10 text-slate-400" title="Limpiar filtros" aria-label="Limpiar filtros">×</a></div>
        </div>
    </form>

    <section class="overflow-hidden rounded-2xl border border-white/10 bg-white/[.025]">
        <div class="flex flex-wrap items-center justify-between gap-3 border-b border-white/10 px-5 py-4">
            <div><h3 class="font-black">Registro de aperturas</h3><p class="mt-1 text-xs text-slate-500">{{ number_format($items->total(), 0, ',', '.') }} resultados encontrados · {{ number_format((int) $summary->good_prizes, 0, ',', '.') }} épicos o legendarios</p></div>
            <p class="rounded-lg bg-white/5 px-3 py-2 text-xs text-slate-400">Valor nominal entregado: <b class="text-white">{{ number_format((float) $summary->awarded_value, 2, ',', '.') }} €</b></p>
        </div>
        <div class="admin-table-wrap overflow-x-auto">
            <table class="admin-table w-full min-w-[1080px] text-left text-sm">
                <thead class="bg-black/20 text-[10px] uppercase tracking-wider text-slate-500"><tr><th class="px-5 py-3">ID / Fecha</th><th class="px-4 py-3">Usuario</th><th class="px-4 py-3">Caja</th><th class="px-4 py-3">Premio</th><th class="px-4 py-3">Rareza</th><th class="px-4 py-3 text-right">Coste</th><th class="px-4 py-3 text-right">Valor</th><th class="px-4 py-3 text-right">Diferencia</th><th class="px-4 py-3">Estado</th></tr></thead>
                <tbody class="divide-y divide-white/5">
                    @forelse($items as $item)
                        @php($difference = (float) $item->precio_caja - (float) $item->valor_canje)
                        <tr class="transition hover:bg-white/[.025]">
                            <td data-label="ID / Fecha" class="px-5 py-3"><b class="text-xs text-slate-300">#{{ $item->id }}</b><time class="mt-1 block text-xs text-slate-500" datetime="{{ $item->created_at->toIso8601String() }}">{{ $item->created_at->format('d/m/Y H:i') }}</time></td>
                            <td data-label="Usuario" class="px-4 py-3"><div class="flex items-center gap-3"><x-ui.user-avatar :user="$item->usuario" size="sm" /><div class="min-w-0"><p class="font-semibold text-white">{{ $item->usuario?->name ?? 'Usuario eliminado' }}</p><p class="mt-1 text-xs text-slate-500">{{ $item->usuario?->email ?? '—' }}</p>@if($item->usuario?->is_demo || in_array($item->usuario?->data_origin, ['test','simulated'], true))<span class="mt-1 inline-block rounded bg-violet-400/10 px-1.5 py-0.5 text-[9px] font-black uppercase text-violet-300">Demo</span>@endif</div></div></td>
                            <td data-label="Caja" class="px-4 py-3"><span class="font-semibold text-slate-200">{{ $cases[$item->caja] ?? ucfirst($item->caja) }}</span><span class="mt-1 block font-mono text-[10px] text-slate-600">{{ $item->caja }}</span></td>
                            <td data-label="Premio" class="px-4 py-3 font-semibold text-white">{{ $item->nombre }}</td>
                            <td data-label="Rareza" class="px-4 py-3"><span class="inline-flex rounded-lg border px-2 py-1 text-[10px] font-black uppercase tracking-wide {{ $rarityStyles[$item->rareza] ?? $rarityStyles['comun'] }}">{{ str_replace('_', ' ', $item->rareza) }}</span></td>
                            <td data-label="Coste" class="px-4 py-3 text-right tabular-nums text-slate-300">{{ number_format($item->precio_caja, 2, ',', '.') }} €</td>
                            <td data-label="Valor" class="px-4 py-3 text-right font-bold tabular-nums text-white">{{ number_format($item->valor_canje, 2, ',', '.') }} €</td>
                            <td data-label="Diferencia" class="px-4 py-3 text-right font-black tabular-nums {{ $difference >= 0 ? 'text-emerald-300' : 'text-red-300' }}">{{ $difference >= 0 ? '+' : '' }}{{ number_format($difference, 2, ',', '.') }} €</td>
                            <td data-label="Estado" class="px-4 py-3">@if($item->estado === 'canjeado')<span class="inline-flex rounded-lg border border-emerald-400/20 bg-emerald-400/10 px-2 py-1 text-[10px] font-black uppercase text-emerald-300">Canjeado</span><time class="mt-1 block text-[10px] text-slate-600">{{ $item->canjeado_at?->format('d/m/Y H:i') }}</time>@else<span class="inline-flex rounded-lg border border-amber-300/20 bg-amber-300/10 px-2 py-1 text-[10px] font-black uppercase text-amber-200">Disponible</span>@endif</td>
                        </tr>
                    @empty
                        <tr><td colspan="9" class="px-5 py-14 text-center"><p class="font-bold text-slate-400">No hay aperturas con estos filtros.</p><p class="mt-1 text-xs text-slate-600">Prueba a ampliar el intervalo o limpiar la búsqueda.</p></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>

    <div>{{ $items->links() }}</div>
</div>
@endsection
