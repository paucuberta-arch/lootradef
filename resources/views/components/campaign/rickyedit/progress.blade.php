@if($rickyeditActiveChallenge ?? null)
<section
    {{ $attributes->class('rounded-xl border border-cyan-400/20 bg-black/25 p-4') }}
    x-data="rickyChallenge(@js([
        'seconds' => $rickyeditActiveChallenge->seconds_remaining,
        'balance' => $rickyeditActiveChallenge->current_balance,
        'games' => $rickyeditActiveChallenge->games_played,
        'duration' => ($rickyeditCampaign['duration_minutes'] ?? 15) * 60,
        'statusUrl' => route('rickyedit.status'),
    ]))"
    aria-live="polite"
>
    <div class="flex items-center justify-between gap-3">
        <div><p class="text-[10px] font-bold uppercase tracking-wider text-slate-500">Tiempo restante</p><b class="text-xl text-cyan-300" x-text="clock"></b></div>
        <div class="text-right"><p class="text-[10px] font-bold uppercase tracking-wider text-slate-500">Saldo del reto</p><b class="text-xl text-fuchsia-300" x-text="money(balance)"></b></div>
    </div>
    <div class="mt-3 h-1.5 overflow-hidden rounded-full bg-white/10"><div class="h-full bg-gradient-to-r from-fuchsia-400 to-cyan-300 transition-all" :style="`width:${percent}%`"></div></div>
    <p class="mt-2 text-[11px] text-slate-500"><span x-text="games"></span> partidas · tiempo calculado por el servidor</p>
</section>
@endif
