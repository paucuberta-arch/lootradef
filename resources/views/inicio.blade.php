@extends('layouts.app')

@section('title', 'Lootra — Casino, retos y entretenimiento online')

@section('preloads')
    @if($rickyeditCampaignEnabled ?? false)
        <link rel="preload" as="image" href="{{ app(\App\Services\CampaignManager::class)->asset('home_hero') }}" media="(min-width: 640px)" fetchpriority="high">
        <link rel="preload" as="image" href="{{ app(\App\Services\CampaignManager::class)->asset('home_mobile') }}" media="(max-width: 639px)" fetchpriority="high">
    @else
        <link rel="preload" as="image" href="{{ asset('images/lootra_visual_pack/01_heroes/hero_home_lootra_960x450.webp') }}" imagesrcset="{{ asset('images/lootra_visual_pack/01_heroes/hero_home_lootra_960x450.webp') }} 960w, {{ asset('images/lootra_visual_pack/01_heroes/hero_home_lootra_1920x900.webp') }} 1920w" imagesizes="100vw" fetchpriority="high">
    @endif
@endsection

@section('contenido')
<div class="home-page overflow-hidden">
    @if($rickyeditCampaignEnabled ?? false)
    <section class="home-challenge-lead mx-auto max-w-[1400px] px-4 py-4 sm:px-6 sm:py-6 lg:px-8" aria-label="Reto destacado de RickyEdit">
        <x-campaign.rickyedit.home-promo />
    </section>
    @endif

    <section class="home-hero relative isolate min-h-[calc(100svh-4rem)] overflow-hidden border-b border-white/5">
        <img src="{{ asset('images/lootra_visual_pack/01_heroes/hero_home_lootra_960x450.webp') }}" srcset="{{ asset('images/lootra_visual_pack/01_heroes/hero_home_lootra_960x450.webp') }} 960w, {{ asset('images/lootra_visual_pack/01_heroes/hero_home_lootra_1920x900.webp') }} 1920w" sizes="100vw" width="1920" height="900" alt="Universo visual de Lootra" class="absolute inset-0 h-full w-full object-cover object-center" @if($rickyeditCampaignEnabled ?? false) loading="lazy" fetchpriority="low" @else fetchpriority="high" @endif decoding="async">
        <div class="absolute inset-0 bg-[linear-gradient(90deg,#050711_5%,rgba(5,7,17,.94)_36%,rgba(5,7,17,.48)_67%,rgba(5,7,17,.82))]"></div>
        <div class="absolute inset-0 bg-[linear-gradient(0deg,#060812_0%,transparent_45%,rgba(6,8,18,.42)_100%)]"></div>
        <div class="home-hero__halo absolute left-[55%] top-[22%] h-80 w-80 rounded-full bg-cyan-400/20 blur-[110px]"></div>

        <div class="relative mx-auto flex min-h-[calc(100svh-4rem)] max-w-[1400px] items-center px-4 py-16 sm:px-6 lg:px-8">
            <div class="max-w-3xl">
                <img src="{{ asset('images/logo/lootra-mark-transparent.png') }}" alt="" class="mb-5 h-16 w-16 object-contain drop-shadow-[0_0_28px_rgba(242,205,117,.28)] sm:h-20 sm:w-20" width="1254" height="1254" fetchpriority="high" decoding="async" aria-hidden="true">
                <div class="mb-6 inline-flex items-center gap-3 rounded-full border border-white/10 bg-black/25 px-3 py-2 text-[11px] font-bold uppercase tracking-[.2em] text-slate-200 backdrop-blur-xl">
                    <span class="relative flex h-2 w-2"><span class="absolute inline-flex h-full w-full animate-ping rounded-full bg-emerald-300 opacity-60"></span><span class="relative h-2 w-2 rounded-full bg-emerald-300"></span></span>
                    Entretenimiento en tiempo real
                </div>
                <p class="mb-4 font-display text-sm font-bold uppercase tracking-[.28em] text-brand-300">Bienvenido a Lootra</p>
                <h1 class="max-w-3xl font-display text-[clamp(3rem,8vw,7.5rem)] font-bold leading-[.84] tracking-[-.075em] text-white">
                    Juega a tu <span class="home-hero__accent">manera.</span>
                </h1>
                <p class="mt-7 max-w-xl text-base leading-7 text-slate-300 sm:text-lg">Casino, originales, apuestas y retos que cambian la partida. Una plataforma rápida, clara y diseñada alrededor de cada jugada.</p>
                <div class="mt-9 flex flex-col gap-3 min-[420px]:flex-row">
                    <a href="{{ route('games.index') }}" class="cta-shine inline-flex min-h-13 items-center justify-center rounded-2xl bg-brand-300 px-7 py-3.5 text-sm font-extrabold text-[#101218] shadow-2xl shadow-brand-500/25 transition hover:-translate-y-1 hover:bg-brand-200">Entrar al casino <span class="ml-3">→</span></a>
                    @guest
                        <a href="{{ route('registro') }}" class="inline-flex min-h-13 items-center justify-center rounded-2xl border border-white/15 bg-white/5 px-7 py-3.5 text-sm font-bold text-white backdrop-blur-xl transition hover:-translate-y-1 hover:bg-white/10">Crear cuenta</a>
                    @else
                        <a href="{{ route('rickyedit.landing') }}" class="inline-flex min-h-13 items-center justify-center rounded-2xl border border-white/15 bg-white/5 px-7 py-3.5 text-sm font-bold text-white backdrop-blur-xl transition hover:-translate-y-1 hover:bg-white/10">Ver reto actual</a>
                    @endguest
                </div>
                <dl class="mt-12 grid max-w-xl grid-cols-3 gap-3 border-t border-white/10 pt-6">
                    <div><dt class="text-[10px] uppercase tracking-widest text-slate-500">Catálogo</dt><dd class="mt-1 font-display text-xl font-bold text-white">{{ $gameCount }}+</dd></div>
                    <div><dt class="text-[10px] uppercase tracking-widest text-slate-500">Acceso</dt><dd class="mt-1 font-display text-xl font-bold text-white">24/7</dd></div>
                    <div><dt class="text-[10px] uppercase tracking-widest text-slate-500">Experiencia</dt><dd class="mt-1 font-display text-xl font-bold text-white">Instant</dd></div>
                </dl>
            </div>
        </div>
        <a href="#descubre" class="absolute bottom-6 left-1/2 hidden -translate-x-1/2 flex-col items-center gap-2 text-[9px] font-bold uppercase tracking-[.3em] text-slate-500 md:flex"><span>Descubre</span><span class="home-scroll-line"></span></a>
    </section>

    @php
        $homePromos = [
                ['image' => 'images/lootra_visual_pack/02_promo_banners/banner_slots_nuevos_1920x600.webp', 'kicker' => 'Nuevos mundos', 'title' => 'Slots recién llegadas', 'url' => route('games.index', ['cat' => 'slots'])],
                ['image' => 'images/lootra_visual_pack/02_promo_banners/banner_originals_neon_1920x600.webp', 'kicker' => 'Solo en Lootra', 'title' => 'Originals: juega diferente', 'url' => route('games.index', ['cat' => 'arcade'])],
                ['image' => 'images/lootra_visual_pack/02_promo_banners/banner_casino_en_vivo_1920x600.webp', 'kicker' => 'Mesas abiertas', 'title' => 'El directo no se detiene', 'url' => route('games.index', ['cat' => 'live'])],
        ];
    @endphp
    <section aria-label="Experiencias destacadas" class="mx-auto max-w-[1400px] px-4 pt-8 sm:px-6 sm:pt-12 lg:px-8">
        <div
            id="home-promotions"
            class="home-promo-carousel group relative isolate overflow-hidden rounded-2xl border border-white/10 bg-[#0b1020] shadow-2xl shadow-black/25 sm:rounded-3xl"
            x-data="promoCarousel({ count: {{ count($homePromos) }}, interval: 6500 })"
            @mouseenter="pauseInteraction()"
            @mouseleave="resumeInteraction()"
            @focusin="pauseInteraction()"
            @focusout="if (!$root.contains($event.relatedTarget)) resumeInteraction()"
            @keydown.left.prevent="previous()"
            @keydown.right.prevent="next()"
            role="region"
            aria-roledescription="carrusel"
            aria-label="Promociones de Lootra"
        >
            @foreach($homePromos as $promo)
                @php
                    $imageSmall = str_replace('_1920x600.webp', '_960x300.webp', $promo['image']);
                @endphp
                <a
                    href="{{ $promo['url'] }}"
                    class="home-promo-slide visual-sheen absolute inset-0 overflow-hidden"
                    x-show="active === {{ $loop->index }}"
                    x-transition:enter="transition-opacity duration-700 ease-out"
                    x-transition:enter-start="opacity-0"
                    x-transition:enter-end="opacity-100"
                    x-transition:leave="transition-opacity duration-500 ease-in"
                    x-transition:leave-start="opacity-100"
                    x-transition:leave-end="opacity-0"
                    :aria-hidden="(active !== {{ $loop->index }}).toString()"
                    :tabindex="active === {{ $loop->index }} ? 0 : -1"
                    role="group"
                    aria-roledescription="diapositiva"
                    aria-label="{{ $loop->iteration }} de {{ $loop->count }}: {{ $promo['title'] }}"
                    @if(!$loop->first) x-cloak @endif
                >
                    <img
                        @if($loop->first)
                            src="{{ asset($imageSmall) }}"
                            srcset="{{ asset($imageSmall) }} 960w, {{ asset($promo['image']) }} 1920w"
                            sizes="(min-width: 1400px) 1344px, calc(100vw - 2rem)"
                        @else
                            data-src="{{ asset($imageSmall) }}"
                            data-srcset="{{ asset($imageSmall) }} 960w, {{ asset($promo['image']) }} 1920w"
                            data-sizes="(min-width: 1400px) 1344px, calc(100vw - 2rem)"
                        @endif
                        data-promo-image="{{ $loop->index }}"
                        alt=""
                        width="1920"
                        height="600"
                        class="absolute inset-0 h-full w-full object-cover transition duration-700 group-hover:scale-[1.025]"
                        loading="lazy"
                        decoding="async"
                    >
                    <span class="absolute inset-0 bg-gradient-to-r from-[#070a14]/95 via-[#070a14]/55 to-transparent"></span>
                    <span class="absolute inset-x-0 bottom-0 z-10 p-6 sm:p-9 lg:p-11">
                        <span class="world-kicker">{{ $promo['kicker'] }}</span>
                        <span class="mt-3 block max-w-xl font-display text-2xl font-bold leading-tight text-white sm:text-4xl">{{ $promo['title'] }}</span>
                        <span class="mt-5 inline-flex items-center gap-2 text-sm font-bold text-white/85">Descubrir <span aria-hidden="true">→</span></span>
                    </span>
                </a>
            @endforeach

            <button type="button" @click="previous()" class="home-promo-carousel__arrow left-3 sm:left-5" aria-controls="home-promotions" aria-label="Ver promoción anterior">
                <svg viewBox="0 0 24 24" aria-hidden="true"><path d="m15 18-6-6 6-6"/></svg>
            </button>
            <button type="button" @click="next()" class="home-promo-carousel__arrow right-3 sm:right-5" aria-controls="home-promotions" aria-label="Ver promoción siguiente">
                <svg viewBox="0 0 24 24" aria-hidden="true"><path d="m9 18 6-6-6-6"/></svg>
            </button>

            <div class="absolute bottom-4 left-1/2 z-20 flex -translate-x-1/2 items-center gap-2 rounded-full border border-white/10 bg-black/35 px-3 py-2 backdrop-blur-md sm:bottom-5">
                @foreach($homePromos as $promo)
                    <button type="button" @click="go({{ $loop->index }})" class="h-2 rounded-full transition-all duration-300" :class="active === {{ $loop->index }} ? 'w-7 bg-white' : 'w-2 bg-white/40 hover:bg-white/70'" :aria-current="active === {{ $loop->index }} ? 'true' : 'false'" aria-label="Mostrar promoción {{ $loop->iteration }}"></button>
                @endforeach
            </div>
            <button type="button" @click="toggleAutoplay()" :disabled="reducedMotion" class="absolute bottom-4 right-4 z-20 grid h-10 w-10 place-items-center rounded-full border border-white/15 bg-black/40 text-white backdrop-blur-md transition hover:bg-black/65 disabled:cursor-default disabled:opacity-60 sm:bottom-5 sm:right-5" :aria-label="reducedMotion ? 'Rotación automática desactivada' : (userPaused ? 'Reanudar rotación automática' : 'Pausar rotación automática')">
                <svg x-show="autoplaying" class="h-4 w-4 fill-current" viewBox="0 0 24 24" aria-hidden="true"><path d="M7 5h3v14H7zm7 0h3v14h-3z"/></svg>
                <svg x-show="!autoplaying" x-cloak class="h-4 w-4 fill-current" viewBox="0 0 24 24" aria-hidden="true"><path d="m8 5 11 7-11 7z"/></svg>
            </button>
            <p class="sr-only" :aria-live="autoplaying ? 'off' : 'polite'" x-text="status"></p>
        </div>
    </section>

    <section id="descubre" class="home-deferred-section mx-auto max-w-[1400px] px-4 py-16 sm:px-6 sm:py-24 lg:px-8">
        <div class="mb-9 flex flex-col justify-between gap-5 sm:flex-row sm:items-end">
            <div><p class="section-kicker">Ahora en Lootra</p><h2 class="mt-3 max-w-2xl text-3xl font-bold tracking-[-.04em] text-white sm:text-5xl">Una plataforma. Distintas formas de jugar.</h2></div>
            <a href="{{ route('games.index') }}" class="text-sm font-bold text-brand-300 hover:text-brand-200">Ver catálogo completo →</a>
        </div>

        <div class="experience-grid grid gap-4 lg:grid-cols-12">
            <a href="{{ route('games.index') }}" class="experience-card group relative min-h-[390px] overflow-hidden rounded-[2rem] border border-white/10 lg:col-span-7">
                <img src="{{ asset('images/lootra_visual_pack/01_heroes/hero_casino_roulette_960x450.webp') }}" srcset="{{ asset('images/lootra_visual_pack/01_heroes/hero_casino_roulette_960x450.webp') }} 960w, {{ asset('images/lootra_visual_pack/01_heroes/hero_casino_roulette_1920x900.webp') }} 1920w" sizes="(min-width: 1400px) 780px, (min-width: 1024px) 58vw, calc(100vw - 2rem)" width="1920" height="900" alt="Casino Lootra" class="absolute inset-0 h-full w-full object-cover transition duration-1000 group-hover:scale-105" loading="lazy" decoding="async">
                <div class="absolute inset-0 bg-gradient-to-t from-[#070914] via-[#070914]/45 to-transparent"></div>
                <div class="absolute inset-x-0 bottom-0 p-6 sm:p-9"><span class="section-kicker">Casino</span><h3 class="mt-2 text-3xl font-bold text-white sm:text-4xl">Todo el catálogo,<br>sin distracciones.</h3><p class="mt-3 max-w-md text-sm leading-6 text-slate-300">Slots, mesa, crash y originales en una experiencia rápida y ordenada.</p><span class="mt-6 inline-flex rounded-xl bg-white px-5 py-3 text-sm font-bold text-slate-950">Explorar juegos</span></div>
            </a>
            @if($rickyeditCampaignEnabled ?? false)
            <a href="{{ route('rickyedit.landing') }}" class="experience-card home-ricky-card group relative min-h-[390px] overflow-hidden rounded-[2rem] border border-fuchsia-300/15 lg:col-span-5">
                <img src="{{ asset('images/campaigns/rickyedit/challenge-hero-v2.webp') }}" alt="Reto RickyEdit" class="absolute inset-0 h-full w-full object-cover object-center transition duration-1000 group-hover:scale-105" width="1920" height="768" loading="lazy" decoding="async">
                <div class="absolute inset-0 bg-gradient-to-t from-[#120719] via-[#120719]/35 to-fuchsia-900/10"></div>
                <div class="absolute inset-x-0 bottom-0 p-6 sm:p-9"><span class="section-kicker text-fuchsia-300">Reto de la comunidad</span><h3 class="mt-2 text-3xl font-bold text-white">RickyEdit × Lootra</h3><p class="mt-3 text-sm text-slate-300">15 minutos. 1.000 créditos. Una clasificación.</p><span class="mt-6 inline-flex rounded-xl bg-fuchsia-300 px-5 py-3 text-sm font-bold text-fuchsia-950">Aceptar el reto</span></div>
            </a>
            @else
            <a href="{{ route('games.index', ['cat' => 'arcade']) }}" class="experience-card group relative min-h-[390px] overflow-hidden rounded-[2rem] border border-violet-300/15 bg-gradient-to-br from-violet-950 to-[#111527] p-7 lg:col-span-5 sm:p-9">
                <img src="{{ asset('images/lootra_visual_pack/01_heroes/hero_originals_quantum_1920x900.webp') }}" alt="Lootra Originals" class="absolute inset-0 h-full w-full object-cover transition duration-1000 group-hover:scale-105" width="1920" height="900" loading="lazy" decoding="async"><div class="absolute inset-0 bg-gradient-to-t from-[#100b28] via-[#100b28]/55 to-transparent"></div>
                <div class="absolute inset-x-0 bottom-0 p-6 sm:p-9"><span class="section-kicker text-violet-300">Lootra Originals</span><h3 class="mt-2 text-3xl font-bold text-white">Juegos que no encontrarás fuera.</h3><p class="mt-3 text-sm text-slate-300">Mecánicas rápidas creadas para Lootra.</p><span class="mt-6 inline-flex rounded-xl bg-violet-300 px-5 py-3 text-sm font-bold text-violet-950">Descubrir originales</span></div>
            </a>
            @endif
            <a href="{{ route('sports.index') }}" class="experience-card home-mini-card group relative min-h-64 overflow-hidden rounded-[2rem] border border-white/10 p-7 lg:col-span-5 sm:p-9"><img src="{{ asset('images/lootra_visual_pack/04_backgrounds/bg_emerald_forest_1920x1080.webp') }}" alt="" class="absolute inset-0 h-full w-full object-cover opacity-55 transition duration-1000 group-hover:scale-105" width="1920" height="1080" loading="lazy" decoding="async"><div class="absolute inset-0 bg-gradient-to-r from-[#071712] via-[#071712]/80 to-transparent"></div><div class="home-orbit absolute -right-20 -top-20 h-64 w-64 rounded-full border border-emerald-300/20"></div><div class="relative"><span class="section-kicker text-emerald-300">En directo</span><h3 class="mt-4 text-3xl font-bold text-white">Apuestas deportivas</h3><p class="mt-3 max-w-sm text-sm leading-6 text-slate-300">Partidos, cuotas y resultados en un mismo lugar.</p></div><span class="absolute bottom-8 right-8 text-3xl text-emerald-300 transition group-hover:translate-x-2">→</span></a>
            <a href="{{ route('cases.index') }}" class="experience-card home-mini-card group relative min-h-64 overflow-hidden rounded-[2rem] border border-white/10 p-7 lg:col-span-7 sm:p-9"><img src="{{ asset('images/lootra_visual_pack/02_promo_banners/banner_jackpots_960x300.webp') }}" srcset="{{ asset('images/lootra_visual_pack/02_promo_banners/banner_jackpots_960x300.webp') }} 960w, {{ asset('images/lootra_visual_pack/02_promo_banners/banner_jackpots_1920x600.webp') }} 1920w" sizes="(min-width: 1400px) 780px, (min-width: 1024px) 58vw, calc(100vw - 2rem)" width="1920" height="600" alt="" class="absolute inset-0 h-full w-full object-cover opacity-65 transition duration-1000 group-hover:scale-105" loading="lazy" decoding="async"><div class="absolute inset-0 bg-gradient-to-r from-[#17100a] via-[#17100a]/82 to-transparent"></div><div class="relative"><span class="section-kicker">Colecciona</span><h3 class="mt-4 text-3xl font-bold text-white">Cajas y recompensas</h3><p class="mt-3 max-w-sm text-sm leading-6 text-slate-300">Descubre premios, gestiona tu inventario y canjea desde tu perfil.</p></div><span class="absolute bottom-8 right-8 text-3xl text-brand-300 transition group-hover:translate-x-2">→</span></a>
        </div>
    </section>

    <section class="home-deferred-section border-y border-white/5 bg-white/[.018] py-16 sm:py-24">
        <div class="mx-auto max-w-[1400px] px-4 sm:px-6 lg:px-8">
            <div class="mb-8 flex items-end justify-between gap-4"><div><p class="section-kicker">Lootra Originals y favoritos</p><h2 class="mt-3 text-3xl font-bold tracking-tight text-white sm:text-4xl">Empieza por aquí</h2></div><a href="{{ route('games.index') }}" class="hidden text-sm text-slate-400 hover:text-white sm:block">Todos los juegos →</a></div>
            <div class="home-game-rail grid grid-flow-col auto-cols-[72%] gap-4 overflow-x-auto pb-5 sm:auto-cols-[38%] lg:grid-flow-row lg:grid-cols-4 lg:overflow-visible">
                @foreach($featuredGames->take(4) as $game)
                    <a href="{{ $game['detail_url'] }}" class="game-card group aspect-[4/5] snap-start {{ $game['grad'] }}">
                        <img src="{{ $game['image'] }}" alt="{{ $game['name'] }}" class="absolute inset-0 h-full w-full object-cover" width="800" height="1000" loading="lazy" decoding="async" onerror="this.src='{{ asset('images/game-fallback.svg') }}'">
                        <div class="game-overlay"></div>
                        <div class="absolute inset-x-0 bottom-0 z-10 p-5"><p class="text-xs text-slate-400">{{ $game['provider'] }}</p><h3 class="mt-1 text-lg font-bold text-white">{{ $game['name'] }}</h3><span class="mt-4 inline-flex text-xs font-bold text-brand-300">Jugar ahora →</span></div>
                    </a>
                @endforeach
            </div>
        </div>
    </section>

    <section class="home-deferred-section mx-auto max-w-[1400px] px-4 py-16 sm:px-6 sm:py-24 lg:px-8">
        <div class="home-cta relative overflow-hidden rounded-[2rem] border border-white/10 px-6 py-14 text-center sm:px-12 sm:py-20">
            <div class="absolute inset-0 bg-[radial-gradient(circle_at_50%_120%,rgba(242,205,117,.25),transparent_52%)]"></div>
            <div class="relative"><p class="section-kicker">Tu próxima partida</p><h2 class="mx-auto mt-4 max-w-3xl text-4xl font-bold tracking-[-.05em] text-white sm:text-6xl">Entra. Elige. Juega.</h2><p class="mx-auto mt-5 max-w-xl text-slate-400">Sin ruido, sin esperas y con todo Lootra a un clic.</p><a href="{{ route('games.index') }}" class="mt-8 inline-flex rounded-2xl bg-white px-7 py-4 text-sm font-extrabold text-slate-950 transition hover:-translate-y-1">Descubrir el casino</a></div>
        </div>
    </section>
</div>
@endsection
