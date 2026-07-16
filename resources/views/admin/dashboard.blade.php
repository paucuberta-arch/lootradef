@extends('layouts.admin')
@section('admin-title', 'Dashboard')

@section('admin-content')
@php
    $user = auth()->user();
@endphp

<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-7">
    <div>
        <p class="text-xs font-bold uppercase tracking-[.2em] text-brand-400">Centro de control</p>
        <h2 class="text-2xl font-extrabold text-white mt-1">Visión general del negocio</h2>
        <p class="text-sm text-slate-500 mt-1">Indicadores actualizados directamente desde la actividad de la plataforma.</p>
    </div>
    @if($user->hasAnyRole(['super_admin', 'admin']))
        <div class="inline-flex p-1 rounded-xl bg-white/[0.04] border border-white/10">
            @foreach([7, 30, 90] as $period)
                <a href="{{ route('admin.dashboard', ['period' => $period]) }}"
                   class="px-4 py-2 rounded-lg text-xs font-bold transition {{ $days === $period ? 'bg-brand-500 text-black shadow-lg shadow-brand-500/20' : 'text-slate-400 hover:text-white' }}">
                    {{ $period }} días
                </a>
            @endforeach
        </div>
    @endif
</div>

{{-- KPI Cards — visibles segun permisos --}}
@if($user->hasAnyRole(['super_admin', 'admin']))
<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
    @php
        $cards = [
            ['label' => 'Usuarios totales', 'value' => number_format($stats['usuarios']), 'icon' => 'M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z', 'color' => 'blue'],
            ['label' => 'Jugadores hoy', 'value' => $stats['jugadores_hoy'], 'icon' => 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z', 'color' => 'emerald'],
            ['label' => 'Partidas hoy', 'value' => number_format($stats['partidas_hoy']), 'icon' => 'M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z', 'color' => 'brand'],
            ['label' => 'Nuevos (7d)', 'value' => $stats['nuevos_usuarios_semana'], 'icon' => 'M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z', 'color' => 'cyan'],
        ];
    @endphp
    @foreach($cards as $card)
        <div class="rounded-2xl bg-white/[0.03] border border-white/5 p-5">
            <div class="flex items-center gap-3 mb-3">
                <div class="tone-icon tone-{{ $card['color'] }} w-10 h-10 rounded-xl flex items-center justify-center">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="{{ $card['icon'] }}"/>
                    </svg>
                </div>
            </div>
            <p class="text-2xl font-extrabold text-white">{{ $card['value'] }}</p>
            <p class="text-xs text-slate-500 mt-1">{{ $card['label'] }}</p>
        </div>
    @endforeach
</div>
@endif

{{-- KPI Financieros — solo super_admin --}}
@if($user->hasPermissionTo('stats.revenue'))
<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
    @php
        $financeCards = [
            ['label' => "Volumen apostado ({$days}d)", 'value' => '€' . number_format($stats['apostado_periodo'], 2), 'trend' => $stats['tendencia_apuestas'], 'icon' => 'M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z', 'color' => 'purple'],
            ['label' => "Beneficio bruto ({$days}d)", 'value' => '€' . number_format($stats['beneficio_periodo'], 2), 'trend' => null, 'icon' => 'M13 7h8m0 0v8m0-8l-8 8-4-4-6 6', 'color' => $stats['beneficio_periodo'] >= 0 ? 'emerald' : 'red'],
            ['label' => 'Margen de la casa', 'value' => number_format($stats['margen_periodo'], 1) . '%', 'trend' => null, 'icon' => 'M3 3v18h18M7 16l4-5 4 3 5-7', 'color' => 'cyan'],
            ['label' => 'Payout real', 'value' => number_format($stats['payout_periodo'], 1) . '%', 'trend' => null, 'icon' => 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z', 'color' => 'brand'],
        ];
    @endphp
    @foreach($financeCards as $card)
        <div class="rounded-2xl bg-white/[0.03] border border-white/5 p-5">
            <div class="flex items-center gap-3 mb-3">
                <div class="tone-icon tone-{{ $card['color'] }} w-10 h-10 rounded-xl flex items-center justify-center">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="{{ $card['icon'] }}"/>
                    </svg>
                </div>
            </div>
            <p class="text-2xl font-extrabold text-white">{{ $card['value'] }}</p>
            <p class="text-xs text-slate-500 mt-1">{{ $card['label'] }}</p>
            @if($card['trend'] !== null)
                <p class="text-[11px] font-bold mt-2 {{ $card['trend'] >= 0 ? 'text-emerald-400' : 'text-red-400' }}">
                    {{ $card['trend'] >= 0 ? '↑' : '↓' }} {{ number_format(abs($card['trend']), 1) }}% frente al periodo anterior
                </p>
            @endif
        </div>
    @endforeach
</div>

<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
    @foreach([
        ['label' => 'Jugadores activos', 'value' => number_format($stats['jugadores_periodo']), 'meta' => ($stats['tendencia_jugadores'] >= 0 ? '+' : '') . number_format($stats['tendencia_jugadores'], 1) . '%'],
        ['label' => 'Partidas jugadas', 'value' => number_format($stats['partidas_periodo']), 'meta' => number_format($stats['partidas_periodo'] / max($days, 1), 1) . ' por día'],
        ['label' => 'Apuesta media', 'value' => '€' . number_format($stats['apuesta_media'], 2), 'meta' => 'por partida'],
        ['label' => 'Saldo en circulación', 'value' => '€' . number_format($stats['saldo_total'], 2), 'meta' => 'en todas las carteras'],
    ] as $metric)
        <div class="rounded-2xl bg-gradient-to-br from-white/[0.055] to-white/[0.015] border border-white/10 p-5">
            <p class="text-xs text-slate-500">{{ $metric['label'] }}</p>
            <p class="text-xl font-extrabold text-white mt-2">{{ $metric['value'] }}</p>
            <p class="text-[11px] text-cyan-400 mt-1">{{ $metric['meta'] }}</p>
        </div>
    @endforeach
</div>
@endif

{{-- Operativa de apuestas deportivas y cajas --}}
@if($user->hasAnyRole(['super_admin', 'admin']))
<div class="grid grid-cols-1 xl:grid-cols-2 gap-5 mb-8">
    <section class="rounded-2xl border border-cyan-500/20 bg-gradient-to-br from-cyan-500/[.09] to-white/[.02] p-5">
        <div class="flex items-center justify-between mb-5">
            <div><p class="text-[10px] font-black uppercase tracking-[.2em] text-cyan-400">Sportsbook</p><h3 class="text-lg font-extrabold text-white mt-1">Apuestas deportivas</h3></div>
            <span class="rounded-xl bg-cyan-400/10 px-3 py-2 text-sm font-black text-cyan-300">{{ number_format($stats['apuestas_deportivas']) }}</span>
        </div>
        <div class="grid grid-cols-2 gap-3">
            @foreach([
                ['Volumen', '€'.number_format($stats['volumen_deportivo'], 2), 'text-purple-300'],
                ['Premios pagados', '€'.number_format($stats['premios_deportivos'], 2), 'text-emerald-300'],
                ['Beneficio', '€'.number_format($stats['beneficio_deportivo'], 2), $stats['beneficio_deportivo'] >= 0 ? 'text-cyan-300' : 'text-red-300'],
                ['Pendientes', number_format($stats['apuestas_pendientes']), 'text-amber-300'],
            ] as [$label, $value, $color])
            <div class="rounded-xl border border-white/5 bg-black/20 p-3"><p class="text-[11px] text-slate-500">{{ $label }}</p><p class="mt-1 font-extrabold {{ $color }}">{{ $value }}</p></div>
            @endforeach
        </div>
    </section>
    <section class="rounded-2xl border border-fuchsia-500/20 bg-gradient-to-br from-fuchsia-500/[.09] to-white/[.02] p-5">
        <div class="flex items-center justify-between mb-5">
            <div><p class="text-[10px] font-black uppercase tracking-[.2em] text-fuchsia-400">Loot boxes</p><h3 class="text-lg font-extrabold text-white mt-1">Rendimiento de cajas</h3></div>
            <span class="rounded-xl bg-fuchsia-400/10 px-3 py-2 text-sm font-black text-fuchsia-300">{{ number_format($stats['cajas_abiertas']) }}</span>
        </div>
        <div class="grid grid-cols-2 gap-3">
            @foreach([
                ['Ingresos', '€'.number_format($stats['ingresos_cajas'], 2), 'text-purple-300'],
                ['Canjeado', '€'.number_format($stats['pagado_canje'], 2).' · '.$stats['cajas_canjeadas'], 'text-emerald-300'],
                ['Beneficio', '€'.number_format($stats['beneficio_cajas'], 2), $stats['beneficio_cajas'] >= 0 ? 'text-fuchsia-300' : 'text-red-300'],
                ['Inventario pendiente', '€'.number_format($stats['valor_inventario'], 2), 'text-amber-300'],
            ] as [$label, $value, $color])
            <div class="rounded-xl border border-white/5 bg-black/20 p-3"><p class="text-[11px] text-slate-500">{{ $label }}</p><p class="mt-1 font-extrabold {{ $color }}">{{ $value }}</p></div>
            @endforeach
        </div>
    </section>
</div>
@endif

{{-- Alerts — visibles para admin/moderator --}}
@if($user->hasAnyRole(['super_admin', 'admin', 'moderator']))
<div class="grid grid-cols-1 lg:grid-cols-2 gap-4 mb-8">
    <div class="rounded-2xl bg-white/[0.03] border border-white/5 p-5">
        <div class="flex items-center justify-between mb-3">
            <h3 class="text-sm font-bold text-white">Reviews pendientes</h3>
            <span class="px-2 py-0.5 rounded-lg bg-amber-500/10 text-amber-400 text-xs font-bold">{{ $stats['reviews_pendientes'] }}</span>
        </div>
        <a href="{{ route('admin.reviews', ['estado' => 'pendiente']) }}" class="text-xs text-brand-400 hover:text-brand-300 transition">Ver todas →</a>
    </div>
    <div class="rounded-2xl bg-white/[0.03] border border-white/5 p-5">
        <div class="flex items-center justify-between mb-3">
            <h3 class="text-sm font-bold text-white">Feedback abierto</h3>
            <span class="px-2 py-0.5 rounded-lg bg-blue-500/10 text-blue-400 text-xs font-bold">{{ $stats['feedback_abierto'] }}</span>
        </div>
        <a href="{{ route('admin.feedback', ['estado' => 'abierto']) }}" class="text-xs text-brand-400 hover:text-brand-300 transition">Ver todos →</a>
    </div>
</div>
@endif

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    {{-- Partidas por juego — admin --}}
    @if($user->hasAnyRole(['super_admin', 'admin']))
    <div class="rounded-2xl bg-white/[0.03] border border-white/5 p-5">
        <h3 class="text-sm font-bold text-white uppercase tracking-wider mb-4">Partidas por juego</h3>
        <div class="space-y-3">
            @forelse($partidasPorJuego as $pj)
                <div class="flex items-center justify-between">
                    <span class="text-sm text-slate-300 capitalize">{{ $pj->juego }}</span>
                    <div class="flex items-center gap-4 text-xs">
                        <span class="text-slate-500">{{ $pj->total }} partidas</span>
                        <span class="text-emerald-400 font-semibold">€{{ number_format($pj->apuestas, 2) }}</span>
                    </div>
                </div>
            @empty
                <p class="text-sm text-slate-600">Sin datos aun.</p>
            @endforelse
        </div>
    </div>
    @endif

    {{-- Actividad reciente — admin/moderator --}}
    @if($user->hasAnyRole(['super_admin', 'admin', 'moderator']))
    <div class="rounded-2xl bg-white/[0.03] border border-white/5 p-5">
        <h3 class="text-sm font-bold text-white uppercase tracking-wider mb-4">Actividad reciente</h3>
        <div class="space-y-3 max-h-64 overflow-y-auto">
            @forelse($ultimasActividades as $log)
                <div class="flex items-start gap-3 text-xs">
                    <div class="w-2 h-2 rounded-full bg-brand-500 mt-1.5 shrink-0"></div>
                    <div>
                        <span class="text-slate-400">{{ $log->usuario?->name ?? 'Sistema' }}</span>
                        <span class="text-slate-500">{{ $log->accion }}</span>
                        @if($log->modelo)
                            <span class="text-slate-600">({{ $log->modelo }} #{{ $log->modelo_id }})</span>
                        @endif
                        <div class="text-slate-600 mt-0.5">{{ $log->created_at->diffForHumans() }}</div>
                    </div>
                </div>
            @empty
                <p class="text-sm text-slate-600">Sin actividad.</p>
            @endforelse
        </div>
    </div>
    @endif
</div>

{{-- Top juegos por ingresos — solo super_admin --}}
@if($user->hasPermissionTo('stats.revenue') && $topJuegos->count())
<div class="rounded-2xl bg-white/[0.03] border border-white/5 p-5 mt-6">
    <h3 class="text-sm font-bold text-white uppercase tracking-wider mb-4">Top juegos por ingresos</h3>
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-white/5">
                    <th class="text-left px-4 py-2 text-xs font-bold text-slate-500 uppercase">Juego</th>
                    <th class="text-right px-4 py-2 text-xs font-bold text-slate-500 uppercase">Partidas</th>
                    <th class="text-right px-4 py-2 text-xs font-bold text-slate-500 uppercase">Apostado</th>
                    <th class="text-right px-4 py-2 text-xs font-bold text-slate-500 uppercase">Ganado</th>
                    <th class="text-right px-4 py-2 text-xs font-bold text-slate-500 uppercase">Beneficio</th>
                </tr>
            </thead>
            <tbody>
                @foreach($topJuegos as $tj)
                    @php $beneficio = $tj->total_apuestas - $tj->total_ganancias; @endphp
                    <tr class="border-b border-white/[0.03]">
                        <td class="px-4 py-2.5 capitalize font-semibold text-white">{{ $tj->juego }}</td>
                        <td class="px-4 py-2.5 text-right text-slate-400">{{ number_format($tj->total_partidas) }}</td>
                        <td class="px-4 py-2.5 text-right text-purple-400 font-semibold">€{{ number_format($tj->total_apuestas, 2) }}</td>
                        <td class="px-4 py-2.5 text-right text-emerald-400">€{{ number_format($tj->total_ganancias, 2) }}</td>
                        <td class="px-4 py-2.5 text-right font-bold {{ $beneficio >= 0 ? 'text-emerald-400' : 'text-red-400' }}">
                            €{{ number_format($beneficio, 2) }}
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endif

{{-- Grafico de crecimiento usuarios (ultimos 30 dias) — solo super_admin --}}
@if($user->hasPermissionTo('stats.growth') && $usuariosPorDia->count())
<div class="rounded-2xl bg-white/[0.03] border border-white/5 p-5 mt-6">
    <h3 class="text-sm font-bold text-white uppercase tracking-wider mb-4">Crecimiento de usuarios ({{ $days }} días)</h3>
    <div class="flex items-end gap-1 h-32">
        @php
            $maxUsers = $usuariosPorDia->max('total') ?: 1;
        @endphp
        @foreach($usuariosPorDia as $dia)
            @php $height = max(4, ($dia->total / $maxUsers) * 100); @endphp
            <div class="flex-1 group relative" style="height: {{ $height }}%">
                <div class="absolute inset-0 rounded-t bg-brand-500/40 hover:bg-brand-500/60 transition cursor-pointer"></div>
                <div class="absolute -top-8 left-1/2 -translate-x-1/2 hidden group-hover:block px-2 py-1 rounded bg-slate-800 text-[10px] text-white whitespace-nowrap z-10">
                    {{ $dia->dia }}: {{ $dia->total }}
                </div>
            </div>
        @endforeach
    </div>
    <div class="flex justify-between mt-2 text-[10px] text-slate-600">
        <span>{{ $usuariosPorDia->first()?->dia }}</span>
        <span>{{ $usuariosPorDia->last()?->dia }}</span>
    </div>
</div>
@endif

{{-- Grafico de ingresos vs gastos (ultimos 30 dias) — solo super_admin --}}
@if($user->hasPermissionTo('stats.growth') && $ingresosPorDia->count())
<div class="rounded-2xl bg-white/[0.03] border border-white/5 p-5 mt-6">
    <h3 class="text-sm font-bold text-white uppercase tracking-wider mb-4">Apuestas vs Pagos ({{ $days }} días)</h3>
    <div class="space-y-2">
        @php
            $maxIngreso = $ingresosPorDia->max('apuestas') ?: 1;
        @endphp
        @foreach($ingresosPorDia as $dia)
            @php
                $pctApostado = ($dia->apuestas / $maxIngreso) * 100;
                $pctGanado = $maxIngreso > 0 ? ($dia->ganancias / $maxIngreso) * 100 : 0;
            @endphp
            <div class="flex items-center gap-3 text-xs">
                <span class="w-16 text-slate-600 shrink-0">{{ \Carbon\Carbon::parse($dia->dia)->format('d/m') }}</span>
                <div class="flex-1 flex flex-col gap-0.5">
                    <div class="h-2 rounded bg-purple-500/40" style="width: {{ $pctApostado }}%"></div>
                    <div class="h-2 rounded bg-emerald-500/40" style="width: {{ $pctGanado }}%"></div>
                </div>
                <span class="text-slate-500 w-20 text-right">€{{ number_format($dia->apuestas, 0) }}</span>
            </div>
        @endforeach
    </div>
    <div class="flex items-center gap-4 mt-3 text-[10px] text-slate-600">
        <span class="flex items-center gap-1"><span class="w-2 h-2 rounded bg-purple-500/40"></span> Apostado</span>
        <span class="flex items-center gap-1"><span class="w-2 h-2 rounded bg-emerald-500/40"></span> Pagado</span>
    </div>
</div>
@endif
@endsection
