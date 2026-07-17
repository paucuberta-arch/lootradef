@props(['variant' => 'horizontal', 'closable' => false])
@if($rickyeditCampaignEnabled ?? false)
@php
    $isGlobal = $variant === 'global';
    $challenge = $rickyeditActiveChallenge ?? null;
    $cta = $challenge ? route('games.index') : (auth()->check() ? route('rickyedit.intro') : route('registro'));
@endphp
<aside
    @if($closable) x-data="{open: localStorage.getItem('rickyedit-banner-v1') !== 'closed'}" x-show="open" x-cloak @endif
    class="campaign-banner campaign-banner--{{ $variant }} relative overflow-hidden border-y border-fuchsia-400/20 bg-gradient-to-r from-violet-950/95 via-fuchsia-950/90 to-cyan-950/90"
    aria-label="Reto RickyEdit"
>
    <div class="mx-auto flex max-w-[1400px] flex-col gap-3 px-4 py-3 sm:flex-row sm:items-center sm:justify-between sm:px-6">
        <div class="flex min-w-0 items-center gap-3">
            <img src="{{ app(\App\Services\CampaignManager::class)->asset('badge') }}" alt="" class="h-10 w-10 shrink-0" width="40" height="40">
            <div class="min-w-0">
                <p class="text-[10px] font-black uppercase tracking-[.2em] text-cyan-300">RickyEdit x Lootra</p>
                <p class="font-display text-sm font-bold text-white sm:text-base">¿Puedes superar a RickyEdit? <span class="font-normal text-slate-300">{{ number_format($rickyeditCampaign['initial_balance'], 0, ',', '.') }} créditos · {{ $rickyeditCampaign['duration_minutes'] }} minutos</span></p>
            </div>
        </div>
        <div class="flex items-center gap-2 pl-13 sm:pl-0">
            <a href="{{ $cta }}" data-campaign-click class="min-h-10 rounded-xl bg-white px-4 py-2.5 text-xs font-black text-slate-950 hover:bg-cyan-100">
                {{ $challenge ? 'Seguir jugando' : 'Aceptar el reto' }}
            </a>
            @if($closable)
                <button @click="open=false; localStorage.setItem('rickyedit-banner-v1','closed')" class="grid min-h-10 min-w-10 place-items-center rounded-xl text-slate-400 hover:bg-white/10 hover:text-white" aria-label="Cerrar promoción">×</button>
            @endif
        </div>
    </div>
</aside>
@endif
