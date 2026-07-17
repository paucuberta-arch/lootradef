@if($rickyeditCampaignEnabled ?? false)
<aside {{ $attributes->class('rounded-2xl border border-fuchsia-400/20 bg-gradient-to-br from-fuchsia-500/10 via-violet-500/5 to-cyan-400/10 p-5') }}>
    <p class="text-[10px] font-black uppercase tracking-[.22em] text-fuchsia-300">Reto de los 1.000</p>
    <h2 class="mt-2 text-xl font-black text-white">Supera {{ number_format($rickyeditCampaign['creator_score']) }} puntos</h2>
    <p class="mt-2 text-sm leading-relaxed text-slate-400">Empieza con 1.000 créditos demo y consigue el mejor saldo en 15 minutos.</p>
    @if($rickyeditActiveChallenge ?? null)
        <x-campaign.rickyedit.progress class="mt-4" />
    @else
        <a href="{{ auth()->check() ? route('rickyedit.intro') : route('registro') }}" data-campaign-click class="mt-4 inline-flex min-h-11 items-center rounded-xl bg-fuchsia-400 px-4 py-2.5 text-sm font-black text-slate-950">Ver el reto</a>
    @endif
</aside>
@endif
