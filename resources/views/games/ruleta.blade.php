@extends('layouts.app')
@section('title', $gameName . ' — Lootra Casino')

@section('styles')
<style>
    @keyframes wheel-spin { from{transform:rotate(0)} to{transform:rotate(2520deg)} }
    @keyframes ball-orbit { 0%{transform:rotate(0) translateX(132px) rotate(0)} 100%{transform:rotate(-2160deg) translateX(60px) rotate(2160deg)} }
    @keyframes pointer-pendulum { 0%,100%{transform:translateX(-50%) rotate(-13deg)} 50%{transform:translateX(-50%) rotate(13deg)} }
    @keyframes electric { 0%,100%{filter:drop-shadow(0 0 5px #22d3ee)} 45%{filter:drop-shadow(0 0 18px #a78bfa) brightness(1.4)} }
    .roulette-shell{position:relative;width:min(350px,82vw);aspect-ratio:1;margin:auto;perspective:800px}
    .roulette-wheel{position:absolute;inset:8%;border-radius:50%;padding:20px;background:repeating-conic-gradient(from -4.86deg,#b91c1c 0 9.72deg,#101827 9.72deg 19.44deg);border:10px solid #d9a441;box-shadow:0 0 0 8px #3b2108,0 35px 80px #000b,inset 0 0 38px #000;transition:transform .2s}
    .roulette-wheel::before{content:"";position:absolute;inset:24%;border-radius:50%;background:radial-gradient(circle at 35% 28%,#fff3bd,#c78b2b 22%,#3b2108 25%,#080b13 57%,#e0ad45 60%,#5b320b 66%);box-shadow:inset 0 0 20px #000,0 0 22px #000}
    .roulette-wheel::after{content:"0";position:absolute;left:47%;top:1.5%;font-size:11px;font-weight:900;color:#b7ffd6;background:#047857;border-radius:50%;width:19px;height:19px;display:grid;place-items:center}
    .roulette-wheel.spinning{animation:wheel-spin 4.6s cubic-bezier(.12,.68,.08,1) forwards}
    .roulette-ball{position:absolute;left:50%;top:50%;z-index:8;width:13px;height:13px;margin:-6px;border-radius:50%;background:radial-gradient(circle at 32% 25%,#fff,#d8dee9 45%,#64748b);box-shadow:0 2px 7px #000}
    .roulette-ball.spinning{animation:ball-orbit 4.4s cubic-bezier(.2,.65,.12,1) forwards}
    .pointer-arm{position:absolute;z-index:12;left:50%;top:-1%;width:32px;height:65px;transform-origin:50% 5%;animation:pointer-pendulum .28s ease-in-out infinite;filter:drop-shadow(0 5px 4px #000)}
    .pointer-arm::before{content:"";position:absolute;left:7px;top:0;width:18px;height:25px;border-radius:9px;background:linear-gradient(90deg,#8b5e16,#fff0a8,#9a6719);border:2px solid #4b2d08}
    .pointer-arm::after{content:"";position:absolute;left:8px;top:19px;border-left:8px solid transparent;border-right:8px solid transparent;border-top:38px solid #f8fafc}
    .pointer-arm.idle{animation:none;transform:translateX(-50%)}
    .result-reveal{position:absolute;z-index:20;left:50%;top:-22%;translate:-50% -50%;width:58px;height:58px;border-radius:18px;display:grid;place-items:center;font-size:25px;font-weight:950;opacity:0;filter:blur(18px);transform:scale(.35);transition:all 1.35s cubic-bezier(.16,1,.3,1)}
    .result-reveal.approaching{top:27%;opacity:.72;filter:blur(8px);transform:scale(.7)}
    .result-reveal.resolved{top:50%;opacity:1;filter:blur(0);transform:scale(1.35);box-shadow:0 0 0 8px #ffffff18,0 15px 40px #000}
    .lightning-stage .roulette-wheel{box-shadow:0 0 0 8px #312e81,0 0 55px #22d3ee55,inset 0 0 38px #000;animation-name:wheel-spin,electric}
    .number-cell{transition:transform .16s,border-color .16s,filter .16s}.number-cell:hover{transform:translateY(-3px);filter:brightness(1.2)}
</style>
@endsection

@section('contenido')
<div class="mx-auto max-w-[1450px] px-4 py-7 sm:px-6 sm:py-10" x-data="rouletteGame()">
    <header class="mb-6 flex flex-wrap items-center justify-between gap-4">
        <div><div class="flex items-center gap-3"><span class="grid h-11 w-11 place-items-center rounded-xl bg-gradient-to-br {{ $variant === 'lightning' ? 'from-cyan-400 to-violet-600' : 'from-amber-300 to-red-600' }} shadow-lg">◆</span><div><h1 class="game-heading font-extrabold">{{ $gameName }}</h1><p class="mt-1 text-sm text-slate-500">Elige una casilla, confirma el boleto y sigue la bola</p></div></div></div>
        <div class="flex gap-2"><a href="{{ route('ruleta') }}" class="rounded-xl border px-3 py-2 text-xs font-bold {{ $variant === 'european' ? 'border-amber-400/40 bg-amber-400/10 text-amber-300' : 'border-white/10 text-slate-400' }}">Europea</a><a href="{{ route('ruleta.lightning') }}" class="rounded-xl border px-3 py-2 text-xs font-bold {{ $variant === 'lightning' ? 'border-cyan-400/40 bg-cyan-400/10 text-cyan-300' : 'border-white/10 text-slate-400' }}">Lightning</a><div class="rounded-xl border border-white/10 bg-white/5 px-4 py-2 text-sm"><span class="text-slate-500">Saldo</span> <b class="ml-1 text-brand-400" x-text="money(saldo)"></b></div></div>
    </header>

    <div class="grid gap-6 xl:grid-cols-[minmax(360px,.82fr)_minmax(520px,1.18fr)_290px]">
        <section class="game-stage rounded-[1.75rem] border border-white/5 bg-white/[.03] p-5 sm:p-7 {{ $variant === 'lightning' ? 'lightning-stage' : '' }}">
            <div class="roulette-shell">
                <div class="pointer-arm" :class="spinning?'':'idle'"></div>
                <div class="roulette-wheel" :class="spinning?'spinning':''"></div>
                <div class="roulette-ball" :class="spinning?'spinning':''"></div>
                <div x-show="lastNumero!==null" class="result-reveal" :class="[revealStage===1?'approaching':'',revealStage===2?'resolved':'',resultClass(lastColor)]" x-text="lastNumero"></div>
            </div>
            <div class="mt-3 min-h-20 text-center">
                <p class="text-xs font-black uppercase tracking-[.2em]" :class="spinning?'text-cyan-300':'text-slate-500'" x-text="statusText"></p>
                <div x-show="revealStage===2" class="mt-3"><b class="text-xl" x-text="`Ha salido el ${lastNumero} ${lastColor}`"></b><p class="mt-1 text-sm" :class="ganancia>0?'text-emerald-300':'text-slate-500'" x-text="ganancia>0?'Premio '+money(ganancia):'La próxima puede ser la tuya'"></p></div>
            </div>
        </section>

        <section class="rounded-[1.75rem] border border-white/5 bg-[#0e1424] p-5 sm:p-7">
            <div class="mb-5 flex items-start justify-between gap-3"><div><p class="text-[10px] font-black uppercase tracking-[.2em] text-cyan-400">Paso 1</p><h2 class="mt-1 text-xl font-black">Elige dónde apostar</h2></div><span class="rounded-lg bg-white/5 px-3 py-2 text-xs text-slate-400">Seleccionado: <b class="text-white" x-text="selectionLabel"></b></span></div>
            <div class="mb-5 grid grid-cols-3 gap-2">
                <button @click="select('rojo')" :class="selected('rojo')?'ring-2 ring-white':''" class="rounded-xl bg-red-600 py-3 font-black">Rojo <small class="block opacity-70">x2</small></button>
                <button @click="select('negro')" :class="selected('negro')?'ring-2 ring-white':''" class="rounded-xl bg-slate-800 py-3 font-black">Negro <small class="block opacity-70">x2</small></button>
                <button @click="select('numero',0)" :class="selected('numero',0)?'ring-2 ring-white':''" class="rounded-xl bg-emerald-700 py-3 font-black">Cero <small class="block opacity-70">x35</small></button>
            </div>
            <div class="mb-3 flex items-center justify-between"><div><p class="text-[10px] font-black uppercase tracking-[.2em] text-fuchsia-400">Número exacto</p><h3 class="font-bold">Pulsa directamente del 1 al 36</h3></div><span class="rounded-lg bg-fuchsia-500/10 px-2 py-1 text-xs font-bold text-fuchsia-300">Paga x35</span></div>
            <div class="grid grid-cols-6 gap-1.5 rounded-2xl border border-white/5 bg-black/20 p-3">
                <template x-for="n in 36" :key="n"><button @click="select('numero',n)" class="number-cell aspect-square rounded-lg border text-xs font-black" :class="numberClass(n)" x-text="n"></button></template>
            </div>
            <div class="mt-5 grid grid-cols-2 gap-2"><button @click="select('par')" :class="selected('par')?'border-brand-400 bg-brand-400/15 text-brand-300':'border-white/10 bg-white/5 text-slate-400'" class="rounded-xl border py-3 font-bold">Par · x2</button><button @click="select('impar')" :class="selected('impar')?'border-brand-400 bg-brand-400/15 text-brand-300':'border-white/10 bg-white/5 text-slate-400'" class="rounded-xl border py-3 font-bold">Impar · x2</button></div>
            <div class="mt-2 grid grid-cols-3 gap-2"><template x-for="d in [{id:'docena1',t:'1–12'},{id:'docena2',t:'13–24'},{id:'docena3',t:'25–36'}]"><button @click="select(d.id)" :class="selected(d.id)?'border-cyan-400 bg-cyan-400/15 text-cyan-300':'border-white/10 bg-white/5 text-slate-400'" class="rounded-xl border py-3 text-sm font-bold" x-text="d.t+' · x3'"></button></template></div>
        </section>

        <aside class="space-y-5">
            <section class="rounded-2xl border border-white/10 bg-white/[.035] p-5"><p class="text-[10px] font-black uppercase tracking-[.2em] text-brand-400">Paso 2</p><h2 class="mt-1 text-lg font-black">Confirma tu boleto</h2><div class="mt-4 rounded-xl border border-white/10 bg-black/20 p-3"><p class="text-xs text-slate-500">Tu selección</p><b class="mt-1 block text-cyan-300" x-text="selectionLabel"></b></div><label class="mt-4 block text-xs text-slate-500">Importe de la apuesta</label><div class="mt-2 flex items-center rounded-xl border border-white/10 bg-black/20 px-3"><span class="text-slate-500">€</span><input x-model.number="apuesta" type="number" min=".1" max="500" step=".1" :disabled="spinning" class="w-full bg-transparent px-2 py-3 font-bold outline-none"></div><div class="mt-2 grid grid-cols-4 gap-1"><template x-for="v in [1,5,10,25]"><button @click="apuesta=v" class="rounded-lg bg-white/5 py-2 text-xs text-slate-400 hover:text-white" x-text="v+'€'"></button></template></div><button @click="play" :disabled="spinning||apuesta>saldo||!apuesta" class="mt-4 w-full rounded-xl bg-gradient-to-r {{ $variant === 'lightning' ? 'from-cyan-400 to-violet-500' : 'from-amber-300 to-orange-500' }} py-3.5 font-black text-slate-950 shadow-lg disabled:opacity-40" x-text="spinning?'La bola está girando…':'Girar ruleta'"></button><p x-show="error" class="mt-3 text-center text-xs text-red-300" x-text="error"></p></section>
            @if($variant === 'lightning')<section class="rounded-2xl border border-cyan-400/20 bg-cyan-400/[.06] p-4"><h3 class="text-sm font-bold text-cyan-300">⚡ Números Lightning</h3><p class="mt-2 text-xs leading-relaxed text-slate-400">En cada giro se cargan cinco números con multiplicadores de x50 a x500. Se revelan con el resultado.</p><div x-show="Object.keys(multipliers).length" class="mt-3 flex flex-wrap gap-1"><template x-for="(boost,n) in multipliers"><span class="rounded-lg bg-violet-500/15 px-2 py-1 text-xs text-violet-300" x-text="n+' · x'+boost"></span></template></div></section>@endif
            <section class="rounded-2xl border border-white/10 bg-white/[.035] p-5"><h3 class="mb-3 text-sm font-black uppercase tracking-wider">Últimos giros</h3><div class="space-y-2"><template x-for="h in historial.slice(0,8)"><div class="flex items-center text-xs"><span class="grid h-7 w-7 place-items-center rounded-full font-black" :class="resultClass(h.color)" x-text="h.numero"></span><span class="ml-2 text-slate-500" x-text="h.tipo"></span><b class="ml-auto" :class="h.ganancia>0?'text-emerald-300':'text-slate-600'" x-text="h.ganancia>0?'+'+money(h.ganancia):'-'+money(h.apuesta)"></b></div></template><p x-show="!historial.length" class="py-3 text-center text-xs text-slate-600">Todavía no hay giros.</p></div></section>
        </aside>
    </div>
</div>

@push('scripts')
<script>
function rouletteGame(){return{
    saldo:{{ Auth::user()?->cartera?->saldo ?? 0 }},apuesta:1,tipo:'rojo',valor:null,spinning:false,ganancia:0,lastNumero:null,lastColor:null,revealStage:0,error:'',multipliers:{},
    historial:@js($partidas->map(fn($p)=>['numero'=>$p->detalles['numero']??0,'color'=>$p->detalles['color']??'verde','tipo'=>$p->detalles['tipo_apuesta']??'','ganancia'=>(float)$p->ganancia,'apuesta'=>(float)$p->apuesta])->all()),
    red:[1,3,5,7,9,11,13,15,17,19,21,23,25,27,30,32,34,36],
    get selectionLabel(){if(this.tipo==='numero')return `Número ${this.valor}`;return {rojo:'Rojo',negro:'Negro',par:'Par',impar:'Impar',docena1:'Primera docena',docena2:'Segunda docena',docena3:'Tercera docena'}[this.tipo]},
    get statusText(){return this.spinning?(this.revealStage===0?'La bola busca su casilla…':'Acercándonos al resultado…'):(this.lastNumero===null?'Selecciona una apuesta para empezar':'Resultado confirmado')},
    select(tipo,valor=null){if(this.spinning)return;this.tipo=tipo;this.valor=valor},selected(t,v=null){return this.tipo===t&&(t!=='numero'||this.valor===v)},
    numberClass(n){const active=this.selected('numero',n);const red=this.red.includes(n);return [active?'ring-2 ring-cyan-300 border-cyan-300':'border-white/10',red?'bg-red-600/80 text-white':'bg-slate-800 text-white']},
    resultClass(c){return c==='rojo'?'bg-red-600 text-white':c==='negro'?'bg-slate-800 text-white border border-white/20':'bg-emerald-600 text-white'},money(v){return new Intl.NumberFormat('es-ES',{style:'currency',currency:'EUR'}).format(Number(v)||0)},
    async play(){if(this.spinning||this.apuesta>this.saldo)return;this.spinning=true;this.revealStage=0;this.lastNumero=null;this.ganancia=0;this.error='';try{const r=await fetch(@js($playRoute),{method:'POST',headers:{'Content-Type':'application/json','Accept':'application/json','X-CSRF-TOKEN':document.querySelector('meta[name="csrf-token"]').content},body:JSON.stringify({apuesta:this.apuesta,tipo:this.tipo,valor:this.valor})});const d=await r.json();if(!r.ok)throw new Error(d.error||d.message||'No se pudo completar el giro.');await new Promise(x=>setTimeout(x,1500));this.lastNumero=d.numero;this.lastColor=d.color;this.multipliers=d.multipliers||{};this.revealStage=1;await new Promise(x=>setTimeout(x,1800));this.revealStage=2;await new Promise(x=>setTimeout(x,1300));this.ganancia=Number(d.ganancia);this.saldo=Number(d.saldo);this.$store.wallet.saldo=this.saldo;this.historial.unshift({numero:d.numero,color:d.color,tipo:this.selectionLabel,ganancia:this.ganancia,apuesta:this.apuesta});window.dispatchEvent(new CustomEvent('saldo-updated',{detail:{saldo:this.saldo}}));}catch(e){this.error=e.message;}finally{this.spinning=false;}}
}}
</script>
@endpush
@endsection
