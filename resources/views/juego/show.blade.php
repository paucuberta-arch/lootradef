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
            <div class="rounded-2xl overflow-hidden mb-8 {{ $juego['grad'] }}" x-data="{ imageLoaded: false }">
                <div class="relative flex min-h-[290px] h-full flex-col justify-end p-5 sm:min-h-[350px] sm:p-10">
                    <div x-show="!imageLoaded" class="absolute inset-0 animate-pulse bg-gradient-to-br from-white/5 to-transparent" aria-hidden="true"></div>
                    @if(!empty($juego['image']))
                        <img src="{{ $juego['image'] }}" alt="{{ $juego['name'] }}" class="absolute inset-0 w-full h-full object-cover" loading="eager" x-on:load="imageLoaded=true" x-on:error="$event.currentTarget.src=@js(asset('images/game-fallback.svg')); imageLoaded=true">
                    @endif
                    <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-black/20 to-transparent"></div>
                    <div class="relative z-10">
                        <div class="flex items-center gap-3 mb-3">
                            <span class="px-3 py-1 rounded-lg bg-white/10 border border-white/10 text-xs font-semibold text-white backdrop-blur-sm">{{ $juego['cat'] }}</span>
                            <span class="px-3 py-1 rounded-lg bg-white/10 border border-white/10 text-xs font-semibold text-white backdrop-blur-sm">{{ $juego['provider'] }}</span>
                        </div>
                        <h1 class="text-3xl sm:text-4xl lg:text-5xl font-extrabold text-white mb-4">{{ $juego['name'] }}</h1>
                        <div class="flex flex-wrap gap-3">
                            @if($playUrl)
                                <a href="{{ $playUrl }}" class="px-6 py-3 rounded-xl bg-brand-500 hover:bg-brand-400 text-black font-bold transition shadow-lg shadow-brand-500/20">
                                    Jugar ahora
                                </a>
                            @else
                                <a href="#" class="px-6 py-3 rounded-xl bg-white/10 hover:bg-white/15 text-white font-semibold border border-white/10 transition backdrop-blur-sm opacity-50 cursor-not-allowed">
                                    Proximamente
                                </a>
                            @endif
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
            <div class="rounded-2xl bg-white/[0.03] border border-white/5 p-6">
                <h3 class="text-sm font-bold text-white uppercase tracking-wider mb-4">Juegos similares</h3>
                <div class="space-y-3">
                    @forelse($similarGames as $similar)
                        <a href="{{ route('games.show', $similar['slug']) }}" x-data class="flex items-center gap-3 p-2 rounded-xl hover:bg-white/5 transition">
                            <img src="{{ $similar['image'] }}" alt="" class="h-10 w-10 rounded-lg object-cover" loading="lazy" x-on:error="$event.currentTarget.src=@js(asset('images/game-fallback.svg'))">
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
