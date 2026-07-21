@extends('layouts.app')

@section('title', $juego['name'] . ' — Lootra Casino')

@section('contenido')

<div class="max-w-[1400px] mx-auto px-4 sm:px-6 lg:px-8 py-6 sm:py-10">

    {{-- Breadcrumb --}}
    <nav class="flex items-center gap-2 text-sm text-slate-500 mb-6">
        <a href="{{ route('games.index') }}" class="hover:text-white transition">Juegos</a>
        <span>/</span>
        <span class="text-slate-300">{{ $juego['name'] }}</span>
    </nav>

    <div class="flex flex-col lg:flex-row gap-8">

        {{-- Columna principal --}}
        <div class="flex-1 min-w-0">

            {{-- Hero del juego --}}
            <div class="game-detail-hero world-panel mb-8 overflow-hidden rounded-3xl {{ $juego['grad'] }}" x-data="{ imageLoaded: false }">
                <div x-show="!imageLoaded" class="absolute inset-0 animate-pulse bg-gradient-to-br from-white/5 to-transparent" aria-hidden="true"></div>
                @if(!empty($juego['image']))
                    <img src="{{ $juego['image'] }}" alt="" class="absolute inset-0 h-full w-full scale-110 object-cover opacity-25 blur-xl" aria-hidden="true" fetchpriority="high" decoding="async">
                @endif
                <div class="absolute inset-0 bg-[linear-gradient(100deg,rgba(5,7,17,.98)_6%,rgba(5,7,17,.88)_53%,rgba(5,7,17,.3))]"></div>
                <div class="absolute inset-0 bg-gradient-to-t from-[#050711] via-transparent to-transparent"></div>

                <div class="relative grid min-h-[330px] items-end gap-8 p-6 sm:p-9 md:grid-cols-[minmax(0,1fr)_210px] lg:min-h-[410px] lg:grid-cols-[minmax(0,1fr)_260px] lg:p-12">
                    <div class="relative z-10 max-w-2xl py-3">
                        <p class="world-kicker">Ficha de juego</p>
                        <div class="mb-4 mt-4 flex flex-wrap items-center gap-2">
                            <span class="rounded-lg border border-white/10 bg-white/10 px-3 py-1 text-xs font-semibold text-white backdrop-blur-sm">{{ ucfirst($juego['cat']) }}</span>
                            <span class="rounded-lg border border-white/10 bg-white/10 px-3 py-1 text-xs font-semibold text-white backdrop-blur-sm">{{ $juego['provider'] }}</span>
                        </div>
                        <h1 class="font-display text-4xl font-black leading-[.94] tracking-[-.05em] text-white sm:text-5xl lg:text-6xl">{{ $juego['name'] }}</h1>
                        <p class="mt-4 max-w-xl text-sm leading-6 text-slate-300">{{ $juego['description'] }}</p>
                        <div class="mt-7 flex flex-wrap gap-3">
                            @if($playUrl)
                                <a href="{{ $playUrl }}" class="cta-shine rounded-xl bg-gradient-to-r from-brand-300 via-brand-400 to-emerald-400 px-6 py-3 font-extrabold text-black shadow-lg shadow-brand-500/20 transition hover:-translate-y-1">Jugar ahora <span class="ml-2">→</span></a>
                            @else
                                <span class="cursor-not-allowed rounded-xl border border-white/10 bg-white/10 px-6 py-3 font-semibold text-slate-400">Próximamente</span>
                            @endif
                        </div>
                    </div>
                    @if(!empty($juego['image']))
                        <div class="relative hidden self-center md:block">
                            <div class="absolute inset-5 rounded-3xl bg-[var(--world-accent)] opacity-20 blur-3xl"></div>
                            <img src="{{ $juego['image'] }}" alt="Portada de {{ $juego['name'] }}" class="game-detail-poster relative aspect-[4/5] w-full rounded-2xl object-cover ring-1 ring-white/15" decoding="async" fetchpriority="high" x-on:load="imageLoaded=true" x-on:error="$event.currentTarget.src=@js(asset('images/game-fallback.svg')); imageLoaded=true">
                        </div>
                    @endif
                </div>
            </div>

            {{-- Info del juego --}}
            <div class="world-panel rounded-2xl p-6 sm:p-8 mb-6">
                <h2 class="text-xl font-bold text-white mb-4">Sobre el juego</h2>
                <p class="text-slate-400 leading-relaxed">{{ $juego['description'] }}</p>
            </div>

            {{-- Caracteristicas --}}
            <div class="world-panel rounded-2xl p-6 sm:p-8">
                <h2 class="text-xl font-bold text-white mb-5">Caracteristicas</h2>
                <div class="grid grid-cols-2 sm:grid-cols-3 gap-2 sm:gap-4">
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

            {{-- Reviews --}}
            <x-review-widget :slug="$slug" :reviews="$reviews" review-type="juego" />

        </div>

        {{-- Sidebar info --}}
        <aside class="w-full lg:w-80 shrink-0 space-y-6">

            {{-- Resumen rapido --}}
            <div class="world-panel rounded-2xl p-6">
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
                @if($playUrl)
                    <a href="{{ $playUrl }}" class="block w-full text-center mt-6 rounded-xl bg-brand-500 hover:bg-brand-400 text-black font-bold py-3 transition shadow-lg shadow-brand-500/20">
                        Jugar ahora
                    </a>
                @else
                    <div class="block w-full text-center mt-6 rounded-xl bg-white/5 text-slate-500 font-bold py-3 border border-white/5">
                        Proximamente
                    </div>
                @endif
            </div>

            {{-- Juegos similares --}}
            <div class="world-panel rounded-2xl p-6">
                <h3 class="text-sm font-bold text-white uppercase tracking-wider mb-4">Juegos similares</h3>
                <div class="space-y-3">
                    @forelse($similarGames as $similar)
                        <a href="{{ route('games.show', $similar['slug']) }}" x-data class="flex items-center gap-3 p-2 rounded-xl hover:bg-white/5 transition">
                            <img src="{{ $similar['image'] }}" alt="" class="h-10 w-10 rounded-lg object-cover" loading="lazy" decoding="async" x-on:error="$event.currentTarget.src=@js(asset('images/game-fallback.svg'))">
                            <div>
                                <p class="text-sm font-semibold text-white">{{ $similar['name'] }}</p>
                                <p class="text-xs text-slate-500">{{ ucfirst($similar['category']) }}</p>
                            </div>
                        </a>
                    @empty
                        <p class="text-sm text-slate-500">No hay juegos similares disponibles.</p>
                    @endforelse
                </div>
            </div>

        </aside>

    </div>

</div>

@endsection
