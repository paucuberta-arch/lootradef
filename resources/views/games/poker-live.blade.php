@extends('layouts.app')
@section('title', "Texas Hold'em Live — Lootra")

@section('styles')
<style>
    .poker-room { min-height:760px;background:#030309 url('{{ asset('images/poker-live.webp') }}') center top/cover no-repeat;position:relative;isolation:isolate; }
    .poker-room::after { content:"";position:absolute;inset:0;background:linear-gradient(to bottom,rgba(2,2,8,.08),rgba(3,3,9,.12) 55%,rgba(3,3,9,.94));z-index:-1; }
    .live-pill::before { content:"";width:8px;height:8px;border-radius:50%;background:#fb7185;box-shadow:0 0 12px #f43f5e;animation:pulse 1s infinite; }
    .poker-card { width:clamp(58px,8vw,92px);aspect-ratio:.69;border-radius:10px;background:linear-gradient(145deg,#fff,#e2e8f0);color:#111827;position:relative;padding:8px;box-shadow:0 18px 35px rgba(0,0,0,.55);border:1px solid white;animation:deal-pro  .5s cubic-bezier(.2,.8,.2,1) both; }
    .poker-card.red { color:#dc2626; }
    .poker-card .rank { font:900 clamp(18px,3vw,30px) "Space Grotesk";line-height:1; }
    .poker-card .suit { font-size:clamp(20px,4vw,38px);position:absolute;inset:0;display:grid;place-items:center; }
    .poker-card.back { background:repeating-linear-gradient(45deg,#111827 0 7px,#312e81 7px 14px);border:4px solid #d4af37; }
    @keyframes deal-pro { from{opacity:0;transform:translateY(-100px) rotate(-14deg) scale(.65)}to{opacity:1;transform:none} }
    .dealer-orb { width:68px;height:68px;border-radius:50%;background:linear-gradient(145deg,#d946ef,#0891b2);padding:2px;box-shadow:0 0 40px rgba(217,70,239,.45); }
    .dealer-orb span { width:100%;height:100%;border-radius:50%;display:grid;place-items:center;background:#080812;font:900 20px "Space Grotesk"; }
    .poker-controls { background:linear-gradient(145deg,rgba(15,15,35,.94),rgba(5,5,15,.96));backdrop-filter:blur(20px); }
</style>
@endsection

@section('contenido')
<div class="max-w-[1500px] mx-auto px-3 sm:px-6 py-6" x-data="pokerLive()">
    <section class="poker-room rounded-[2rem] border border-amber-300/20 overflow-hidden shadow-[0_40px_120px_rgba(0,0,0,.7)]">
        <header class="flex items-center justify-between p-5 sm:p-7">
            <div><div class="live-pill inline-flex items-center gap-2 px-3 py-1 rounded-full bg-red-500/10 border border-red-400/20 text-red-300 text-[10px] font-bold tracking-[.2em]">LIVE TABLE 01</div><h1 class="font-display text-2xl sm:text-4xl font-bold mt-3">Texas Hold'em <span class="text-amber-300">Noir</span></h1></div>
            <div class="flex gap-3"><div class="hidden sm:block px-4 py-2 rounded-xl bg-black/35 border border-white/10"><p class="text-[10px] text-slate-500">MESA</p><strong class="text-emerald-300">€1 — €500</strong></div><div class="balance-chip px-4 py-2 rounded-xl"><p class="text-[10px] text-slate-500">TU SALDO</p><strong class="text-brand-300" x-text="money(saldo)"></strong></div></div>
        </header>

        <div class="absolute top-[23%] left-1/2 -translate-x-1/2 text-center">
            <div class="dealer-orb mx-auto"><span>AI</span></div><p class="text-xs font-bold mt-2">DEALER NOVA</p><p class="text-[10px] text-emerald-300">● En directo</p>
        </div>

        <div class="absolute top-[35%] inset-x-0 text-center">
            <p class="text-[10px] uppercase tracking-[.25em] text-slate-400 mb-3">Mano del dealer</p>
            <div class="flex justify-center gap-2">
                <template x-for="(card,i) in (result?.dealer || [{},{ }])"><div class="poker-card" :class="result ? cardColor(card) : 'back'" :style="`animation-delay:${i*.12}s`"><template x-if="result"><div><span class="rank" x-text="rank(card.rank)"></span><span class="suit" x-text="suit(card.suit)"></span></div></template></div></template>
            </div>
            <p x-show="result" class="mt-2 text-xs text-slate-300" x-text="result?.dealer_hand"></p>
        </div>

        <div class="absolute top-[57%] inset-x-0 text-center">
            <p class="text-[10px] uppercase tracking-[.25em] text-amber-200/70 mb-3">Board</p>
            <div class="flex justify-center gap-1.5 sm:gap-3 min-h-24">
                <template x-for="(card,i) in (result?.community || [])"><div class="poker-card" :class="cardColor(card)" :style="`animation-delay:${.25+i*.12}s`"><span class="rank" x-text="rank(card.rank)"></span><span class="suit" x-text="suit(card.suit)"></span></div></template>
                <template x-if="!result"><div class="flex gap-2"><template x-for="i in 5"><div class="w-14 sm:w-20 aspect-[.69] rounded-lg border border-dashed border-white/15 bg-black/10"></div></template></div></template>
            </div>
        </div>

        <div class="absolute bottom-[15%] inset-x-0 text-center">
            <div class="flex justify-center gap-3">
                <template x-for="(card,i) in (result?.player || [])"><div class="poker-card" :class="cardColor(card)" :style="`animation-delay:${i*.12}s`"><span class="rank" x-text="rank(card.rank)"></span><span class="suit" x-text="suit(card.suit)"></span></div></template>
                <template x-if="!result"><div class="flex gap-3"><div class="poker-card back"></div><div class="poker-card back"></div></div></template>
            </div>
            <p x-show="result" class="mt-3 text-sm font-bold text-cyan-200" x-text="result?.player_hand"></p>
        </div>

        <div x-show="result" x-transition class="absolute inset-0 z-20 grid place-items-center pointer-events-none"><div class="mt-20 px-8 py-4 rounded-2xl bg-black/75 border backdrop-blur-xl" :class="result?.ganancia>apuesta?'border-emerald-400/40':'border-red-400/40'"><p class="font-display text-2xl font-bold" :class="result?.ganancia>apuesta?'text-emerald-300':(result?.ganancia==apuesta?'text-amber-300':'text-red-300')" x-text="message"></p></div></div>
    </section>

    <section class="poker-controls relative -mt-8 mx-3 sm:mx-10 z-30 rounded-2xl border border-white/10 p-4 sm:p-6 shadow-2xl">
        <div class="flex flex-col sm:flex-row items-center gap-4 max-w-3xl mx-auto"><div class="w-full sm:flex-1"><label class="text-[10px] uppercase tracking-widest text-slate-500">Buy-in</label><input x-model.number="apuesta" type="range" min="1" max="500" step="1" class="w-full accent-amber-400 mt-2"><div class="flex justify-between text-xs"><span class="text-slate-500">€1</span><strong class="text-amber-300" x-text="money(apuesta)"></strong><span class="text-slate-500">€500</span></div></div><button @click="deal" :disabled="playing || apuesta>saldo" class="cta-shine w-full sm:w-auto px-10 py-4 rounded-xl bg-gradient-to-r from-amber-300 via-yellow-500 to-fuchsia-500 text-black font-black disabled:opacity-40"><span x-text="playing?'Repartiendo...':(result?'Nueva mano':'Entrar en la mesa')"></span></button></div><p x-show="error" class="text-center text-red-400 text-sm mt-3" x-text="error"></p>
    </section>
</div>

@push('scripts')
<script>
function pokerLive(){return{saldo:{{ auth()->user()->saldo }},apuesta:10,playing:false,result:null,error:'',get message(){if(!this.result)return'';return this.result.ganancia>this.apuesta?`GANAS ${this.money(this.result.ganancia)} · ${this.result.player_hand}`:(this.result.ganancia==this.apuesta?'EMPATE · APUESTA DEVUELTA':`GANA EL DEALER · ${this.result.dealer_hand}`)},money(v){return new Intl.NumberFormat('es-ES',{style:'currency',currency:'EUR'}).format(Number(v||0))},rank(v){return({11:'J',12:'Q',13:'K',14:'A'})[v]||v},suit(v){return({S:'♠',H:'♥',D:'♦',C:'♣'})[v]},cardColor(c){return(c.suit==='H'||c.suit==='D')?'red':''},async deal(){this.playing=true;this.result=null;this.error='';try{const r=await fetch(@js(route('arcade.play',['game'=>'texas-holdem'])),{method:'POST',headers:{'Content-Type':'application/json','X-CSRF-TOKEN':document.querySelector('meta[name="csrf-token"]').content,'Accept':'application/json'},body:JSON.stringify({apuesta:this.apuesta})});const d=await r.json();if(!r.ok)throw new Error(d.message||'La mesa no está disponible.');await new Promise(x=>setTimeout(x,900));this.result=d;this.saldo=Number(d.saldo);Alpine.store('wallet').saldo=this.saldo;window.dispatchEvent(new CustomEvent('saldo-updated',{detail:{saldo:this.saldo}}))}catch(e){this.error=e.message}this.playing=false}}}
</script>
@endpush
@endsection
