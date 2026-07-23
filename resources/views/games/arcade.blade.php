@extends('layouts.game')
@section('title', $game['name'].' — Lootra Originals')

@section('styles')
<style>
    .original-stage { background:radial-gradient(circle at 50% 25%,rgba(124,58,237,.25),transparent 30rem),linear-gradient(145deg,#11112a,#060611);min-height:430px; }
    .game-wheel { width:260px;aspect-ratio:1;border-radius:50%;margin:auto;background:conic-gradient(#f43f5e 0 36deg,#f59e0b 36deg 72deg,#10b981 72deg 108deg,#06b6d4 108deg 144deg,#8b5cf6 144deg 180deg,#ec4899 180deg 216deg,#eab308 216deg 252deg,#14b8a6 252deg 288deg,#6366f1 288deg 324deg,#f43f5e 324deg);border:12px solid #f8fafc;box-shadow:0 0 0 9px #312e81,0 35px 70px #000;position:relative;transition:transform 2.2s cubic-bezier(.1,.7,.1,1); }
    .game-wheel::after { content:"L";position:absolute;inset:35%;border-radius:50%;display:grid;place-items:center;background:#090914;color:#fbbf24;font:900 36px "Space Grotesk";border:5px solid #fbbf24; }
    .mine-cell { aspect-ratio:1;background:linear-gradient(145deg,#172554,#1e1b4b);border:1px solid rgba(103,232,249,.18);box-shadow:inset 0 1px rgba(255,255,255,.08); }
    .dice-cube { width:150px;aspect-ratio:1;border-radius:30px;background:linear-gradient(145deg,#fff,#a5f3fc);color:#111827;display:grid;place-items:center;font:900 64px "Space Grotesk";box-shadow:0 30px 60px #000, inset -12px -12px 30px #67e8f9; }
    .playing-card { width:120px;height:168px;border-radius:16px;background:linear-gradient(145deg,#fff,#e2e8f0);color:#111827;display:grid;place-items:center;font:900 48px "Space Grotesk";box-shadow:0 22px 45px #000; }
    .plinko-board { width:min(430px,90vw);height:330px;position:relative;margin:auto;background:linear-gradient(#171747,#090914);clip-path:polygon(50% 0,100% 100%,0 100%); }
    .peg { position:absolute;width:8px;height:8px;border-radius:50%;background:#67e8f9;box-shadow:0 0 10px #22d3ee; }
    .coin-3d { width:170px;aspect-ratio:1;border-radius:50%;display:grid;place-items:center;background:radial-gradient(circle at 35% 30%,#fff7ad,#f59e0b 45%,#92400e);border:10px ridge #fbbf24;color:#4a2504;font:900 30px "Space Grotesk";box-shadow:0 30px 60px #000,0 0 40px rgba(245,158,11,.3); }
    .is-playing .dice-cube,.is-playing .coin-3d,.is-playing .playing-card { animation:game-tumble .55s ease-in-out infinite alternate; }
    @keyframes game-tumble { to{transform:rotateY(180deg) rotateX(18deg) scale(1.06)} }
    .crystal { width:110px;height:150px;clip-path:polygon(50% 0,95% 30%,75% 100%,25% 100%,5% 30%);filter:drop-shadow(0 0 25px currentColor);transition:transform .3s; }
    .crystal:hover,.crystal.selected { transform:translateY(-15px) scale(1.08); }
    @media (max-width:639px) {
        .original-stage { min-height:350px; }
        .game-wheel { width:min(210px,72vw);border-width:8px;box-shadow:0 0 0 5px #312e81,0 24px 50px #000; }
        .game-wheel::after { font-size:28px;border-width:3px; }
        .dice-cube { width:120px;border-radius:24px;font-size:52px; }
        .playing-card { width:86px;height:122px;border-radius:12px;font-size:36px; }
        .plinko-board { width:100%;height:250px; }
        .coin-3d { width:135px;border-width:7px;font-size:25px; }
        .crystal { width:76px;height:108px; }
    }
</style>
@endsection

@section('game-content')
<div class="mx-auto max-w-[1350px] px-4 pt-5"><x-campaign.rickyedit.banner variant="compact" /></div>
<div class="max-w-[1200px] mx-auto px-4 sm:px-6 py-8" x-data="originalGame()">
    <header class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
        <div><p class="text-xs uppercase tracking-[.2em] text-fuchsia-400 font-bold">Lootra Originals · {{ ucfirst($game['mode']) }}</p><h1 class="game-heading font-extrabold mt-2">{{ $game['name'] }}</h1><p class="text-sm text-slate-500 mt-2">{{ $game['description'] }}</p></div>
        <div class="balance-chip px-5 py-3 rounded-xl"><span class="text-xs text-slate-500">Saldo</span><strong class="ml-2 text-brand-300" x-text="money(saldo)"></strong></div>
    </header>

    <section class="original-stage game-stage rounded-[2rem] border border-white/10 overflow-hidden p-4 sm:p-10" :class="playing && 'is-playing'">
        <div class="min-h-[280px] sm:min-h-[330px] grid place-items-center min-w-0">
            @if($game['mode'] === 'wheel')
                <div class="text-center"><div class="game-wheel" :style="`transform:rotate(${rotation}deg)`"></div><p class="mt-8 text-xl font-bold" x-text="result ? result.label : 'Gira la rueda de neón'"></p></div>
            @elseif($game['mode'] === 'mines')
                <div class="w-full max-w-md"><div class="grid grid-cols-4 gap-2 sm:gap-3"><template x-for="i in 16"><button @click="!playing && (selectedCell=i-1)" class="mine-cell rounded-xl grid place-items-center text-cyan-300 text-xl" :class="selectedCell===i-1 && 'ring-2 ring-cyan-300'" x-text="result && result.cell===i-1 ? (result.safe?'◆':'✹') : '?' "></button></template></div><div class="grid grid-cols-2 min-[420px]:grid-cols-4 gap-2 mt-5"><template x-for="m in [1,3,5,8]"><button @click="choice=m" :class="choice===m?'bg-fuchsia-500 text-white':'bg-white/5 text-slate-400'" class="px-3 py-2 rounded-lg text-xs font-bold" x-text="m+' minas'"></button></template></div></div>
            @elseif($game['mode'] === 'dice')
                <div class="text-center"><div class="dice-cube mx-auto" x-text="result?.roll || '?' "></div><div class="flex flex-wrap gap-3 justify-center mt-7"><button @click="choice='low'" :class="choice==='low'?'bg-cyan-400 text-black':'bg-white/5'" class="px-5 sm:px-6 py-3 rounded-xl font-bold">1 — 45</button><button @click="choice='high'" :class="choice==='high'?'bg-fuchsia-500 text-white':'bg-white/5'" class="px-5 sm:px-6 py-3 rounded-xl font-bold">56 — 100</button></div></div>
            @elseif($game['mode'] === 'hilo')
                <div class="text-center"><div class="flex justify-center items-center gap-3 sm:gap-6"><div class="playing-card" x-text="card(result?.first || initialCard)"></div><span class="text-2xl sm:text-3xl text-fuchsia-400">→</span><div class="playing-card" :class="!result && 'bg-[repeating-linear-gradient(45deg,#312e81_0_8px,#0891b2_8px_16px)]'" x-text="result ? card(result.next) : ''"></div></div><div class="flex flex-wrap gap-3 justify-center mt-7"><button @click="choice='lower'" :class="choice==='lower'?'bg-cyan-400 text-black':'bg-white/5'" class="px-5 sm:px-6 py-3 rounded-xl font-bold">Más baja</button><button @click="choice='higher'" :class="choice==='higher'?'bg-fuchsia-500':'bg-white/5'" class="px-5 sm:px-6 py-3 rounded-xl font-bold">Más alta</button></div></div>
            @elseif($game['mode'] === 'plinko')
                <div class="mx-auto max-w-[430px]">
                    <div class="plinko-board"><template x-for="i in 36"><i class="peg" :style="`left:${15+(i%8)*10}%;top:${12+Math.floor(i/8)*17}%`"></i></template><span x-show="playing || result" class="absolute w-5 h-5 rounded-full bg-fuchsia-400 shadow-[0_0_25px_#d946ef] transition-all duration-[1800ms]" :style="result ? `left:${result.slot*8.5+5}%;top:88%` : 'left:48%;top:2%' "></span></div>
                    <div class="mt-2 grid grid-cols-7 gap-1 text-center text-[10px] font-bold text-cyan-300" aria-label="Multiplicadores"><span>.2x</span><span>.5x</span><span>1x</span><span>2x</span><span>5x</span><span>2x</span><span>1x</span></div>
                </div>
            @elseif($game['mode'] === 'keno')
                <div class="w-full max-w-xl"><div class="grid grid-cols-5 sm:grid-cols-6 gap-2"><template x-for="n in 30"><button @click="toggleNumber(n)" class="w-full aspect-square rounded-lg border font-bold text-sm" :class="numbers.includes(n)?'bg-fuchsia-500 border-fuchsia-300':'bg-white/5 border-white/10'" x-text="n"></button></template></div><p class="text-center text-sm text-slate-500 mt-5">Elige exactamente 5 números · <span x-text="numbers.length"></span>/5</p></div>
            @elseif($game['mode'] === 'coin')
                <div class="text-center"><div class="coin-3d mx-auto" x-text="result ? (result.landed==='heads'?'CARA':'CRUZ') : 'L'"></div><div class="flex gap-3 justify-center mt-7"><button @click="choice='heads'" :class="choice==='heads'?'bg-brand-400 text-black':'bg-white/5'" class="px-7 py-3 rounded-xl font-bold">Cara</button><button @click="choice='tails'" :class="choice==='tails'?'bg-brand-400 text-black':'bg-white/5'" class="px-7 py-3 rounded-xl font-bold">Cruz</button></div></div>
            @elseif($game['mode'] === 'baccarat')
                <div class="text-center w-full"><div class="flex justify-center gap-4 sm:gap-8"><div><p class="text-xs text-slate-500 mb-3">JUGADOR</p><div class="playing-card" x-text="result?.player ?? '?' "></div></div><div><p class="text-xs text-slate-500 mb-3">BANCA</p><div class="playing-card" x-text="result?.banker ?? '?' "></div></div></div><div class="flex flex-wrap gap-2 justify-center mt-7"><template x-for="c in [{id:'player',t:'Jugador 2x'},{id:'banker',t:'Banca 1.95x'},{id:'tie',t:'Empate 8x'}]"><button @click="choice=c.id" :class="choice===c.id?'bg-emerald-500 text-black':'bg-white/5'" class="px-4 sm:px-5 py-3 rounded-xl font-bold" x-text="c.t"></button></template></div></div>
            @else
                <div class="text-center"><div class="flex justify-center gap-3 sm:gap-7"><button @click="choice='violet'" class="crystal bg-gradient-to-b from-fuchsia-300 to-violet-800 text-fuchsia-400" :class="choice==='violet'&&'selected'"></button><button @click="choice='cyan'" class="crystal bg-gradient-to-b from-cyan-100 to-cyan-700 text-cyan-300" :class="choice==='cyan'&&'selected'"></button><button @click="choice='gold'" class="crystal bg-gradient-to-b from-yellow-100 to-amber-700 text-amber-300" :class="choice==='gold'&&'selected'"></button></div><p class="mt-8 text-sm sm:text-base text-slate-400">Violeta: estable · Cian: arriesgado · Oro: extremo</p></div>
            @endif
        </div>

        <div x-show="result" x-transition class="text-center mt-4" aria-live="polite"><p class="text-2xl font-black" :class="result.ganancia>0?'text-emerald-400':'text-red-400'" x-text="result.ganancia>0 ? `Premio bruto ${money(result.ganancia)} · ${result.multiplier}x` : 'Esta vez no hubo premio'"></p><p class="mt-1 text-xs font-bold" :class="netResult>0?'text-emerald-200':netResult===0?'text-amber-200':'text-slate-400'" x-text="netMessage"></p></div>
        <p x-show="error" class="text-center text-red-400 mt-4" x-text="error"></p>
        <div class="max-w-lg mx-auto mt-7 flex flex-col min-[420px]:flex-row gap-3"><input x-model.number="apuesta" type="number" min=".2" max="500" step=".2" class="flex-1 rounded-xl bg-white/5 border border-white/10 px-4 py-3 text-white"><button @click="play" :disabled="playing || apuesta>saldo || (mode==='keno'&&numbers.length!==5)" class="cta-shine w-full min-[420px]:w-auto px-8 py-3.5 rounded-xl bg-gradient-to-r from-fuchsia-500 to-brand-400 text-black font-extrabold disabled:opacity-40"><span x-text="playing?'Jugando...':'Jugar'"></span></button></div>
    </section>
</div>

@push('scripts')
<script>
function originalGame(){return{mode:@js($game['mode']),initialCard:@js($initialCard),saldo:{{ $gameBalance }},apuesta:2,roundBet:0,choice:@js(match($game['mode']){'dice'=>'high','hilo'=>'higher','coin'=>'heads','baccarat'=>'player','nebula'=>'violet','mines'=>3,default=>null}),numbers:[],playing:false,result:null,error:'',rotation:0,reducedMotion:window.matchMedia('(prefers-reduced-motion: reduce)').matches,
get netResult(){return Number(((Number(this.result?.ganancia)||0)-Number(this.roundBet)).toFixed(2))},get netMessage(){if(!this.result)return '';if(this.netResult>0)return `Ganancia neta +${this.money(this.netResult)}`;if(this.netResult===0)return 'Apuesta devuelta íntegramente';return `Resultado neto -${this.money(Math.abs(this.netResult))}`},money(v){return new Intl.NumberFormat('es-ES',{style:'currency',currency:'EUR'}).format(Number(v||0))},card(v){return !v?'?':({11:'J',12:'Q',13:'K',14:'A'})[v]||v},toggleNumber(n){if(this.playing)return;if(this.numbers.includes(n))this.numbers=this.numbers.filter(x=>x!==n);else if(this.numbers.length<5)this.numbers.push(n)},requestToken(){const webCrypto=globalThis.crypto;if(typeof webCrypto?.randomUUID==='function')return webCrypto.randomUUID();const bytes=new Uint8Array(16);webCrypto.getRandomValues(bytes);bytes[6]=(bytes[6]&15)|64;bytes[8]=(bytes[8]&63)|128;return Array.from(bytes,(b,i)=>([4,6,8,10].includes(i)?'-':'')+b.toString(16).padStart(2,'0')).join('')},async play(){if(this.playing)return;this.playing=true;this.roundBet=Number(this.apuesta);window.lootraAudio?.play('spin');this.result=null;this.error='';const requestToken=this.requestToken();if(this.mode==='wheel')this.rotation+=1800+Math.random()*360;try{const r=await fetch(@js(route('games.originals.play',['game'=>$game['slug']])),{method:'POST',headers:{'Content-Type':'application/json','X-CSRF-TOKEN':document.querySelector('meta[name="csrf-token"]').content,'Accept':'application/json'},body:JSON.stringify({apuesta:this.apuesta,choice:this.choice,numbers:this.numbers,request_token:requestToken})});const d=await r.json();if(!r.ok)throw new Error(d.message||'No se pudo completar la partida.');const revealDelay=this.reducedMotion?30:(this.mode==='wheel'?2200:800);await new Promise(x=>setTimeout(x,revealDelay));this.result=d;if(this.mode==='plinko'&&!this.reducedMotion)await new Promise(x=>setTimeout(x,1800));this.saldo=Number(d.saldo);Alpine.store('wallet').saldo=this.saldo;window.lootraAudio?.play(Number(d.ganancia)>0?(Number(d.ganancia)>=Number(this.roundBet)*10?'jackpot':'win'):'lose');window.dispatchEvent(new CustomEvent('saldo-updated',{detail:{saldo:this.saldo}}))}catch(e){this.error=e.message;window.lootraAudio?.play('error')}this.playing=false}}}
</script>
@endpush
@endsection
