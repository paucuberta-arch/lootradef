@extends('layouts.app')
@section('title', 'RickyEdit x Lootra — El Reto de los 1.000')
@section('contenido')
<div class="mx-auto max-w-[1400px] px-4 py-8 sm:px-6 lg:py-14">
    <section class="ricky-landing-hero relative isolate overflow-hidden rounded-[2rem] border border-fuchsia-400/25 bg-slate-950 px-5 py-10 sm:px-10 lg:min-h-[560px] lg:px-14 lg:py-16">
        <img src="{{ app(\App\Services\CampaignManager::class)->asset('challenge_hero') }}" alt="Trofeo dorado rodeado de energía violeta, fichas y elementos de casino del Reto de los 1.000" class="absolute inset-0 -z-20 h-full w-full object-cover object-[68%_center]" width="1920" height="768" fetchpriority="high" decoding="async">
        <div class="absolute inset-0 -z-10 bg-gradient-to-r from-[#05050d] via-[#070712]/95 to-violet-950/10"></div>
        <div class="absolute inset-0 -z-10 bg-gradient-to-t from-[#05050d]/90 via-transparent to-black/15"></div>
        <div class="ricky-landing-hero__beam absolute -right-28 top-1/2 -z-10 h-72 w-72 -translate-y-1/2 rounded-full bg-fuchsia-500/25 blur-[70px]" aria-hidden="true"></div>
        <div class="relative z-10 max-w-2xl">
            <img src="{{ app(\App\Services\CampaignManager::class)->asset('logo') }}" alt="RickyEdit x Lootra" class="h-14 w-auto" width="260" height="56">
            <p class="mt-8 inline-flex items-center gap-2 rounded-full border border-cyan-300/20 bg-cyan-300/10 px-3 py-1.5 text-xs font-black uppercase tracking-[.25em] text-cyan-200"><span class="h-2 w-2 rounded-full bg-cyan-300 shadow-[0_0_14px_#67e8f9]"></span> El Reto de los 1.000</p>
            <h1 class="mt-3 font-display text-4xl font-black tracking-tight text-white sm:text-6xl">¿Puedes superar a RickyEdit?</h1>
            <p class="mt-5 max-w-xl text-lg leading-relaxed text-slate-300">Empieza con {{ number_format($campaign['initial_balance'], 0, ',', '.') }} créditos demo. Tienes {{ $campaign['duration_minutes'] }} minutos para conseguir una puntuación superior.</p>
            <div class="mt-7 flex flex-col gap-3 sm:flex-row">
                @if(!$campaignEnabled)
                    <span class="rounded-xl border border-amber-400/30 bg-amber-400/10 px-5 py-3 font-bold text-amber-200">El reto no está activo en este momento</span>
                @elseif($challenge?->status === 'active')
                    <a href="{{ route('games.index') }}" class="cta-shine rounded-xl bg-white px-6 py-3.5 text-center font-black text-slate-950 shadow-xl shadow-fuchsia-500/20 transition hover:-translate-y-0.5">Seguir jugando</a>
                @elseif(auth()->check())
                    <a href="{{ route('rickyedit.intro') }}" data-campaign-click class="cta-shine rounded-xl bg-white px-6 py-3.5 text-center font-black text-slate-950 shadow-xl shadow-fuchsia-500/20 transition hover:-translate-y-0.5">Entrar al reto</a>
                @else
                    <a href="{{ route('registro') }}" data-campaign-click class="cta-shine rounded-xl bg-white px-6 py-3.5 text-center font-black text-slate-950 shadow-xl shadow-fuchsia-500/20 transition hover:-translate-y-0.5">Crear cuenta</a>
                    <a href="{{ route('rickyedit.intro') }}" class="rounded-xl border border-white/15 px-6 py-3.5 text-center font-bold text-white">Ya tengo cuenta</a>
                @endif
            </div>
            <p class="mt-6 text-sm font-bold text-amber-200 lg:max-w-lg">+18 · Créditos demo · Sin dinero real · Sin retiradas ni premios con valor económico.</p>
        </div>
        <div class="ricky-landing-cutout pointer-events-none relative z-0 mx-auto mt-7 h-80 w-full max-w-sm lg:absolute lg:bottom-0 lg:right-[1%] lg:mt-0 lg:h-[96%] lg:w-[40%] lg:max-w-[520px] xl:right-[3%]" aria-label="RickyEdit, protagonista del Reto de los 1.000" role="img">
            <span class="ricky-landing-cutout__aura absolute left-1/2 top-1/2 h-[70%] w-[78%] -translate-x-1/2 -translate-y-1/2 rounded-full bg-fuchsia-500/30 blur-[55px]" aria-hidden="true"></span>
            <span class="absolute bottom-0 left-1/2 h-12 w-[76%] -translate-x-1/2 rounded-[50%] bg-violet-400/30 blur-2xl" aria-hidden="true"></span>
            <img src="{{ app(\App\Services\CampaignManager::class)->asset('creator_portrait') }}" alt="" class="relative mx-auto h-full w-full object-contain object-bottom" width="447" height="559" fetchpriority="high" decoding="async">
        </div>
    </section>

    <section class="mt-8 grid gap-5 lg:grid-cols-3">
        <div class="rounded-2xl border border-white/10 bg-white/[.035] p-6"><p class="text-xs font-black uppercase tracking-wider text-fuchsia-300">Objetivo</p><b class="mt-2 block text-4xl">{{ number_format($campaign['creator_score']) }}</b><p class="mt-2 text-sm text-slate-400">Puntuación oficial de RickyEdit</p></div>
        <div class="rounded-2xl border border-white/10 bg-white/[.035] p-6"><p class="text-xs font-black uppercase tracking-wider text-cyan-300">Tu partida</p><b class="mt-2 block text-4xl">{{ str_pad((string) $campaign['duration_minutes'], 2, '0', STR_PAD_LEFT) }}:00</b><p class="mt-2 text-sm text-slate-400">Un único intento, medido en el servidor</p></div>
        <div class="rounded-2xl border border-white/10 bg-white/[.035] p-6"><p class="text-xs font-black uppercase tracking-wider text-emerald-300">Puntuación</p><b class="mt-2 block text-4xl">Saldo final</b><p class="mt-2 text-sm text-slate-400">Cada crédito entero al finalizar equivale a un punto</p></div>
    </section>

    <section class="mt-12 grid gap-8 lg:grid-cols-[1.2fr_.8fr]">
        <div>
            <p class="text-xs font-black uppercase tracking-[.2em] text-cyan-300">Juegos incluidos</p>
            <h2 class="mt-2 text-3xl font-black">Elige tu estrategia</h2>
            <div class="mt-5 grid grid-cols-2 gap-3 sm:grid-cols-3">
                @foreach(['Slots','Poker','Blackjack','Ruleta','Crash','Arcade'] as $game)
                    <div class="rounded-xl border border-white/10 bg-white/[.035] p-4 font-bold">{{ $game }}</div>
                @endforeach
            </div>
            <div class="mt-8 overflow-hidden rounded-2xl border border-white/10 bg-black/25">
                @if($campaign['youtube_url'])
                    <a href="{{ $campaign['youtube_url'] }}" rel="noopener noreferrer" target="_blank"><img src="{{ app(\App\Services\CampaignManager::class)->asset('video_poster') }}" alt="Ver vídeo oficial del reto" class="aspect-video w-full object-cover" width="1280" height="720"></a>
                @else
                    <img src="{{ app(\App\Services\CampaignManager::class)->asset('video_poster') }}" alt="Espacio reservado para el vídeo oficial" class="aspect-video w-full object-cover" width="1280" height="720">
                @endif
                <p class="p-4 text-xs text-slate-500">Espacio preparado para el vídeo oficial. Los recursos actuales son provisionales.</p>
            </div>
        </div>
        <x-campaign.rickyedit.leaderboard :leaders="$leaders" :limit="10" />
    </section>

    <section class="mt-12 grid gap-8 lg:grid-cols-2">
        <div><h2 class="text-2xl font-black">Preguntas frecuentes</h2><div class="mt-4 space-y-3">@foreach([
            ['¿Puedo reiniciar?', 'No. Cada cuenta dispone de una única participación válida.'],
            ['¿Son créditos reales?', 'No. Son créditos demo, separados del saldo habitual y sin valor económico.'],
            ['¿Cuándo empieza el tiempo?', 'Al confirmar “Iniciar reto”, nunca durante el registro.'],
            ['¿Cómo se calcula la puntuación?', 'Es el saldo final del reto truncado a créditos enteros.'],
        ] as [$q,$a])<details class="rounded-xl border border-white/10 bg-white/[.03] p-4"><summary class="cursor-pointer font-bold">{{ $q }}</summary><p class="mt-2 text-sm leading-relaxed text-slate-400">{{ $a }}</p></details>@endforeach</div></div>
        <div class="rounded-2xl border border-amber-400/20 bg-amber-400/5 p-6"><h2 class="text-2xl font-black">Avisos legales</h2><p class="mt-4 text-sm leading-relaxed text-slate-300">Campaña promocional de entretenimiento para mayores de 18 años. No implica apuestas con dinero real. Los créditos de campaña no son comprables, transferibles ni retirables y no generan premios con valor económico.</p><p class="mt-3 text-sm leading-relaxed text-slate-400">La participación puede descalificarse ante automatización, manipulación, abuso de peticiones o incumplimiento de las condiciones.</p></div>
    </section>
</div>
@endsection
