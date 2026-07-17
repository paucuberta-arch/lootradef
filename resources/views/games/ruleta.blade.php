@extends('layouts.game')
@section('title', $gameName . ' — Lootra Casino')

@section('styles')
<style>
    @keyframes result-in{from{opacity:0;transform:translateY(8px) scale(.96)}to{opacity:1;transform:none}}
    @keyframes winning-glow{0%,100%{box-shadow:0 0 0 1px #ffffff16,0 0 18px transparent}50%{box-shadow:0 0 0 2px #fde68a,0 0 28px #fbbf2477}}
    .roulette-camera{width:min(100%,390px);aspect-ratio:1;margin:auto;perspective:900px;transition:transform .8s cubic-bezier(.16,1,.3,1);transform:rotateX(7deg) scale(.9)}
    .roulette-camera.zooming{transform:rotateX(3deg) scale(1.04)}
    .roulette-camera.holding{transform:rotateX(1deg) scale(1.08)}
    .roulette-shell{position:relative;width:100%;height:100%;border-radius:50%;background:radial-gradient(circle,#191107 0 42%,#5b2e0d 43% 54%,#1c0e05 55% 62%,#8b5423 63% 68%,#2a1206 69%);box-shadow:0 32px 60px #000b,inset 0 0 20px #f5c56c55,0 0 0 2px #d9a441;overflow:hidden;contain:layout paint}
    .roulette-shell::after{content:"";position:absolute;inset:2%;border-radius:50%;background:linear-gradient(115deg,#fff3 0 4%,transparent 18% 72%,#0008);pointer-events:none;z-index:15}
    .roulette-rotor{position:absolute;inset:10%;border-radius:50%;background:radial-gradient(circle,#f6d58b 0 5%,#8a531a 6% 15%,#2c1405 16% 34%,#d5a548 35% 38%,#111827 39% 69%,#d2a34b 70% 73%,#4a2409 74%);box-shadow:inset 0 0 32px #000,0 0 0 3px #e5b958,0 10px 30px #000b}
    .roulette-hub{position:absolute;z-index:3;inset:35%;border-radius:50%;background:radial-gradient(circle at 34% 28%,#fff1b9,#d19a38 25%,#6b390c 54%,#180b03 58%,#ba7b22 70%);box-shadow:inset 0 0 16px #0009,0 8px 20px #000}
    .pocket{position:absolute;z-index:4;left:50%;top:50%;width:22px;height:72px;margin-left:-11px;margin-top:-72px;transform-origin:50% 72px;clip-path:polygon(15% 0,85% 0,100% 100%,0 100%);border-left:1px solid #ffe9a488;border-right:1px solid #422306;display:flex;justify-content:center;padding-top:5px;font-size:9px;font-weight:950;text-shadow:0 1px 2px #000;box-shadow:inset 0 0 8px #0008}
    .pocket-red{background:linear-gradient(#dc2626,#761515)}.pocket-black{background:linear-gradient(#263248,#070b12)}.pocket-green{background:linear-gradient(#10b981,#065f46)}
    .pocket.winner{animation:winning-glow .75s ease-in-out 3;filter:brightness(1.4);z-index:6}
    .roulette-ball{position:absolute;left:50%;top:50%;z-index:12;width:16px;height:16px;margin:-8px;border-radius:50%;background:radial-gradient(circle at 30% 25%,#fff 0 18%,#edf2f7 34%,#a8b2c2 66%,#475569 100%);box-shadow:0 3px 7px #000,0 0 8px #fff8}
    .roulette-camera.is-spinning .roulette-rotor,.roulette-camera.is-spinning .roulette-ball{will-change:transform}
    .roulette-ball.travelling{box-shadow:0 3px 7px #000,0 0 13px #fff;filter:brightness(1.2)}
    .roulette-result{animation:result-in .65s cubic-bezier(.16,1,.3,1)}
    .lightning-stage .roulette-shell{box-shadow:0 45px 80px #000c,inset 0 0 22px #67e8f955,0 0 0 2px #818cf8,0 0 45px #22d3ee33}
    .number-cell{transition:transform .16s,border-color .16s,background-color .16s}
    @media (hover:hover){.number-cell:hover{transform:translateY(-2px)}}
    @media (max-width:639px){
        .roulette-camera.zooming{transform:rotateX(3deg) scale(1.02)}
        .roulette-camera.holding{transform:rotateX(1deg) scale(1.05)}
    }
</style>
@endsection

@section('game-content')
<div class="mx-auto max-w-[1450px] px-4 py-7 sm:px-6 sm:py-10" x-data="rouletteGame()">
    <header class="mb-6 flex flex-wrap items-center justify-between gap-4">
        <div><div class="flex items-center gap-3"><span class="grid h-11 w-11 place-items-center rounded-xl bg-gradient-to-br {{ $variant === 'lightning' ? 'from-cyan-400 to-violet-600' : 'from-amber-300 to-red-600' }} shadow-lg">◆</span><div><h1 class="game-heading font-extrabold">{{ $gameName }}</h1><p class="mt-1 text-sm text-slate-500">Elige una casilla, confirma el boleto y sigue la bola</p></div></div></div>
        <div class="flex w-full flex-wrap gap-2 sm:w-auto"><a href="{{ route('games.roulette.european') }}" class="rounded-xl border px-3 py-2 text-xs font-bold {{ $variant === 'european' ? 'border-amber-400/40 bg-amber-400/10 text-amber-300' : 'border-white/10 text-slate-400' }}">Europea</a><a href="{{ route('games.roulette.lightning') }}" class="rounded-xl border px-3 py-2 text-xs font-bold {{ $variant === 'lightning' ? 'border-cyan-400/40 bg-cyan-400/10 text-cyan-300' : 'border-white/10 text-slate-400' }}">Lightning</a><div class="rounded-xl border border-white/10 bg-white/5 px-3 sm:px-4 py-2 text-sm whitespace-nowrap"><span class="text-slate-500">Saldo</span> <b class="ml-1 text-brand-400" x-text="money(saldo)"></b></div></div>
    </header>

    <div class="grid gap-6 lg:grid-cols-[minmax(300px,.82fr)_minmax(0,1.18fr)] xl:grid-cols-[minmax(360px,.82fr)_minmax(520px,1.18fr)_290px]">
        <section class="game-stage rounded-[1.75rem] border border-white/5 bg-white/[.03] p-4 sm:p-7 {{ $variant === 'lightning' ? 'lightning-stage' : '' }}">
            @php($wheelOrder = $rouletteConfig['wheel_order'])
            @php($redNumbers = $rouletteConfig['red_numbers'])
            <div class="roulette-camera" x-ref="camera" :class="[cameraStage===1?'zooming':cameraStage===2?'holding':'',spinning?'is-spinning':'']">
                <div class="roulette-shell" x-ref="shell">
                    <div class="roulette-rotor" x-ref="rotor">
                        @foreach($wheelOrder as $index => $number)
                            <div data-pocket="{{ $number }}" class="pocket {{ $number === 0 ? 'pocket-green' : (in_array($number, $redNumbers) ? 'pocket-red' : 'pocket-black') }}" style="transform:rotate({{ $index * (360 / 37) }}deg)"><span>{{ $number }}</span></div>
                        @endforeach
                        <div class="roulette-hub"></div>
                    </div>
                    <div class="roulette-ball" x-ref="ball"></div>
                </div>
            </div>
            <div class="mt-3 min-h-20 text-center">
                <p class="text-xs font-black uppercase tracking-[.2em]" :class="spinning?'text-cyan-300':'text-slate-500'" x-text="statusText"></p>
                <div x-show="resultVisible" class="roulette-result mt-3"><b class="text-xl">Número ganador: <span :class="lastColor==='rojo'?'text-red-400':lastColor==='negro'?'text-slate-300':'text-emerald-400'" x-text="lastNumero"></span></b><p class="mt-1 text-sm capitalize text-slate-400" x-text="lastColor"></p><p class="mt-1 text-sm" :class="ganancia>0?'text-emerald-300':'text-slate-500'" x-text="ganancia>0?'Premio '+money(ganancia):'La próxima puede ser la tuya'"></p></div>
            </div>
        </section>

        <section class="rounded-[1.75rem] border border-white/5 bg-[#0e1424] p-4 sm:p-7">
            <div class="mb-5 flex flex-wrap items-start justify-between gap-3"><div><p class="text-[10px] font-black uppercase tracking-[.2em] text-cyan-400">Paso 1</p><h2 class="mt-1 text-xl font-black">Elige dónde apostar</h2></div><span class="max-w-full rounded-lg bg-white/5 px-3 py-2 text-xs text-slate-400 break-words">Seleccionado: <b class="text-white" x-text="selectionLabel"></b></span></div>
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

        <aside class="space-y-5 lg:col-span-2 xl:col-span-1">
            <section class="rounded-2xl border border-white/10 bg-white/[.035] p-5"><p class="text-[10px] font-black uppercase tracking-[.2em] text-brand-400">Paso 2</p><h2 class="mt-1 text-lg font-black">Confirma tu boleto</h2><div class="mt-4 rounded-xl border border-white/10 bg-black/20 p-3"><p class="text-xs text-slate-500">Tu selección</p><b class="mt-1 block text-cyan-300" x-text="selectionLabel"></b></div><label class="mt-4 block text-xs text-slate-500">Importe de la apuesta</label><div class="mt-2 flex items-center rounded-xl border border-white/10 bg-black/20 px-3"><span class="text-slate-500">€</span><input x-model.number="apuesta" type="number" min=".1" max="500" step=".1" :disabled="spinning" class="w-full bg-transparent px-2 py-3 font-bold outline-none"></div><div class="mt-2 grid grid-cols-4 gap-1"><template x-for="v in [1,5,10,25]"><button @click="apuesta=v" class="rounded-lg bg-white/5 py-2 text-xs text-slate-400 hover:text-white" x-text="v+'€'"></button></template></div><button @click="play" :disabled="spinning||apuesta>saldo||!apuesta" class="mt-4 w-full rounded-xl bg-gradient-to-r {{ $variant === 'lightning' ? 'from-cyan-400 to-violet-500' : 'from-amber-300 to-orange-500' }} py-3.5 font-black text-slate-950 shadow-lg disabled:opacity-40" x-text="spinning?'La bola está girando…':'Girar ruleta'"></button><p x-show="error" class="mt-3 text-center text-xs text-red-300" x-text="error"></p></section>
            @if($variant === 'lightning')<section class="rounded-2xl border border-cyan-400/20 bg-cyan-400/[.06] p-4"><h3 class="text-sm font-bold text-cyan-300">⚡ Números Lightning</h3><p class="mt-2 text-xs leading-relaxed text-slate-400">En cada giro se cargan cinco números con multiplicadores de x50 a x500. Se revelan con el resultado.</p><div x-show="Object.keys(multipliers).length" class="mt-3 flex flex-wrap gap-1"><template x-for="(boost,n) in multipliers"><span class="rounded-lg bg-violet-500/15 px-2 py-1 text-xs text-violet-300" x-text="n+' · x'+boost"></span></template></div></section>@endif
            <section class="rounded-2xl border border-white/10 bg-white/[.035] p-5"><h3 class="mb-3 text-sm font-black uppercase tracking-wider">Últimos giros</h3><div class="space-y-2"><template x-for="h in historial.slice(0,8)"><div class="flex items-center text-xs"><span class="grid h-7 w-7 place-items-center rounded-full font-black" :class="resultClass(h.color)" x-text="h.numero"></span><span class="ml-2 text-slate-500" x-text="h.tipo"></span><b class="ml-auto" :class="h.ganancia>0?'text-emerald-300':'text-slate-600'" x-text="h.ganancia>0?'+'+money(h.ganancia):'-'+money(h.apuesta)"></b></div></template><p x-show="!historial.length" class="py-3 text-center text-xs text-slate-600">Todavía no hay giros.</p></div></section>
        </aside>
    </div>
</div>

@push('scripts')
<script>
function rouletteGame(){return{
    saldo:{{ Auth::user()?->cartera?->saldo ?? 0 }},apuesta:1,tipo:'rojo',valor:null,spinning:false,ganancia:0,lastNumero:null,lastColor:null,cameraStage:0,resultVisible:false,error:'',multipliers:{},wheelRotation:0,
    wheelOrder:@js($rouletteConfig['wheel_order']),
    historial:@js($partidas->map(fn($p)=>['numero'=>$p->detalles['numero']??0,'color'=>$p->detalles['color']??'verde','tipo'=>$p->detalles['tipo_apuesta']??'','ganancia'=>(float)$p->ganancia,'apuesta'=>(float)$p->apuesta])->all()),
    red:@js($rouletteConfig['red_numbers']),animations:[],spinSequence:0,reducedMotion:window.matchMedia('(prefers-reduced-motion: reduce)').matches,
    get selectionLabel(){if(this.tipo==='numero')return `Número ${this.valor}`;return {rojo:'Rojo',negro:'Negro',par:'Par',impar:'Impar',docena1:'Primera docena',docena2:'Segunda docena',docena3:'Tercera docena'}[this.tipo]},
    get statusText(){return this.spinning?(this.cameraStage===0?'La pelota recorre el carril exterior…':this.cameraStage===1?'La pelota pierde velocidad y rebota…':'Confirmando la casilla ganadora…'):(this.lastNumero===null?'Selecciona una apuesta para empezar':'Resultado confirmado')},
    select(tipo,valor=null){if(this.spinning)return;this.tipo=tipo;this.valor=valor},selected(t,v=null){return this.tipo===t&&(t!=='numero'||this.valor===v)},
    numberClass(n){const active=this.selected('numero',n);const red=this.red.includes(n);return [active?'ring-2 ring-cyan-300 border-cyan-300':'border-white/10',red?'bg-red-600/80 text-white':'bg-slate-800 text-white']},
    resultClass(c){return c==='rojo'?'bg-red-600 text-white':c==='negro'?'bg-slate-800 text-white border border-white/20':'bg-emerald-600 text-white'},money(v){return new Intl.NumberFormat('es-ES',{style:'currency',currency:'EUR'}).format(Number(v)||0)},
    wait(ms){return new Promise(resolve=>setTimeout(resolve,ms))},
    cancelAnimations(){this.animations.forEach(animation=>animation.cancel());this.animations=[];this.$refs.ball?.classList.remove('travelling')},
    destroy(){this.spinSequence++;this.cancelAnimations()},
    async animateSpin(number){
        const sequence=++this.spinSequence;
        this.cancelAnimations();
        const duration=this.reducedMotion?650:4200+Math.floor(Math.random()*700),wheelTurns=this.reducedMotion?1:5+Math.random()*1.5,ballTurns=this.reducedMotion?2:8+Math.random()*2;
        const wheelStart=((this.wheelRotation%360)+360)%360,wheelEnd=wheelStart+wheelTurns*360+(Math.random()*8-4),pocketIndex=this.wheelOrder.indexOf(Number(number));
        const pocketAngle=pocketIndex*(360/37),targetScreen=((pocketAngle+wheelEnd)%360+360)%360;
        const startAngle=Math.random()*360,outerEnd=startAngle-ballTurns*360;
        const finalAngle=outerEnd-(((outerEnd-targetScreen)%360)+360)%360,step=360/37;
        const size=this.$refs.shell.getBoundingClientRect().width,outer=size*.445,drop=size*.345,bounce=size*.325,settled=size*.315;
        this.$root.querySelectorAll('.pocket.winner').forEach(el=>el.classList.remove('winner'));
        this.$refs.ball.classList.add('travelling');
        const wheelAnimation=this.$refs.rotor.animate([
            {transform:`rotate(${wheelStart}deg)`,offset:0,easing:'cubic-bezier(.16,.62,.25,1)'},
            {transform:`rotate(${wheelEnd-18}deg)`,offset:.84,easing:'cubic-bezier(.1,.55,.2,1)'},
            {transform:`rotate(${wheelEnd}deg)`,offset:1,easing:'ease-out'}
        ],{duration,fill:'forwards'});
        const ballAnimation=this.$refs.ball.animate([
            {transform:`rotate(${startAngle}deg) translateY(-${outer}px) scale(1)`,offset:0,easing:'linear'},
            {transform:`rotate(${startAngle-ballTurns*250}deg) translateY(-${outer}px) scale(1.03)`,offset:.52,easing:'linear'},
            {transform:`rotate(${outerEnd}deg) translateY(-${drop}px) scale(1)`,offset:.69,easing:'cubic-bezier(.22,.7,.3,1)'},
            {transform:`rotate(${finalAngle+step*4.2}deg) translateY(-${bounce}px) scale(.97)`,offset:.79},
            {transform:`rotate(${finalAngle+step*2.5}deg) translateY(-${settled*.96}px) scale(1.08)`,offset:.84},
            {transform:`rotate(${finalAngle+step*1.45}deg) translateY(-${bounce}px) scale(.94)`,offset:.88},
            {transform:`rotate(${finalAngle+step*.72}deg) translateY(-${settled*.94}px) scale(1.05)`,offset:.92},
            {transform:`rotate(${finalAngle+step*.25}deg) translateY(-${bounce}px) scale(.98)`,offset:.96},
            {transform:`rotate(${finalAngle}deg) translateY(-${settled}px) scale(1)`,offset:1,easing:'ease-out'}
        ],{duration,fill:'forwards'});
        this.animations=[wheelAnimation,ballAnimation];
        const completion=Promise.allSettled([wheelAnimation.finished,ballAnimation.finished]);
        await this.wait(duration*.66);
        if(sequence!==this.spinSequence)return false;
        this.cameraStage=1;
        await completion;
        if(sequence!==this.spinSequence)return false;
        const normalizedWheel=((wheelEnd%360)+360)%360,normalizedBall=((finalAngle%360)+360)%360;
        this.$refs.rotor.style.transform=`rotate(${normalizedWheel}deg)`;
        this.$refs.ball.style.transform=`rotate(${normalizedBall}deg) translateY(-${settled}px)`;
        this.animations.forEach(animation=>animation.cancel());this.animations=[];
        this.wheelRotation=normalizedWheel;this.cameraStage=2;this.$refs.ball.classList.remove('travelling');
        this.$root.querySelector(`[data-pocket="${number}"]`)?.classList.add('winner');
        return true;
    },
    async play(){if(this.spinning||this.apuesta>this.saldo)return;this.spinning=true;this.cameraStage=0;this.resultVisible=false;this.lastNumero=null;this.ganancia=0;this.error='';try{const r=await fetch(@js($playRoute),{method:'POST',headers:{'Content-Type':'application/json','Accept':'application/json','X-CSRF-TOKEN':document.querySelector('meta[name="csrf-token"]').content},body:JSON.stringify({apuesta:this.apuesta,tipo:this.tipo,valor:this.valor})});const d=await r.json();if(!r.ok)throw new Error(d.error||d.message||'No se pudo completar el giro.');const landed=await this.animateSpin(d.numero);if(!landed)return;this.lastNumero=d.numero;this.lastColor=d.color;this.multipliers=d.multipliers||{};this.ganancia=Number(d.ganancia);this.saldo=Number(d.saldo);this.$store.wallet.saldo=this.saldo;this.historial.unshift({numero:d.numero,color:d.color,tipo:this.selectionLabel,ganancia:this.ganancia,apuesta:this.apuesta});this.resultVisible=true;window.dispatchEvent(new CustomEvent('saldo-updated',{detail:{saldo:this.saldo}}));await this.wait(this.reducedMotion?80:550);}catch(e){if(e.name!=='AbortError')this.error=e.message;this.cancelAnimations();}finally{this.cameraStage=0;this.spinning=false;}}
}}
</script>
@endpush
@endsection
