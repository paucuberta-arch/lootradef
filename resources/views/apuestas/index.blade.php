@extends('layouts.app')

@section('title', 'Sports Live — Lootra')

@section('styles')
<style>
    @keyframes live-ring { 0%,100%{box-shadow:0 0 0 0 rgba(244,63,94,.45)} 50%{box-shadow:0 0 0 9px rgba(244,63,94,0)} }
    @keyframes event-in { from{opacity:0;transform:translateX(-12px)} to{opacity:1;transform:none} }
    .live-dot{animation:live-ring 1.7s infinite}.event-in{animation:event-in .4s ease-out}.sports-grid{background-image:linear-gradient(rgba(255,255,255,.025) 1px,transparent 1px),linear-gradient(90deg,rgba(255,255,255,.025) 1px,transparent 1px);background-size:28px 28px}
    .odd{transition:transform .2s ease,border-color .2s ease,background .2s ease}.odd:hover{transform:translateY(-3px)}
</style>
@endsection

@section('contenido')
<div class="sports-grid min-h-screen" x-data="sportsbook()" x-init="start()">
    <section class="relative overflow-hidden border-b border-white/10">
        <img src="{{ asset('images/lootra_visual_pack/13_sports/sports_hero_960x540.webp') }}" srcset="{{ asset('images/lootra_visual_pack/13_sports/sports_hero_960x540.webp') }} 960w, {{ asset('images/lootra_visual_pack/13_sports/sports_hero_1672x941.webp') }} 1672w" sizes="100vw" width="1672" height="941" class="absolute inset-0 h-full w-full object-cover object-center" alt="Futbolista ejecutando un disparo en el estadio digital de Lootra" fetchpriority="high" decoding="async">
        <div class="absolute inset-0 bg-gradient-to-r from-[#061027]/98 via-[#07152d]/88 to-[#071128]/20"></div>
        <div class="absolute inset-0 bg-[linear-gradient(115deg,transparent_55%,rgba(34,211,238,.12)_55.2%,transparent_55.5%)]"></div>
        <div class="relative mx-auto max-w-[1450px] px-4 py-12 sm:px-5 sm:py-20">
            <div class="mb-5 inline-flex items-center gap-3 rounded-full border border-rose-400/30 bg-rose-500/10 px-4 py-2 text-xs font-black uppercase tracking-[.22em] text-rose-300">
                <span class="live-dot h-2.5 w-2.5 rounded-full bg-rose-400"></span> Simulación en directo
            </div>
            <h1 class="font-display text-3xl font-bold tracking-tight min-[420px]:text-4xl sm:text-6xl">El partido cambia.<br><span class="bg-gradient-to-r from-cyan-300 via-emerald-300 to-amber-300 bg-clip-text text-transparent">Tu apuesta también se vive.</span></h1>
            <p class="mt-5 max-w-2xl text-lg text-slate-300">Sigue cada minuto, gol y ocasión. Los encuentros avanzan en tiempo real acelerado y los premios se ingresan automáticamente al finalizar.</p>
            <div class="mt-8 flex flex-wrap gap-3">
                <div class="rounded-2xl border border-white/10 bg-white/5 px-5 py-3 backdrop-blur"><b class="text-2xl text-rose-300" x-text="liveCount"></b><span class="ml-2 text-sm text-slate-400">en vivo</span></div>
                <div class="rounded-2xl border border-white/10 bg-white/5 px-5 py-3 backdrop-blur"><b class="text-2xl text-cyan-300" x-text="openCount"></b><span class="ml-2 text-sm text-slate-400">mercados abiertos</span></div>
                @auth<div class="rounded-2xl border border-emerald-400/20 bg-emerald-400/10 px-5 py-3 backdrop-blur"><b class="text-2xl text-emerald-300" x-text="money($store.wallet.saldo)"></b><span class="ml-2 text-sm text-slate-300">saldo</span></div>@endauth
            </div>
        </div>
    </section>

    <div class="relative mx-auto grid max-w-[1450px] gap-6 overflow-hidden px-4 py-7 sm:px-5 sm:py-9 lg:grid-cols-[minmax(0,1fr)_minmax(300px,380px)] lg:gap-7">
        <img src="{{ asset('images/lootra_visual_pack/13_sports/sports_stadium_1200x675.webp') }}" alt="" class="pointer-events-none absolute inset-x-0 top-0 h-[42rem] w-full object-cover opacity-[.07] [mask-image:linear-gradient(to_bottom,black,transparent)]" width="1200" height="675" loading="lazy" decoding="async" aria-hidden="true">
        <main class="relative">
            <div class="mb-6 flex flex-col items-start justify-between gap-4 sm:flex-row sm:flex-wrap sm:items-center">
                <div class="flex max-w-full overflow-x-auto rounded-xl border border-white/10 bg-white/5 p-1">
                    <template x-for="item in [{id:'all',label:'Todos'},{id:'en_vivo',label:'En vivo'},{id:'programado',label:'Próximos'},{id:'finalizado',label:'Finalizados'}]">
                        <button @click="filter=item.id" class="shrink-0 rounded-lg px-3 py-2 text-sm font-bold transition" :class="filter===item.id?'bg-cyan-400 text-slate-950':'text-slate-400 hover:text-white'" x-text="item.label"></button>
                    </template>
                </div>
                <div class="flex items-center gap-2 text-xs text-slate-500"><span class="h-2 w-2 rounded-full bg-emerald-400"></span> Actualización automática cada 3 segundos</div>
            </div>

            <div class="space-y-5">
                <template x-for="match in filteredMatches" :key="match.id">
                    <article class="group relative overflow-hidden rounded-3xl border bg-[#0d1427]/95 shadow-2xl shadow-black/20 transition hover:border-cyan-400/30" :class="match.status==='en_vivo'?'border-rose-400/25':'border-white/10'">
                        <div class="absolute inset-x-0 top-0 h-1 bg-white/5"><div class="h-full bg-gradient-to-r from-cyan-400 to-fuchsia-500 transition-all duration-1000" :style="`width:${match.status==='programado'?0:match.minute/90*100}%`"></div></div>
                        <div class="p-4 sm:p-7">
                            <div class="mb-6 flex items-center justify-between gap-3">
                                <div><p class="text-xs font-black uppercase tracking-[.18em] text-cyan-300" x-text="match.league"></p><p class="mt-1 text-xs text-slate-500" x-text="match.status==='programado'?'Comienza '+match.starts_label:'Fútbol · 1X2'"></p></div>
                                <span class="rounded-full px-3 py-1.5 text-xs font-black uppercase" :class="statusClass(match.status)" x-text="statusLabel(match)"></span>
                            </div>
                            <div class="grid grid-cols-[minmax(0,1fr)_auto_minmax(0,1fr)] items-center gap-2 sm:gap-8">
                                <div class="min-w-0 text-center"><div class="mx-auto grid h-12 w-12 place-items-center rounded-xl bg-gradient-to-br from-cyan-400 to-blue-700 font-display text-base font-black shadow-lg shadow-cyan-500/20 sm:h-14 sm:w-14 sm:rounded-2xl sm:text-lg" x-text="match.home_short"></div><h2 class="mt-3 break-words font-display text-xs font-bold sm:text-lg" x-text="match.home"></h2></div>
                                <div class="text-center"><div class="font-display text-2xl font-black min-[420px]:text-3xl sm:text-5xl" x-text="match.status==='programado'?'VS':match.home_score+' : '+match.away_score"></div><p class="mt-2 text-[10px] font-bold text-slate-500 sm:text-xs" x-text="match.status==='en_vivo'?match.minute+' / 90 min':(match.status==='finalizado'?'Resultado final':'Próximamente')"></p></div>
                                <div class="min-w-0 text-center"><div class="mx-auto grid h-12 w-12 place-items-center rounded-xl bg-gradient-to-br from-fuchsia-400 to-rose-700 font-display text-base font-black shadow-lg shadow-fuchsia-500/20 sm:h-14 sm:w-14 sm:rounded-2xl sm:text-lg" x-text="match.away_short"></div><h2 class="mt-3 break-words font-display text-xs font-bold sm:text-lg" x-text="match.away"></h2></div>
                            </div>

                            <div class="mt-7 grid grid-cols-3 gap-2 sm:gap-3">
                                <template x-for="choice in choices(match)" :key="choice.key">
                                    <button @click="select(match,choice)" :disabled="!match.betting_open" class="odd rounded-xl border px-2 py-3 disabled:cursor-not-allowed disabled:opacity-40" :class="selected?.match.id===match.id&&selected?.choice.key===choice.key?'border-emerald-300 bg-emerald-400/20':'border-white/10 bg-white/5 hover:border-cyan-300/50 hover:bg-cyan-400/10'">
                                        <span class="block truncate text-[11px] font-semibold text-slate-400" x-text="choice.label"></span><b class="mt-1 block text-base text-white" x-text="Number(choice.odd).toFixed(2)"></b>
                                    </button>
                                </template>
                            </div>

                            <div x-show="match.status==='en_vivo'&&match.events.length" class="mt-5 border-t border-white/10 pt-4">
                                <div class="flex gap-3 overflow-x-auto pb-1">
                                    <template x-for="event in match.events.slice(0,3)" :key="event.minute+'-'+event.text">
                                        <div class="event-in min-w-fit rounded-xl border border-white/10 bg-black/20 px-3 py-2 text-xs"><b class="mr-2" :class="event.type==='goal'?'text-amber-300':'text-cyan-300'" x-text="event.type==='goal'?'⚽ '+event.minute+'′':'◉ '+event.minute+'′'"></b><span class="text-slate-300" x-text="event.text"></span></div>
                                    </template>
                                </div>
                            </div>
                        </div>
                    </article>
                </template>
                <div x-show="filteredMatches.length===0" class="rounded-3xl border border-dashed border-white/15 p-16 text-center text-slate-500">No hay partidos en esta categoría.</div>
            </div>
        </main>

        <aside class="relative space-y-5 lg:sticky lg:top-24 lg:self-start">
            <section class="overflow-hidden rounded-3xl border border-white/10 bg-[#11182b] shadow-2xl shadow-black/30">
                <div class="relative overflow-hidden border-b border-white/10 p-5"><img src="{{ asset('images/lootra_visual_pack/13_sports/sports_ball_768x512.webp') }}" alt="" class="absolute inset-0 h-full w-full object-cover opacity-35" width="768" height="512" loading="lazy" decoding="async" aria-hidden="true"><div class="absolute inset-0 bg-gradient-to-r from-[#10182d] via-[#10182d]/85 to-fuchsia-950/45"></div><div class="relative"><h2 class="font-display text-xl font-bold">Tu boleto</h2><p class="mt-1 text-xs text-slate-300">Apuesta simple · cuota fija</p></div></div>
                <div class="p-5">
                    <div x-show="!selected" class="py-9 text-center"><div class="mx-auto grid h-16 w-16 place-items-center rounded-2xl bg-white/5 text-2xl">＋</div><p class="mt-4 font-semibold text-slate-300">Elige una cuota</p><p class="mt-1 text-xs text-slate-500">Tu selección aparecerá aquí</p></div>
                    <div x-show="selected" x-cloak>
                        <div class="rounded-2xl border border-cyan-400/20 bg-cyan-400/5 p-4"><div class="flex justify-between gap-3"><div><p class="text-xs text-slate-500" x-text="selected?.match.league"></p><b class="mt-1 block text-sm" x-text="selected?.match.home+' — '+selected?.match.away"></b><p class="mt-2 text-sm text-cyan-300" x-text="selected?.choice.label"></p></div><b class="text-xl text-emerald-300" x-text="Number(selected?.choice.odd||0).toFixed(2)"></b></div></div>
                        <label class="mt-5 block text-xs font-bold uppercase tracking-wider text-slate-400">Importe</label>
                        <div class="mt-2 flex items-center rounded-xl border border-white/10 bg-black/25 px-4"><span class="text-slate-500">EUR Demo</span><input x-model.number="stake" type="number" min="1" max="5000" step="1" class="w-full bg-transparent px-3 py-3 font-bold outline-none" placeholder="10.00"></div>
                        <div class="mt-3 grid grid-cols-4 gap-2"><template x-for="amount in [5,10,25,50]"><button @click="stake=amount" class="rounded-lg bg-white/5 py-2 text-xs font-bold text-slate-400 hover:bg-white/10 hover:text-white" x-text="amount+' EUR Demo'"></button></template></div>
                        <div class="my-5 flex justify-between border-y border-white/10 py-4 text-sm"><span class="text-slate-400">Retorno potencial</span><b class="text-lg text-emerald-300" x-text="money((stake||0)*(selected?.choice.odd||0))"></b></div>
                        @auth
                            <button @click="placeBet" :disabled="submitting||!stake" class="w-full rounded-xl bg-gradient-to-r from-cyan-400 to-emerald-400 py-3.5 font-black text-slate-950 shadow-lg shadow-cyan-500/20 transition hover:brightness-110 disabled:opacity-50" x-text="submitting?'Procesando…':'Confirmar apuesta'"></button>
                        @else
                            <a href="{{ route('login') }}" class="block w-full rounded-xl bg-gradient-to-r from-cyan-400 to-emerald-400 py-3.5 text-center font-black text-slate-950">Inicia sesión para apostar</a>
                        @endauth
                    </div>
                    <p x-show="message" x-text="message" class="mt-4 rounded-xl px-3 py-2 text-center text-xs" :class="error?'bg-rose-500/15 text-rose-300':'bg-emerald-500/15 text-emerald-300'"></p>
                </div>
            </section>

            @auth
            <section class="rounded-3xl border border-white/10 bg-[#11182b] p-5"><div class="mb-4 flex items-center justify-between"><h2 class="font-display font-bold">Mis apuestas</h2><span class="rounded-full bg-white/5 px-2 py-1 text-xs text-slate-400" x-text="bets.length"></span></div>
                <div class="max-h-[410px] space-y-3 overflow-y-auto pr-1"><template x-for="bet in bets" :key="bet.id"><div class="rounded-xl border border-white/10 bg-black/20 p-3"><div class="flex justify-between gap-3"><div><p class="truncate text-xs font-bold" x-text="bet.match"></p><p class="mt-1 text-xs text-slate-500"><span x-text="bet.selection"></span> · <span x-text="Number(bet.odds).toFixed(2)"></span></p></div><span class="h-fit rounded-full px-2 py-1 text-[10px] font-black uppercase" :class="betClass(bet.status)" x-text="bet.status"></span></div><div class="mt-3 flex justify-between text-xs text-slate-400"><span x-text="'Apuesta '+money(bet.amount)"></span><b class="text-white" x-text="bet.status==='ganada'?'Premio '+money(bet.winnings):'Posible '+money(bet.potential)"></b></div></div></template><p x-show="!bets.length" class="py-6 text-center text-xs text-slate-500">Aún no has realizado apuestas.</p></div>
            </section>
            @endauth
        </aside>
    </div>
</div>

<script>
function sportsbook() {
    return {
        matches: @json($matches), bets: @json($bets), filter: 'all', selected: null, stake: 10, submitting: false, message: '', error: false,
        get filteredMatches(){ return this.matches.filter(m=>this.filter==='all'||m.status===this.filter).sort((a,b)=>({en_vivo:0,programado:1,finalizado:2}[a.status]-{en_vivo:0,programado:1,finalizado:2}[b.status])); },
        get liveCount(){ return this.matches.filter(m=>m.status==='en_vivo').length; }, get openCount(){ return this.matches.filter(m=>m.betting_open).length; },
        poller:null,visibilityHandler:null,
        start(){this.visibilityHandler=()=>document.hidden?this.pausePolling():this.resumePolling();this.resumePolling();document.addEventListener('visibilitychange',this.visibilityHandler)},
        destroy(){this.pausePolling();document.removeEventListener('visibilitychange',this.visibilityHandler)},
        pausePolling(){if(this.poller){clearInterval(this.poller);this.poller=null}},
        resumePolling(){if(this.poller)return;this.refresh();this.poller=setInterval(()=>this.refresh(),3000)},
        async refresh(){ try{const r=await fetch('{{ route('sports.feed') }}',{headers:{Accept:'application/json'},cache:'no-store'});if(!r.ok)return;const d=await r.json();this.matches=d.matches;this.bets=d.bets||[];if(d.balance!==null&&d.balance!==undefined)this.$store.wallet.saldo=Number(d.balance);if(this.selected){const fresh=this.matches.find(m=>m.id===this.selected.match.id);if(!fresh?.betting_open)this.selected=null;else this.selected.match=fresh;}}catch(e){} },
        choices(m){return [{key:'local',label:'1 · '+m.home,odd:m.odds.local},{key:'empate',label:'X · Empate',odd:m.odds.empate},{key:'visitante',label:'2 · '+m.away,odd:m.odds.visitante}]},
        select(match,choice){if(match.betting_open){this.selected={match,choice,requestToken:window.lootraRequestToken()};this.message='';}}, money(v){return `${Number(v||0).toFixed(2)} EUR Demo`},
        statusLabel(m){return m.status==='en_vivo'?'● EN VIVO · '+m.minute+'′':m.status==='finalizado'?'FINAL':'PRÓXIMO'}, statusClass(s){return s==='en_vivo'?'bg-rose-500/15 text-rose-300':s==='finalizado'?'bg-slate-500/15 text-slate-400':'bg-cyan-500/15 text-cyan-300'}, betClass(s){return s==='ganada'?'bg-emerald-500/15 text-emerald-300':s==='perdida'?'bg-rose-500/15 text-rose-300':'bg-amber-500/15 text-amber-300'},
        async placeBet(){if(!this.selected||!this.stake)return;this.submitting=true;this.message='';try{const url=@js(route('sports.place',['partido'=>'__MATCH__'])).replace('__MATCH__',encodeURIComponent(this.selected.match.id));const r=await fetch(url,{method:'POST',headers:{'Content-Type':'application/json','Accept':'application/json','X-CSRF-TOKEN':document.querySelector('meta[name="csrf-token"]').content},body:JSON.stringify({seleccion:this.selected.choice.key,importe:this.stake,request_token:this.selected.requestToken})});const d=await r.json();if(!r.ok)throw new Error(d.message||'No se pudo realizar la apuesta.');this.$store.wallet.saldo=Number(d.balance);if(!this.bets.some(b=>b.id===d.bet.id))this.bets.unshift(d.bet);this.selected=null;this.message=d.message;this.error=false;}catch(e){this.message=e.message;this.error=true;}finally{this.submitting=false;} }
    }
}
</script>
@endsection
