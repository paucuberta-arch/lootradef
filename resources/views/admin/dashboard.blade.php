@extends('layouts.admin')
@section('admin-title', 'Dashboard')

@section('admin-content')
{{-- Stats Cards --}}
<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
    @php
        $cards = [
            ['label' => 'Usuarios totales', 'value' => number_format($stats['usuarios']), 'icon' => 'M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z', 'color' => 'blue'],
            ['label' => 'Jugadores hoy', 'value' => $stats['jugadores_hoy'], 'icon' => 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z', 'color' => 'emerald'],
            ['label' => 'Partidas hoy', 'value' => number_format($stats['partidas_hoy']), 'icon' => 'M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z', 'color' => 'brand'],
            ['label' => 'Apostado hoy', 'value' => '€' . number_format($stats['apostado_hoy'], 2), 'icon' => 'M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z', 'color' => 'purple'],
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

{{-- Alerts --}}
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

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    {{-- Partidas por juego --}}
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

    {{-- Actividad reciente --}}
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
</div>
@endsection
