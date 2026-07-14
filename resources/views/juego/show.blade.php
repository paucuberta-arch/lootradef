@extends('layouts.app')

@section('title', $juego['name'] . ' — Lootra Casino')

@section('styles')
<style>
    .{{ $juego['grad'] }} { background: linear-gradient(135deg, {{ str_contains($juego['grad'], '5') ? '#f12711, #f5af19' : str_contains($juego['grad'], '2') ? '#2d1b69, #11998e' : str_contains($juego['grad'], '3') ? '#c31432, #240b36' : str_contains($juego['grad'], '4') ? '#0f0c29, #302b63' : str_contains($juego['grad'], '6') ? '#00b09b, #96c93d' : str_contains($juego['grad'], '7') ? '#7f00ff, #e100ff' : str_contains($juego['grad'], '8') ? '#fc4a1a, #f7b733' : str_contains($juego['grad'], '9') ? '#1f1c2c, #928dab' : str_contains($juego['grad'], '10') ? '#e44d26, #f16529' : str_contains($juego['grad'], '11') ? '#1a2a6c, #b21f1f' : '#0f2027, #203a43' }} 100%); }
</style>
@endsection

@section('contenido')

<div class="max-w-[1400px] mx-auto px-4 sm:px-6 lg:px-8 py-6 sm:py-10">

    {{-- Breadcrumb --}}
    <nav class="flex items-center gap-2 text-sm text-slate-500 mb-6">
        <a href="{{ url('/') }}" class="hover:text-white transition">Inicio</a>
        <span>/</span>
        <span class="text-slate-300">{{ $juego['name'] }}</span>
    </nav>

    <div class="flex flex-col lg:flex-row gap-8">

        {{-- Columna principal --}}
        <div class="flex-1 min-w-0">

            {{-- Hero del juego --}}
            <div class="rounded-2xl overflow-hidden mb-8 {{ $juego['grad'] }}" style="min-height: 350px;">
                <div class="relative h-full min-h-[350px] flex flex-col justify-end p-6 sm:p-10">
                    <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-black/20 to-transparent"></div>
                    <div class="relative z-10">
                        <div class="flex items-center gap-3 mb-3">
                            <span class="px-3 py-1 rounded-lg bg-white/10 border border-white/10 text-xs font-semibold text-white backdrop-blur-sm">{{ $juego['cat'] }}</span>
                            <span class="px-3 py-1 rounded-lg bg-white/10 border border-white/10 text-xs font-semibold text-white backdrop-blur-sm">{{ $juego['provider'] }}</span>
                        </div>
                        <h1 class="text-3xl sm:text-4xl lg:text-5xl font-extrabold text-white mb-4">{{ $juego['name'] }}</h1>
                        <div class="flex flex-wrap gap-3">
                            <a href="#" class="px-6 py-3 rounded-xl bg-brand-500 hover:bg-brand-400 text-black font-bold transition shadow-lg shadow-brand-500/20">
                                Jugar ahora
                            </a>
                            <a href="#" class="px-6 py-3 rounded-xl bg-white/10 hover:bg-white/15 text-white font-semibold border border-white/10 transition backdrop-blur-sm">
                                Jugar en demo
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Info del juego --}}
            <div class="rounded-2xl bg-white/[0.03] border border-white/5 p-6 sm:p-8 mb-6">
                <h2 class="text-xl font-bold text-white mb-4">Sobre el juego</h2>
                <p class="text-slate-400 leading-relaxed">{{ $juego['description'] }}</p>
            </div>

            {{-- Caracteristicas --}}
            <div class="rounded-2xl bg-white/[0.03] border border-white/5 p-6 sm:p-8">
                <h2 class="text-xl font-bold text-white mb-5">Caracteristicas</h2>
                <div class="grid grid-cols-2 sm:grid-cols-3 gap-4">
                    @foreach([
                        ['label' => 'Proveedor', 'value' => $juego['provider']],
                        ['label' => 'Categoria', 'value' => $juego['cat']],
                        ['label' => 'RTP', 'value' => $juego['rtp']],
                        ['label' => 'Volatilidad', 'value' => $juego['volatilidad']],
                        ['label' => 'Max Win', 'value' => $juego['max_win']],
                        ['label' => 'Apuesta minima', 'value' => $juego['min_bet']],
                        ['label' => 'Apuesta maxima', 'value' => $juego['max_bet']],
                        ['label' => 'Lineas', 'value' => $juego['lines']],
                        ['label' => 'Carretes', 'value' => $juego['reels']],
                    ] as $feat)
                        <div class="p-3 rounded-xl bg-white/[0.02] border border-white/5">
                            <p class="text-xs text-slate-500 mb-1">{{ $feat['label'] }}</p>
                            <p class="text-sm font-bold text-white">{{ $feat['value'] }}</p>
                        </div>
                    @endforeach
                </div>
            </div>

        </div>

        {{-- Sidebar info --}}
        <aside class="w-full lg:w-80 shrink-0 space-y-6">

            {{-- Resumen rapido --}}
            <div class="rounded-2xl bg-white/[0.03] border border-white/5 p-6">
                <h3 class="text-sm font-bold text-white uppercase tracking-wider mb-4">Resumen</h3>
                <div class="space-y-3">
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-slate-500">Proveedor</span>
                        <span class="text-sm font-semibold text-white">{{ $juego['provider'] }}</span>
                    </div>
                    <div class="h-px bg-white/5"></div>
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-slate-500">RTP</span>
                        <span class="text-sm font-semibold text-emerald-400">{{ $juego['rtp'] }}</span>
                    </div>
                    <div class="h-px bg-white/5"></div>
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-slate-500">Volatilidad</span>
                        <span class="text-sm font-semibold text-white">{{ $juego['volatilidad'] }}</span>
                    </div>
                    <div class="h-px bg-white/5"></div>
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-slate-500">Max Win</span>
                        <span class="text-sm font-bold text-brand-400">{{ $juego['max_win'] }}</span>
                    </div>
                </div>
                <a href="#" class="block w-full text-center mt-6 rounded-xl bg-brand-500 hover:bg-brand-400 text-black font-bold py-3 transition shadow-lg shadow-brand-500/20">
                    Jugar ahora
                </a>
            </div>

            {{-- Juegos similares --}}
            <div class="rounded-2xl bg-white/[0.03] border border-white/5 p-6">
                <h3 class="text-sm font-bold text-white uppercase tracking-wider mb-4">Juegos similares</h3>
                <div class="space-y-3">
                    @foreach(['Sweet Bonanza', 'Book of Dead', 'Starburst'] as $similar)
                        <div class="flex items-center gap-3 p-2 rounded-xl hover:bg-white/5 transition cursor-pointer">
                            <div class="w-10 h-10 rounded-lg bg-white/5 flex items-center justify-center text-lg">&#x1F3B0;</div>
                            <div>
                                <p class="text-sm font-semibold text-white">{{ $similar }}</p>
                                <p class="text-xs text-slate-500">Slots</p>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

        </aside>

    </div>

</div>

@endsection
