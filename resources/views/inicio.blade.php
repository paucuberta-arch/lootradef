@extends('layouts.app')

@section('title', 'Lootra Casino — Juegos de Casino Online')

@section('contenido')

@php
    $cats = [
        ['id' => 'todos', 'label' => 'Todos', 'icon' => '&#x1F3AE;'],
        ['id' => 'slots', 'label' => 'Slots', 'icon' => '&#x1F3B0;'],
        ['id' => 'ruleta', 'label' => 'Ruleta', 'icon' => '&#x1F3B2;'],
        ['id' => 'blackjack', 'label' => 'Blackjack', 'icon' => '&#x1F0CF;'],
        ['id' => 'poker', 'label' => 'Poker', 'icon' => '&#x1F0AD;'],
        ['id' => 'live', 'label' => 'Live Casino', 'icon' => '&#x1F4FA;'],
        ['id' => 'crash', 'label' => 'Crash', 'icon' => '&#x1F680;'],
        ['id' => 'arcade', 'label' => 'Originales', 'icon' => '&#x2728;'],
        ['id' => 'cartas', 'label' => 'Cartas', 'icon' => '&#x1F0CF;'],
        ['id' => 'numeros', 'label' => 'Números', 'icon' => '&#x1F522;'],
    ];
@endphp

<div class="max-w-[1400px] mx-auto px-4 sm:px-6 lg:px-8 py-6 sm:py-10"
     x-data="{
         selected: new URLSearchParams(window.location.search).get('cat') || 'todos',
         search: new URLSearchParams(window.location.search).get('q') || '',
         sort: new URLSearchParams(window.location.search).get('sort') || 'default',
         juegos: @js($juegos->values()),
         init() {
             const cat = new URLSearchParams(window.location.search).get('cat');
             if (cat && this.juegos.some(j => j.cat === cat)) this.selected = cat;
         },
         syncUrl() {
             const params = new URLSearchParams();
             if (this.selected !== 'todos') params.set('cat', this.selected);
             if (this.search.trim()) params.set('q', this.search.trim());
             if (this.sort !== 'default') params.set('sort', this.sort);
             history.replaceState({}, '', `${location.pathname}${params.size ? '?' + params : ''}#catalogo`);
         },
         selectCat(category) { this.selected = category; this.syncUrl(); },
         selectSort(value) { this.sort = value; this.syncUrl(); },
         resetFilters() { this.selected='todos'; this.search=''; this.sort='default'; this.syncUrl(); },
         get filtered() {
             let list = this.juegos;
             if (this.selected !== 'todos') list = list.filter(j => j.cat === this.selected);
             if (this.search.trim() !== '') {
                 const q = this.search.toLowerCase();
                 list = list.filter(j => j.name.toLowerCase().includes(q) || j.provider.toLowerCase().includes(q));
             }
             if (this.sort === 'az') list = [...list].sort((a,b) => a.name.localeCompare(b.name));
             if (this.sort === 'popular') list = [...list].sort((a,b) => (b.badge === 'Popular' ? 1 : 0) - (a.badge === 'Popular' ? 1 : 0));
             if (this.sort === 'rtp') list = [...list].sort((a,b) => parseFloat(b.rtp) - parseFloat(a.rtp));
             return list;
         },
         catCount(cat) {
             if (cat === 'todos') return this.juegos.length;
             return this.juegos.filter(j => j.cat === cat).length;
         }
     }">

    <section class="relative overflow-hidden rounded-[2rem] border border-white/10 min-h-[420px] mb-10 flex items-end hero-casino">
        <img src="{{ asset('images/lootra-hero.webp') }}"
             alt="Mesa de casino premium iluminada" class="absolute inset-0 w-full h-full object-cover" fetchpriority="high">
        <div class="absolute inset-0 bg-gradient-to-r from-[#070712] via-[#09081a]/90 to-fuchsia-950/25"></div>
        <div class="absolute inset-0 bg-gradient-to-t from-[#070712] via-transparent to-cyan-500/10"></div>
        <div class="absolute -right-20 -top-20 w-80 h-80 rounded-full bg-fuchsia-500/25 blur-[90px]"></div>
        <div class="relative z-10 p-7 sm:p-12 lg:p-16 max-w-3xl">
            <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-cyan-400/10 border border-cyan-300/20 text-cyan-200 text-xs font-bold uppercase tracking-[.18em] mb-5">
                <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                Casino de nueva generación
            </div>
            <h1 class="font-display text-4xl sm:text-6xl lg:text-7xl font-bold tracking-[-.06em] leading-[.95] text-white mb-5">
                Tu próxima gran <span class="text-transparent bg-clip-text bg-gradient-to-r from-brand-300 via-fuchsia-400 to-cyan-300">jugada</span> empieza aquí.
            </h1>
            <p class="text-slate-300 text-base sm:text-lg max-w-xl leading-relaxed mb-7">Juegos con ritmo, recompensas instantáneas y una experiencia visual creada para que cada ronda se sienta única.</p>
            <div class="flex flex-wrap gap-3">
                <button @click="selectCat('slots'); $nextTick(() => document.querySelector('#catalogo').scrollIntoView({behavior:'smooth'}))" class="cta-shine px-6 py-3.5 rounded-xl bg-gradient-to-r from-brand-300 via-brand-400 to-emerald-400 text-black font-extrabold shadow-xl shadow-brand-500/20 hover:scale-105 transition-transform">Explorar juegos</button>
                @guest
                    <a href="{{ route('registro') }}" class="px-6 py-3.5 rounded-xl bg-white/10 border border-white/15 text-white font-bold backdrop-blur-xl hover:bg-white/15 hover:border-cyan-300/30 transition">Crear cuenta</a>
                @else
                    <a href="{{ route('profile.show') }}" class="px-6 py-3.5 rounded-xl bg-white/10 border border-white/15 text-white font-bold backdrop-blur-xl hover:bg-white/15 hover:border-cyan-300/30 transition">Mi perfil</a>
                @endguest
            </div>
        </div>
        <div class="absolute right-8 bottom-8 hidden lg:grid grid-cols-3 gap-2 z-10">
            @foreach([['20', 'Juegos'], ['97%', 'RTP máx.'], ['24/7', 'Acceso']] as [$value, $label])
                <div class="min-w-24 p-3 rounded-xl bg-black/30 border border-white/10 backdrop-blur-xl text-center">
                    <div class="font-display font-bold text-white">{{ $value }}</div>
                    <div class="text-[10px] uppercase tracking-widest text-slate-400">{{ $label }}</div>
                </div>
            @endforeach
        </div>
    </section>

    <div id="catalogo" class="flex flex-col lg:flex-row gap-8 scroll-mt-24">

        {{-- SIDEBAR --}}
        <aside class="w-full lg:w-64 shrink-0">
            <div class="lg:sticky lg:top-20 space-y-6">

                {{-- Buscador movil --}}
                <div class="relative lg:hidden">
                    <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                    <input x-model.debounce.250ms="search" @input.debounce.300ms="syncUrl()" type="search" aria-label="Buscar juegos" placeholder="Buscar juegos..."
                        class="w-full pl-10 pr-4 py-2.5 rounded-xl bg-white/5 border border-white/10 text-sm text-white placeholder-slate-500 outline-none focus:border-brand-500 transition">
                </div>

                {{-- Categorias --}}
                <div class="rounded-2xl bg-white/[0.03] border border-white/5 p-5">
                    <h3 class="text-xs font-bold text-slate-500 uppercase tracking-wider mb-4">Categorias</h3>
                    <div class="space-y-1">
                        @foreach($cats as $cat)
                            <button
                                @click="selectCat('{{ $cat['id'] }}')"
                                :class="selected === '{{ $cat['id'] }}'
                                    ? 'bg-brand-500/10 border-brand-500/30 text-brand-400'
                                    : 'text-slate-400 hover:text-white hover:bg-white/5 border-transparent'"
                                class="w-full flex items-center justify-between px-3 py-2.5 rounded-xl text-sm font-medium border transition-all"
                            >
                                <span class="flex items-center gap-2.5">
                                    <span class="text-base">{!! $cat['icon'] !!}</span>
                                    {{ $cat['label'] }}
                                </span>
                                <span class="text-xs font-mono" :class="selected === '{{ $cat['id'] }}' ? 'text-brand-400/70' : 'text-slate-600'" x-text="catCount('{{ $cat['id'] }}')"></span>
                            </button>
                        @endforeach
                    </div>
                </div>

                {{-- Ordenar --}}
                <div class="rounded-2xl bg-white/[0.03] border border-white/5 p-5">
                    <h3 class="text-xs font-bold text-slate-500 uppercase tracking-wider mb-4">Ordenar por</h3>
                    <div class="space-y-1">
                        @php
                            $sortOpts = [
                                ['id' => 'default', 'label' => 'Predeterminado'],
                                ['id' => 'popular', 'label' => 'Populares'],
                                ['id' => 'az', 'label' => 'Alfabetico'],
                                ['id' => 'rtp', 'label' => 'Mayor RTP'],
                            ];
                        @endphp
                        @foreach($sortOpts as $opt)
                            <button
                                @click="selectSort('{{ $opt['id'] }}')"
                                :class="sort === '{{ $opt['id'] }}'
                                    ? 'bg-brand-500/10 border-brand-500/30 text-brand-400'
                                    : 'text-slate-400 hover:text-white hover:bg-white/5 border-transparent'"
                                class="w-full flex items-center gap-2.5 px-3 py-2.5 rounded-xl text-sm font-medium border transition-all text-left"
                            >
                                {{ $opt['label'] }}
                            </button>
                        @endforeach
                    </div>
                </div>

                {{-- Promo --}}
                <div class="rounded-2xl bg-gradient-to-br from-brand-500/20 to-brand-600/10 border border-brand-500/20 p-5">
                    <div class="text-2xl mb-3">&#x1F389;</div>
                    <h3 class="text-sm font-bold text-white mb-1">Bonus de bienvenida</h3>
                    <p class="text-xs text-slate-400 leading-relaxed mb-4">Registrate y recibe un bonus exclusivo para empezar a jugar.</p>
                    <a href="{{ route('registro') }}" class="block w-full text-center rounded-xl bg-brand-500 hover:bg-brand-400 text-black text-sm font-bold py-2.5 transition">
                        Registrarse
                    </a>
                </div>

            </div>
        </aside>

        {{-- CONTENIDO --}}
        <div class="flex-1 min-w-0">

            {{-- Buscador desktop --}}
            <div class="relative mb-6 hidden lg:block">
                <svg class="absolute left-4 top-1/2 -translate-y-1/2 w-5 h-5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
                <input x-model.debounce.250ms="search" @input.debounce.300ms="syncUrl()" type="search" aria-label="Buscar juegos" placeholder="Buscar por nombre o proveedor..."
                    class="w-full pl-12 pr-4 py-3 rounded-xl bg-white/5 border border-white/10 text-white placeholder-slate-500 outline-none focus:border-brand-500 focus:ring-1 focus:ring-brand-500/30 transition">
            </div>

            {{-- Featured 2x1 --}}
            <div class="mb-8">
                <div class="flex items-center justify-between mb-5">
                    <div><span class="text-[10px] font-bold uppercase tracking-[.2em] text-fuchsia-400">Selección Lootra</span><h2 class="text-2xl font-bold text-white mt-1">Destacados</h2></div>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 sm:gap-5">
                    <a href="{{ route('games.show', 'gates-of-olympus') }}" class="featured-card game-gradient-5 aspect-[16/9] sm:aspect-[16/10] flex items-end">
                        <img src="https://images.unsplash.com/photo-1551524559-8af4e6624178?w=1200&h=675&fit=crop" alt="Gates of Olympus" class="absolute inset-0 w-full h-full object-cover" loading="lazy">
                        <div class="featured-overlay"></div>
                        <div class="absolute top-4 left-4 z-10"><span class="px-3 py-1 rounded-lg bg-brand-500/90 text-black text-xs font-bold uppercase tracking-wider">Popular</span></div>
                        <div class="relative z-10 p-5 sm:p-6 w-full">
                            <div class="flex items-center gap-2 mb-2">
                                <span class="text-xs font-semibold text-brand-300">Pragmatic Play</span>
                                <span class="w-1 h-1 rounded-full bg-slate-600"></span>
                                <span class="text-xs text-slate-500">Slots</span>
                            </div>
                            <h3 class="text-xl sm:text-2xl font-extrabold text-white mb-3">Gates of Olympus</h3>
                            <div class="flex items-center gap-3">
                                <span class="px-5 py-2.5 rounded-xl bg-brand-500 text-black text-sm font-bold">Jugar ahora</span>
                                <span class="px-5 py-2.5 rounded-xl bg-white/10 text-white text-sm font-semibold border border-white/10">Ver detalles</span>
                            </div>
                        </div>
                    </a>
                    <a href="{{ route('games.show', 'texas-holdem') }}" class="featured-card game-gradient-2 aspect-[16/9] sm:aspect-[16/10] flex items-end">
                        <img src="{{ asset('images/poker-live.webp') }}" alt="Texas Hold'em Live" class="absolute inset-0 w-full h-full object-cover" loading="lazy">
                        <div class="featured-overlay"></div>
                        <div class="absolute top-4 left-4 z-10"><span class="px-3 py-1 rounded-lg bg-emerald-500/90 text-black text-xs font-bold uppercase tracking-wider">Nuevo</span></div>
                        <div class="relative z-10 p-5 sm:p-6 w-full">
                            <div class="flex items-center gap-2 mb-2">
                                <span class="text-xs font-semibold text-emerald-300">Lootra Originals</span>
                                <span class="w-1 h-1 rounded-full bg-slate-600"></span>
                                <span class="text-xs text-slate-500">Poker Live</span>
                            </div>
                            <h3 class="text-xl sm:text-2xl font-extrabold text-white mb-3">Texas Hold'em Live</h3>
                            <div class="flex items-center gap-3">
                                <span class="px-5 py-2.5 rounded-xl bg-brand-500 text-black text-sm font-bold">Jugar ahora</span>
                                <span class="px-5 py-2.5 rounded-xl bg-white/10 text-white text-sm font-semibold border border-white/10">Ver detalles</span>
                            </div>
                        </div>
                    </a>
                </div>
            </div>

            {{-- Grid juegos --}}
            <div>
                <div class="flex items-center justify-between mb-5">
                    <div><span class="text-[10px] font-bold uppercase tracking-[.2em] text-cyan-400">Catálogo</span><h2 class="text-2xl font-bold text-white mt-1">Todos los juegos</h2></div>
                    <span class="text-sm text-slate-500 font-medium" x-text="filtered.length + ' juegos'"></span>
                </div>

                <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-3 xl:grid-cols-4 gap-3 sm:gap-4">
                    <template x-for="j in filtered" :key="j.slug">
                        <a :href='@js(route('games.show', '__SLUG__')).replace("__SLUG__", encodeURIComponent(j.slug))' class="game-card aspect-[3/4]" :class="j.grad">
                            <img :src="j.image" :alt="j.name" class="absolute inset-0 w-full h-full object-cover" loading="lazy" x-on:error="$event.currentTarget.src=@js(asset('images/game-fallback.svg'))">
                            <div class="game-overlay"></div>

                            <template x-if="j.badge">
                                <div class="absolute top-3 left-3 z-10">
                                    <span class="px-2.5 py-1 rounded-lg text-[11px] font-bold uppercase tracking-wider" :class="j.badgeColor" x-text="j.badge"></span>
                                </div>
                            </template>

                            <div class="game-actions z-10">
                                <span class="block w-full text-center rounded-xl bg-brand-500 hover:bg-brand-400 text-black text-sm font-bold py-2.5 transition mb-2">Jugar</span>
                                <span class="block w-full text-center rounded-xl bg-white/10 hover:bg-white/15 text-white text-xs font-semibold py-2 transition border border-white/10">Ver detalles</span>
                            </div>

                            <div class="absolute bottom-0 left-0 right-0 p-3 sm:p-4 z-10">
                                <p class="text-xs text-slate-400 mb-0.5" x-text="j.provider"></p>
                                <p class="text-sm sm:text-base font-bold text-white leading-tight" x-text="j.name"></p>
                            </div>
                        </a>
                    </template>
                </div>

                <div x-show="filtered.length === 0" class="text-center py-20">
                    <svg class="mx-auto mb-4 h-10 w-10 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-width="1.5" d="m21 21-4.35-4.35m2.35-5.65a8 8 0 1 1-16 0 8 8 0 0 1 16 0Z"/></svg>
                    <p class="text-slate-400">No encontramos juegos con esos filtros.</p>
                    <button @click="resetFilters()" class="mt-4 rounded-xl border border-white/10 px-4 py-2 text-sm text-cyan-300 hover:bg-white/5">Limpiar filtros</button>
                </div>
            </div>

        </div>
    </div>
</div>

@endsection
