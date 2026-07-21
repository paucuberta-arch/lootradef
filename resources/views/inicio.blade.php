@extends('layouts.app')

@section('title', 'Lootra — Casino, retos y entretenimiento online')

@section('contenido')
<div class="home-page overflow-hidden">
    @if($rickyeditCampaignEnabled ?? false)
    <section class="mx-auto max-w-[1400px] px-4 py-8 sm:px-6 sm:py-12 lg:px-8">
        <x-campaign.rickyedit.home-promo />
    </section>
    @else
    <section class="home-hero relative isolate min-h-[calc(100svh-4rem)] overflow-hidden border-b border-white/5">
        <img src="{{ asset('images/lootra-hero.webp') }}" alt="Experiencia Lootra" class="absolute inset-0 h-full w-full object-cover object-center" fetchpriority="high" decoding="async">
        <div class="absolute inset-0 bg-[linear-gradient(90deg,#050711_5%,rgba(5,7,17,.94)_36%,rgba(5,7,17,.48)_67%,rgba(5,7,17,.82))]"></div>
        <div class="absolute inset-0 bg-[linear-gradient(0deg,#060812_0%,transparent_45%,rgba(6,8,18,.42)_100%)]"></div>
        <div class="home-hero__halo absolute left-[55%] top-[22%] h-80 w-80 rounded-full bg-cyan-400/20 blur-[110px]"></div>

        <div class="relative mx-auto flex min-h-[calc(100svh-4rem)] max-w-[1400px] items-center px-4 py-16 sm:px-6 lg:px-8">
            <div class="max-w-3xl">
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
    @endif

    <section id="descubre" class="mx-auto max-w-[1400px] px-4 py-16 sm:px-6 sm:py-24 lg:px-8">
        <div class="mb-9 flex flex-col justify-between gap-5 sm:flex-row sm:items-end">
            <div><p class="section-kicker">Ahora en Lootra</p><h2 class="mt-3 max-w-2xl text-3xl font-bold tracking-[-.04em] text-white sm:text-5xl">Una plataforma. Distintas formas de jugar.</h2></div>
            <a href="{{ route('games.index') }}" class="text-sm font-bold text-brand-300 hover:text-brand-200">Ver catálogo completo →</a>
        </div>

        <div class="experience-grid grid gap-4 lg:grid-cols-12">
            <a href="{{ route('games.index') }}" class="experience-card group relative min-h-[390px] overflow-hidden rounded-[2rem] border border-white/10 lg:col-span-7">
                @if($rickyeditCampaignEnabled ?? false)
                <div class="absolute inset-0 bg-[radial-gradient(circle_at_70%_20%,rgba(54,197,216,.2),transparent_35%),linear-gradient(135deg,#111827,#070914)]"></div>
                @else
                <img src="{{ asset('images/lootra-hero.webp') }}" alt="Casino Lootra" class="absolute inset-0 h-full w-full object-cover transition duration-1000 group-hover:scale-105" loading="lazy" decoding="async">
                @endif
                <div class="absolute inset-0 bg-gradient-to-t from-[#070914] via-[#070914]/45 to-transparent"></div>
                <div class="absolute inset-x-0 bottom-0 p-6 sm:p-9"><span class="section-kicker">Casino</span><h3 class="mt-2 text-3xl font-bold text-white sm:text-4xl">Todo el catálogo,<br>sin distracciones.</h3><p class="mt-3 max-w-md text-sm leading-6 text-slate-300">Slots, mesa, crash y originales en una experiencia rápida y ordenada.</p><span class="mt-6 inline-flex rounded-xl bg-white px-5 py-3 text-sm font-bold text-slate-950">Explorar juegos</span></div>
            </a>
            @if($rickyeditCampaignEnabled ?? false)
            <a href="{{ route('rickyedit.landing') }}" class="experience-card home-ricky-card group relative min-h-[390px] overflow-hidden rounded-[2rem] border border-fuchsia-300/15 lg:col-span-5">
                <img src="{{ asset('images/campaigns/rickyedit/challenge-hero-v2.webp') }}" alt="Reto RickyEdit" class="absolute inset-0 h-full w-full object-cover object-center transition duration-1000 group-hover:scale-105" loading="lazy" decoding="async">
                <div class="absolute inset-0 bg-gradient-to-t from-[#120719] via-[#120719]/35 to-fuchsia-900/10"></div>
                <div class="absolute inset-x-0 bottom-0 p-6 sm:p-9"><span class="section-kicker text-fuchsia-300">Reto de la comunidad</span><h3 class="mt-2 text-3xl font-bold text-white">RickyEdit × Lootra</h3><p class="mt-3 text-sm text-slate-300">15 minutos. 1.000 créditos. Una clasificación.</p><span class="mt-6 inline-flex rounded-xl bg-fuchsia-300 px-5 py-3 text-sm font-bold text-fuchsia-950">Aceptar el reto</span></div>
            </a>
            @else
            <a href="{{ route('games.index', ['cat' => 'arcade']) }}" class="experience-card group relative min-h-[390px] overflow-hidden rounded-[2rem] border border-violet-300/15 bg-gradient-to-br from-violet-950 to-[#111527] p-7 lg:col-span-5 sm:p-9">
                <div class="absolute -right-20 top-12 h-64 w-64 rounded-full bg-violet-400/15 blur-[65px]"></div><div class="absolute right-12 top-16 text-8xl opacity-80 transition duration-700 group-hover:-translate-y-3 group-hover:rotate-6">✦</div>
                <div class="absolute inset-x-0 bottom-0 p-6 sm:p-9"><span class="section-kicker text-violet-300">Lootra Originals</span><h3 class="mt-2 text-3xl font-bold text-white">Juegos que no encontrarás fuera.</h3><p class="mt-3 text-sm text-slate-300">Mecánicas rápidas creadas para Lootra.</p><span class="mt-6 inline-flex rounded-xl bg-violet-300 px-5 py-3 text-sm font-bold text-violet-950">Descubrir originales</span></div>
            </a>
            @endif
            <a href="{{ route('sports.index') }}" class="experience-card home-mini-card group relative min-h-64 overflow-hidden rounded-[2rem] border border-white/10 bg-gradient-to-br from-emerald-950 to-[#0d1721] p-7 lg:col-span-5 sm:p-9"><div class="home-orbit absolute -right-20 -top-20 h-64 w-64 rounded-full border border-emerald-300/20"></div><span class="section-kicker text-emerald-300">En directo</span><h3 class="mt-4 text-3xl font-bold text-white">Apuestas deportivas</h3><p class="mt-3 max-w-sm text-sm leading-6 text-slate-400">Partidos, cuotas y resultados en un mismo lugar.</p><span class="absolute bottom-8 right-8 text-3xl text-emerald-300 transition group-hover:translate-x-2">→</span></a>
            <a href="{{ route('cases.index') }}" class="experience-card home-mini-card group relative min-h-64 overflow-hidden rounded-[2rem] border border-white/10 bg-gradient-to-br from-amber-950/80 to-[#15120d] p-7 lg:col-span-7 sm:p-9"><div class="absolute -bottom-28 right-10 h-56 w-56 rotate-45 rounded-[3rem] border border-brand-300/15 bg-brand-300/5"></div><span class="section-kicker">Colecciona</span><h3 class="mt-4 text-3xl font-bold text-white">Cajas y recompensas</h3><p class="mt-3 max-w-sm text-sm leading-6 text-slate-400">Descubre premios, gestiona tu inventario y canjea desde tu perfil.</p><span class="absolute bottom-8 right-8 text-3xl text-brand-300 transition group-hover:translate-x-2">→</span></a>
        </div>
    </section>

    <section class="border-y border-white/5 bg-white/[.018] py-16 sm:py-24">
        <div class="mx-auto max-w-[1400px] px-4 sm:px-6 lg:px-8">
            <div class="mb-8 flex items-end justify-between gap-4"><div><p class="section-kicker">Lootra Originals y favoritos</p><h2 class="mt-3 text-3xl font-bold tracking-tight text-white sm:text-4xl">Empieza por aquí</h2></div><a href="{{ route('games.index') }}" class="hidden text-sm text-slate-400 hover:text-white sm:block">Todos los juegos →</a></div>
            <div class="home-game-rail grid grid-flow-col auto-cols-[72%] gap-4 overflow-x-auto pb-5 sm:auto-cols-[38%] lg:grid-flow-row lg:grid-cols-4 lg:overflow-visible">
                @foreach($featuredGames->take(4) as $game)
                    <a href="{{ $game['detail_url'] }}" class="game-card group aspect-[4/5] snap-start {{ $game['grad'] }}">
                        <img src="{{ $game['image'] }}" alt="{{ $game['name'] }}" class="absolute inset-0 h-full w-full object-cover" loading="lazy" decoding="async" onerror="this.src='{{ asset('images/game-fallback.svg') }}'">
                        <div class="game-overlay"></div>
                        <div class="absolute inset-x-0 bottom-0 z-10 p-5"><p class="text-xs text-slate-400">{{ $game['provider'] }}</p><h3 class="mt-1 text-lg font-bold text-white">{{ $game['name'] }}</h3><span class="mt-4 inline-flex text-xs font-bold text-brand-300">Jugar ahora →</span></div>
                    </a>
                @endforeach
            </div>
        </div>
    </section>

    <section class="mx-auto max-w-[1400px] px-4 py-16 sm:px-6 sm:py-24 lg:px-8">
        <div class="home-cta relative overflow-hidden rounded-[2rem] border border-white/10 px-6 py-14 text-center sm:px-12 sm:py-20">
            <div class="absolute inset-0 bg-[radial-gradient(circle_at_50%_120%,rgba(242,205,117,.25),transparent_52%)]"></div>
            <div class="relative"><p class="section-kicker">Tu próxima partida</p><h2 class="mx-auto mt-4 max-w-3xl text-4xl font-bold tracking-[-.05em] text-white sm:text-6xl">Entra. Elige. Juega.</h2><p class="mx-auto mt-5 max-w-xl text-slate-400">Sin ruido, sin esperas y con todo Lootra a un clic.</p><a href="{{ route('games.index') }}" class="mt-8 inline-flex rounded-2xl bg-white px-7 py-4 text-sm font-extrabold text-slate-950 transition hover:-translate-y-1">Descubrir el casino</a></div>
        </div>
    </section>
</div>
@endsection
