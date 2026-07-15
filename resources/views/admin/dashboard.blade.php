@extends('layouts.admin')
@section('admin-title', 'Dashboard')

@section('admin-content')
@php
    $user = auth()->user();
@endphp

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
                <div class="w-10 h-10 rounded-xl bg-{{ $card['color'] }}-500/10 flex items-center justify-center">
                    <svg class="w-5 h-5 text-{{ $card['color'] }}-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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
            ['label' => 'Apostado hoy', 'value' => '€' . number_format($stats['apostado_hoy'], 2), 'icon' => 'M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z', 'color' => 'purple'],
            ['label' => 'Ganado hoy', 'value' => '€' . number_format($stats['ganado_hoy'], 2), 'icon' => 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z', 'color' => 'emerald'],
            ['label' => 'Beneficio hoy', 'value' => '€' . number_format($stats['beneficio_hoy'], 2), 'icon' => 'M13 7h8m0 0v8m0-8l-8 8-4-4-6 6', 'color' => $stats['beneficio_hoy'] >= 0 ? 'emerald' : 'red'],
            ['label' => 'Rating medio', 'value' => $stats['rating_promedio'] . ' ★', 'icon' => 'M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z', 'color' => 'brand'],
        ];
    @endphp
    @foreach($financeCards as $card)
        <div class="rounded-2xl bg-white/[0.03] border border-white/5 p-5">
            <div class="flex items-center gap-3 mb-3">
                <div class="w-10 h-10 rounded-xl bg-{{ $card['color'] }}-500/10 flex items-center justify-center">
                    <svg class="w-5 h-5 text-{{ $card['color'] }}-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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
    <h3 class="text-sm font-bold text-white uppercase tracking-wider mb-4">Crecimiento de usuarios (30 dias)</h3>
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
    <h3 class="text-sm font-bold text-white uppercase tracking-wider mb-4">Ingresos vs Pagos (30 dias)</h3>
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
