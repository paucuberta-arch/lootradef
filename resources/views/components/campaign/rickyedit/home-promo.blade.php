@props(['variant' => 'hero'])

@if($rickyeditCampaignEnabled ?? false)
    @php
        $campaign = app(\App\Services\CampaignManager::class);
        $isCard = $variant === 'card';
    @endphp

    <a
        href="{{ route('rickyedit.landing') }}"
        data-campaign-click
        aria-label="Conoce el Reto de los 1.000 de RickyEdit y Lootra"
        {{ $attributes->class([
            'campaign-home-promo group relative isolate block overflow-hidden border border-fuchsia-400/30 bg-black focus-visible:outline-cyan-300',
            'campaign-home-promo--card rounded-2xl' => $isCard,
            'campaign-home-promo--hero rounded-2xl sm:rounded-3xl' => ! $isCard,
        ]) }}
    >
        @if($isCard)
            <img
                src="{{ $campaign->asset('home_card') }}"
                alt="RickyEdit reta a la comunidad de Lootra: 1.000 créditos demo, 15 minutos y una puntuación que superar"
                class="campaign-home-promo__image h-auto w-full"
                width="497"
                height="608"
                loading="lazy"
                decoding="async"
            >
        @else
            <picture>
                <source media="(min-width: 640px)" srcset="{{ $campaign->asset('home_hero') }}" width="1512" height="386">
                <img
                    src="{{ $campaign->asset('home_mobile') }}"
                    alt="RickyEdit x Lootra, El Reto de los 1.000: supera su puntuación en 15 minutos con créditos demo"
                    class="campaign-home-promo__image block h-auto w-full"
                    width="497"
                    height="608"
                    fetchpriority="high"
                    decoding="async"
                >
            </picture>
        @endif
        <span class="campaign-home-promo__wash pointer-events-none absolute inset-0" aria-hidden="true"></span>
        <span class="campaign-home-promo__spark campaign-home-promo__spark--one" aria-hidden="true"></span>
        <span class="campaign-home-promo__spark campaign-home-promo__spark--two" aria-hidden="true"></span>
        <span class="campaign-home-promo__spark campaign-home-promo__spark--three" aria-hidden="true"></span>
        <span class="absolute right-3 top-3 z-20 inline-flex items-center gap-2 rounded-full border border-white/20 bg-black/55 px-3 py-1.5 text-[10px] font-black uppercase tracking-[.16em] text-white shadow-xl backdrop-blur-md sm:right-5 sm:top-5 sm:text-xs">
            <span class="h-2 w-2 animate-pulse rounded-full bg-fuchsia-400 shadow-[0_0_12px_#e879f9]"></span> Reto activo
        </span>
        @unless($isCard)
            <span class="campaign-home-promo__cta absolute bottom-5 right-5 z-20 hidden items-center gap-2 rounded-xl border border-white/25 bg-white px-5 py-3 text-sm font-black text-slate-950 shadow-2xl shadow-fuchsia-950/50 sm:inline-flex">
                Entrar al reto <span aria-hidden="true">→</span>
            </span>
        @endunless
        <span class="pointer-events-none absolute inset-0 z-20 ring-1 ring-inset ring-white/15"></span>
    </a>
@endif
