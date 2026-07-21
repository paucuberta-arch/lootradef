@if(($rickyeditCampaignEnabled ?? false) && !request()->routeIs('rickyedit.*'))
<a href="{{ ($rickyeditActiveChallenge ?? null) ? route('rickyedit.ranking') : route('rickyedit.landing') }}" data-campaign-click class="campaign-floating-button fixed bottom-4 right-4 z-40 flex min-h-12 items-center gap-2 rounded-full border border-fuchsia-300/30 bg-slate-950/95 px-4 py-3 text-xs font-black text-white shadow-2xl shadow-fuchsia-500/20 backdrop-blur sm:bottom-6 sm:right-6" aria-label="Abrir Reto RickyEdit">
    <img src="{{ app(\App\Services\CampaignManager::class)->asset('badge') }}" alt="" class="h-6 w-6" width="24" height="24"> Reto RickyEdit
</a>
@endif
