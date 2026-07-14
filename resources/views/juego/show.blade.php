@extends('layouts.app')

@section('title', $juego['name'] . ' — Lootra Casino')

@section('styles')
<style>
    .game-gradient-1 { background: linear-gradient(135deg, #1a1a2e 0%, #16213e 50%, #0f3460 100%); }
    .game-gradient-2 { background: linear-gradient(135deg, #2d1b69 0%, #11998e 100%); }
    .game-gradient-3 { background: linear-gradient(135deg, #c31432 0%, #240b36 100%); }
    .game-gradient-4 { background: linear-gradient(135deg, #0f0c29 0%, #302b63 50%, #24243e 100%); }
    .game-gradient-5 { background: linear-gradient(135deg, #f12711 0%, #f5af19 100%); }
    .game-gradient-6 { background: linear-gradient(135deg, #00b09b 0%, #96c93d 100%); }
    .game-gradient-7 { background: linear-gradient(135deg, #7f00ff 0%, #e100ff 100%); }
    .game-gradient-8 { background: linear-gradient(135deg, #fc4a1a 0%, #f7b733 100%); }
    .game-gradient-9 { background: linear-gradient(135deg, #1f1c2c 0%, #928dab 100%); }
    .game-gradient-10 { background: linear-gradient(135deg, #e44d26 0%, #f16529 100%); }
    .game-gradient-11 { background: linear-gradient(135deg, #1a2a6c 0%, #b21f1f 50%, #fdbb2d 100%); }
    .game-gradient-12 { background: linear-gradient(135deg, #0f2027 0%, #203a43 50%, #2c5364 100%); }
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
