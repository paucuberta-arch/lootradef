@extends('layouts.app')

@section('title', 'Lootra Casino — Juegos de Casino Online')

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

@php
    $juegos = [
        ['slug' => 'gates-of-olympus', 'name' => 'Gates of Olympus', 'provider' => 'Pragmatic Play', 'cat' => 'slots', 'grad' => 'game-gradient-5', 'badge' => 'Popular', 'badgeColor' => 'bg-brand-500/90 text-black', 'rtp' => '96.5%', 'min' => '€0.20', 'max' => '€125', 'image' => 'https://images.unsplash.com/photo-1603565816030-6b389eeb23cb?auto=format&fit=crop&w=700&h=900&q=85'],
        ['slug' => 'crazy-time', 'name' => 'Crazy Time', 'provider' => 'Evolution', 'cat' => 'live', 'grad' => 'game-gradient-2', 'badge' => 'Nuevo', 'badgeColor' => 'bg-emerald-500/90 text-black', 'rtp' => '96.08%', 'min' => '€0.10', 'max' => '€1000', 'image' => 'https://images.unsplash.com/photo-1511882150382-421056c89033?w=600&h=800&fit=crop'],
        ['slug' => 'sweet-bonanza', 'name' => 'Sweet Bonanza', 'provider' => 'Pragmatic Play', 'cat' => 'slots', 'grad' => 'game-gradient-3', 'badge' => '', 'badgeColor' => '', 'rtp' => '96.48%', 'min' => '€0.20', 'max' => '€100', 'image' => 'https://images.unsplash.com/photo-1575224300306-1b8da36134ec?auto=format&fit=crop&w=700&h=900&q=85'],
        ['slug' => 'european-roulette', 'name' => 'European Roulette', 'provider' => 'NetEnt', 'cat' => 'ruleta', 'grad' => 'game-gradient-11', 'badge' => '', 'badgeColor' => '', 'rtp' => '97.3%', 'min' => '€0.10', 'max' => '€500', 'image' => 'https://images.unsplash.com/photo-1517232115160-ff93364542dd?w=600&h=800&fit=crop'],
        ['slug' => 'blackjack-vip', 'name' => 'Blackjack VIP', 'provider' => 'Evolution', 'cat' => 'blackjack', 'grad' => 'game-gradient-4', 'badge' => 'VIP', 'badgeColor' => 'bg-purple-500/90 text-white', 'rtp' => '99.28%', 'min' => '€5', 'max' => '€5000', 'image' => 'https://images.unsplash.com/photo-1541278107931-e006523892df?w=600&h=800&fit=crop'],
        ['slug' => 'book-of-dead', 'name' => 'Book of Dead', 'provider' => "Play'n GO", 'cat' => 'slots', 'grad' => 'game-gradient-1', 'badge' => '', 'badgeColor' => '', 'rtp' => '96.21%', 'min' => '€0.10', 'max' => '€100', 'image' => 'https://images.unsplash.com/photo-1539768942893-daf53e736b68?w=600&h=800&fit=crop'],
        ['slug' => 'crash-rocket', 'name' => 'Crash Rocket', 'provider' => 'Spribe', 'cat' => 'crash', 'grad' => 'game-gradient-8', 'badge' => 'Turbo', 'badgeColor' => 'bg-cyan-400/90 text-slate-950', 'rtp' => '97.0%', 'min' => '€0.10', 'max' => '€200', 'image' => 'https://images.unsplash.com/photo-1517976547714-720226b864c1?auto=format&fit=crop&w=700&h=900&q=85'],
        ['slug' => 'texas-holdem', 'name' => "Texas Hold'em", 'provider' => 'PokerStars', 'cat' => 'poker', 'grad' => 'game-gradient-6', 'badge' => '', 'badgeColor' => '', 'rtp' => '98.5%', 'min' => '€1', 'max' => '€10000', 'image' => 'https://images.unsplash.com/photo-1542317783-24cb2074f0a5?w=600&h=800&fit=crop'],
        ['slug' => 'starburst', 'name' => 'Starburst', 'provider' => 'NetEnt', 'cat' => 'slots', 'grad' => 'game-gradient-7', 'badge' => 'Clasico', 'badgeColor' => 'bg-blue-500/90 text-white', 'rtp' => '96.09%', 'min' => '€0.10', 'max' => '€100', 'image' => 'https://images.unsplash.com/photo-1462331940025-496dfbfc7564?w=600&h=800&fit=crop'],
        ['slug' => 'lightning-roulette', 'name' => 'Lightning Roulette', 'provider' => 'Evolution', 'cat' => 'ruleta', 'grad' => 'game-gradient-12', 'badge' => '', 'badgeColor' => '', 'rtp' => '97.3%', 'min' => '€0.20', 'max' => '€500', 'image' => 'https://images.unsplash.com/photo-1507400492013-162706c8c05e?w=600&h=800&fit=crop'],
        ['slug' => 'big-bass-bonanza', 'name' => 'Big Bass Bonanza', 'provider' => 'Pragmatic Play', 'cat' => 'slots', 'grad' => 'game-gradient-9', 'badge' => '', 'badgeColor' => '', 'rtp' => '96.71%', 'min' => '€0.10', 'max' => '€250', 'image' => 'https://images.unsplash.com/photo-1544551763-77ef2d0cfc6c?auto=format&fit=crop&w=700&h=900&q=85'],
        ['slug' => 'blackjack-classic', 'name' => 'Blackjack Classic', 'provider' => 'Microgaming', 'cat' => 'blackjack', 'grad' => 'game-gradient-10', 'badge' => '', 'badgeColor' => '', 'rtp' => '99.91%', 'min' => '€1', 'max' => '€2000', 'image' => 'https://images.unsplash.com/photo-1560015534-cee980ba7e13?w=600&h=800&fit=crop'],
    ];

    $juegos = collect($juegos)
        ->keyBy('slug')
        ->merge(collect(config('arcade_games'))->keyBy('slug'))
        ->values()
        ->all();

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
         search: '',
         sort: 'default',
         juegos: @js($juegos),
         init() {
             const cat = new URLSearchParams(window.location.search).get('cat');
             if (cat && this.juegos.some(j => j.cat === cat)) this.selected = cat;
         },
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
                <button @click="selected = 'slots'; $nextTick(() => document.querySelector('#catalogo').scrollIntoView({behavior:'smooth'}))" class="cta-shine px-6 py-3.5 rounded-xl bg-gradient-to-r from-brand-400 via-orange-400 to-fuchsia-500 text-black font-extrabold shadow-xl shadow-fuchsia-500/20 hover:scale-105 transition-transform">Explorar juegos</button>
                @guest
                    <a href="{{ route('registro') }}" class="px-6 py-3.5 rounded-xl bg-white/10 border border-white/15 text-white font-bold backdrop-blur-xl hover:bg-white/15 hover:border-cyan-300/30 transition">Crear cuenta</a>
                @else
                    <a href="{{ route('perfil') }}" class="px-6 py-3.5 rounded-xl bg-white/10 border border-white/15 text-white font-bold backdrop-blur-xl hover:bg-white/15 hover:border-cyan-300/30 transition">Mi perfil</a>
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
                    <input x-model="search" type="text" placeholder="Buscar juegos..."
                        class="w-full pl-10 pr-4 py-2.5 rounded-xl bg-white/5 border border-white/10 text-sm text-white placeholder-slate-500 outline-none focus:border-brand-500 transition">
                </div>

                {{-- Categorias --}}
                <div class="rounded-2xl bg-white/[0.03] border border-white/5 p-5">
                    <h3 class="text-xs font-bold text-slate-500 uppercase tracking-wider mb-4">Categorias</h3>
                    <div class="space-y-1">
                        @foreach($cats as $cat)
                            <button
                                @click="selected = '{{ $cat['id'] }}'"
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
                                @click="sort = '{{ $opt['id'] }}'"
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
                <input x-model="search" type="text" placeholder="Buscar por nombre o proveedor..."
                    class="w-full pl-12 pr-4 py-3 rounded-xl bg-white/5 border border-white/10 text-white placeholder-slate-500 outline-none focus:border-brand-500 focus:ring-1 focus:ring-brand-500/30 transition">
            </div>

            {{-- Featured 2x1 --}}
            <div class="mb-8">
                <div class="flex items-center justify-between mb-5">
                    <div><span class="text-[10px] font-bold uppercase tracking-[.2em] text-fuchsia-400">Selección Lootra</span><h2 class="text-2xl font-bold text-white mt-1">Destacados</h2></div>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 sm:gap-5">
                    <a href="{{ route('juego.show', 'gates-of-olympus') }}" class="featured-card game-gradient-5 aspect-[16/9] sm:aspect-[16/10] flex items-end">
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
                                <span class="px-5 py-2.5 rounded-xl bg-white/10 text-white text-sm font-semibold border border-white/10">Demo</span>
                            </div>
                        </div>
                    </a>
                    <a href="{{ route('juego.show', 'texas-holdem') }}" class="featured-card game-gradient-2 aspect-[16/9] sm:aspect-[16/10] flex items-end">
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
                                <span class="px-5 py-2.5 rounded-xl bg-white/10 text-white text-sm font-semibold border border-white/10">Demo</span>
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
                        <a :href="'{{ url('/juego') }}/' + j.slug" class="game-card aspect-[3/4]" :class="j.grad">
                            <img :src="j.image" :alt="j.name" class="absolute inset-0 w-full h-full object-cover" loading="lazy" onerror="this.style.display='none'">
                            <div class="game-overlay"></div>

                            <template x-if="j.badge">
                                <div class="absolute top-3 left-3 z-10">
                                    <span class="px-2.5 py-1 rounded-lg text-[11px] font-bold uppercase tracking-wider" :class="j.badgeColor" x-text="j.badge"></span>
                                </div>
                            </template>

                            <div class="game-actions z-10">
                                <span class="block w-full text-center rounded-xl bg-brand-500 hover:bg-brand-400 text-black text-sm font-bold py-2.5 transition mb-2">Jugar</span>
                                <span class="block w-full text-center rounded-xl bg-white/10 hover:bg-white/15 text-white text-xs font-semibold py-2 transition border border-white/10">Demo</span>
                            </div>

                            <div class="absolute bottom-0 left-0 right-0 p-3 sm:p-4 z-10">
                                <p class="text-xs text-slate-400 mb-0.5" x-text="j.provider"></p>
                                <p class="text-sm sm:text-base font-bold text-white leading-tight" x-text="j.name"></p>
                            </div>
                        </a>
                    </template>
                </div>

                <div x-show="filtered.length === 0" class="text-center py-20">
                    <div class="text-4xl mb-3">&#x1F50D;</div>
                    <p class="text-slate-500">No se encontraron juegos.</p>
                </div>
            </div>

        </div>
    </div>
</div>

@endsection
