@extends('layouts.game')
@section('title', $gameName . ' — Lootra Casino')

@section('styles')
<style>
    @keyframes result-in{from{opacity:0;transform:translateY(8px) scale(.96)}to{opacity:1;transform:none}}
    @keyframes winning-glow{0%,100%{filter:brightness(1);box-shadow:inset 0 0 8px #0008}50%{filter:brightness(1.8);box-shadow:inset 0 0 8px #fff8,0 0 18px #fde68a}}
    @keyframes studio-sweep{0%,100%{opacity:.28;transform:translateX(-16%) rotate(8deg)}50%{opacity:.52;transform:translateX(16%) rotate(8deg)}}
    @keyframes rim-breathe{0%,100%{filter:drop-shadow(0 24px 28px #000a) brightness(.96)}50%{filter:drop-shadow(0 30px 38px #000d) brightness(1.06)}}
    .roulette-stage{isolation:isolate;overflow:hidden;min-height:590px;background:linear-gradient(180deg,rgba(3,5,8,.52),rgba(3,5,8,.88)),url('/images/lootra_visual_pack/realista/optimized/roulette-table.webp') center bottom/cover no-repeat,#050806}
    .roulette-stage::before{content:"";position:absolute;z-index:-1;inset:-20%;background:linear-gradient(100deg,transparent 32%,rgba(255,224,153,.12) 46%,transparent 58%);pointer-events:none;animation:studio-sweep 9s ease-in-out infinite}
    .roulette-stage::after{content:"LOOTRA  •  EUROPEAN SERIES";position:absolute;left:50%;bottom:1.15rem;transform:translateX(-50%);color:rgba(244,216,151,.42);font-size:.58rem;font-weight:900;letter-spacing:.32em;white-space:nowrap}
    .roulette-studio-bar{position:relative;z-index:4;display:flex;align-items:center;justify-content:space-between;gap:.75rem;margin:0 auto .2rem;width:min(100%,470px);color:rgba(244,231,193,.5);font-size:.58rem;font-weight:900;letter-spacing:.16em;text-transform:uppercase}
    .roulette-studio-pill{display:inline-flex;align-items:center;gap:.45rem;border:1px solid rgba(241,210,139,.22);border-radius:999px;background:rgba(4,7,9,.6);padding:.36rem .6rem;color:#edd99e;box-shadow:inset 0 1px rgba(255,255,255,.08)}
    .roulette-studio-pill i{display:block;width:.38rem;height:.38rem;border-radius:50%;background:#f59e0b;box-shadow:0 0 0 3px rgba(245,158,11,.12),0 0 12px rgba(245,158,11,.75)}
    .roulette-camera{position:relative;z-index:1;width:min(100%,470px);aspect-ratio:1;margin:1.5rem auto .5rem;perspective:1200px;transition:transform .9s cubic-bezier(.16,1,.3,1);transform:rotateX(13deg) rotateZ(-2deg) scale(.92);animation:rim-breathe 5s ease-in-out infinite}
    .roulette-camera::before{content:"";position:absolute;z-index:-1;left:9%;right:9%;bottom:-5%;height:18%;border-radius:50%;background:#000;filter:blur(14px);opacity:.72;transform:rotateX(65deg)}
    .roulette-camera.zooming{transform:rotateX(7deg) rotateZ(0) scale(1.035)}
    .roulette-camera.holding{transform:rotateX(4deg) rotateZ(0) scale(1.075)}
    .roulette-shell{position:relative;width:100%;height:100%;border-radius:50%;background:radial-gradient(circle,#180a07 0 54%,#d3a84e 55% 57%,#351208 58% 69%,#925025 70% 79%,#e1b767 80% 82%,#311008 83% 91%,#a3612e 92% 96%,#1b0805 97%);box-shadow:0 34px 64px #000c,inset 0 0 24px #f5c56c66,0 0 0 2px #e7c477,0 0 0 7px #351208;overflow:hidden;contain:layout paint}
    .roulette-shell::before{content:"";position:absolute;inset:0;border-radius:50%;background:repeating-conic-gradient(from 8deg,rgba(255,255,255,.08) 0 3deg,rgba(43,10,5,.13) 3deg 8deg);mask-image:radial-gradient(circle,transparent 0 68%,#000 69% 100%);pointer-events:none;z-index:1}
    .roulette-shell::after{content:"";position:absolute;inset:1.5%;border-radius:50%;background:linear-gradient(120deg,rgba(255,255,255,.3) 0 3%,transparent 17% 72%,rgba(0,0,0,.58));pointer-events:none;z-index:15}
    .roulette-ball-track{position:absolute;z-index:2;inset:4.8%;border-radius:50%;border:clamp(7px,2.4vw,11px) solid #150b08;box-shadow:inset 0 0 0 2px #c99a4b,inset 0 0 15px #000,0 0 0 2px #5f3519;pointer-events:none}
    .roulette-rotor{position:absolute;z-index:3;inset:12%;border-radius:50%;background:radial-gradient(circle,#f7dda2 0 3%,#9a5d1d 4% 11%,#2b1007 12% 30%,#d6aa4d 31% 34%,#160907 35% 91%,#e1bd69 92% 96%,#5a2e0f 97%);box-shadow:inset 0 0 28px #000,0 0 0 2px #f0d48b,0 10px 30px #000c}
    .roulette-hub{position:absolute;z-index:8;inset:31%;display:grid;place-items:center;border-radius:50%;background:radial-gradient(circle at 35% 28%,#fff2bd 0 8%,#d4a347 24%,#6d380f 51%,#1d0b04 55%,#b87525 72%,#4b2009 82%);border:2px solid #f4d68b;box-shadow:inset 0 0 18px #000b,0 8px 24px #000}
    .roulette-spindle{position:relative;z-index:1;display:grid;width:31%;aspect-ratio:1;place-items:center;border-radius:50%;background:radial-gradient(circle at 35% 30%,#fffbe1,#e2b85c 24%,#795017 62%,#2b1405);box-shadow:0 3px 9px #000,0 0 0 2px #e8c77a;color:#3a2108;font:950 clamp(10px,2.6vw,16px) "Space Grotesk"}
    .roulette-spindle::before,.roulette-spindle::after{content:"";position:absolute;z-index:-1;left:50%;top:50%;width:310%;height:20%;border-radius:99px;background:linear-gradient(#fff0b6,#9a621f 45%,#3f1c08 55%,#d6a64c);box-shadow:0 3px 5px #0009;transform:translate(-50%,-50%)}
    .roulette-spindle::after{transform:translate(-50%,-50%) rotate(90deg)}
    .pocket{position:absolute;z-index:4;left:50%;top:50%;display:flex;width:6.7%;height:47%;margin-left:-3.35%;margin-top:-47%;transform-origin:50% 100%;clip-path:polygon(0 0,100% 0,58% 100%,42% 100%);justify-content:center;padding-top:3.5%;border-left:1px solid rgba(255,232,166,.65);border-right:1px solid rgba(31,10,5,.8);font-size:clamp(6px,2vw,9px);font-weight:950;line-height:1;text-shadow:0 1px 2px #000;box-shadow:inset 0 5px 9px #0005}
    .pocket span{display:block}
    .pocket-red{background:linear-gradient(#e34442 0 18%,#9f1f21 38%,#5d1114 100%)}.pocket-black{background:linear-gradient(#3b4654 0 18%,#171c24 40%,#05070b 100%)}.pocket-green{background:linear-gradient(#2bc68b 0 18%,#08754f 42%,#043424 100%)}
    .pocket.winner{animation:winning-glow .65s ease-in-out 4;z-index:6}
    .roulette-ball{--ball-size:clamp(12px,4.1%,17px);position:absolute;left:50%;top:50%;z-index:12;width:var(--ball-size);aspect-ratio:1;margin:calc(var(--ball-size) * -.5);border-radius:50%;background:radial-gradient(circle at 30% 25%,#fff 0 18%,#edf2f7 34%,#a8b2c2 66%,#475569 100%);box-shadow:0 3px 7px #000,0 0 8px #fff8}
    .roulette-marker{position:absolute;z-index:20;left:50%;top:-1.5%;width:9%;aspect-ratio:.8;transform:translateX(-50%);clip-path:polygon(50% 100%,0 32%,18% 8%,82% 8%,100% 32%);background:linear-gradient(90deg,#704114,#fff0ad 45%,#c48b32 60%,#4d2709);filter:drop-shadow(0 5px 4px #000b)}
    .roulette-edition{position:relative;z-index:1;margin:-.15rem auto 0;width:max-content;max-width:100%;border:1px solid rgba(231,196,119,.25);border-radius:99px;background:rgba(5,8,15,.72);padding:.4rem .75rem;color:#c9b98e;font-size:.62rem;font-weight:800;letter-spacing:.16em;text-transform:uppercase}
    .roulette-camera.is-spinning .roulette-rotor,.roulette-camera.is-spinning .roulette-ball{will-change:transform}
    .roulette-ball.travelling{box-shadow:0 3px 7px #000,0 0 13px #fff;filter:brightness(1.2)}
    .roulette-result{animation:result-in .65s cubic-bezier(.16,1,.3,1);text-shadow:0 4px 24px #000}
    .roulette-result--win{filter:drop-shadow(0 0 16px rgba(52,211,153,.18))}.roulette-result--jackpot{filter:drop-shadow(0 0 20px rgba(242,205,117,.35))}.roulette-result--return{opacity:.9}
    .roulette-status-glass{position:relative;z-index:2;margin:.75rem auto 1.4rem;width:min(94%,410px);border:1px solid rgba(255,232,179,.16);border-radius:1rem;background:linear-gradient(135deg,rgba(5,8,10,.84),rgba(15,10,8,.58));padding:.8rem 1rem;box-shadow:0 14px 35px #0008,inset 0 1px #fff1;backdrop-filter:blur(14px)}
    .roulette-console{position:relative;overflow:hidden;background:linear-gradient(155deg,rgba(17,25,28,.98),rgba(6,10,13,.98));box-shadow:0 26px 70px #0008,inset 0 1px #fff1}
    .roulette-console::before{content:"";position:absolute;inset:0;background:linear-gradient(rgba(6,10,12,.74),rgba(6,10,12,.9)),url('/images/lootra_visual_pack/realista/optimized/betting-layout.webp') center 44%/115% auto no-repeat;opacity:.3;pointer-events:none}
    .roulette-console>*{position:relative}
    .roulette-ticket{background:linear-gradient(145deg,rgba(20,26,31,.96),rgba(7,10,13,.96));box-shadow:0 20px 55px #0007,inset 0 1px #fff1}
    .casino-chip-button{position:relative;isolation:isolate;transition:transform .18s ease,filter .18s ease;color:#f8e7ba!important;background:radial-gradient(circle at 35% 30%,#4c3a19,#17130c 55%,#050505 57%)!important;border:2px dashed rgba(244,207,120,.42);box-shadow:0 6px 13px #0008,inset 0 0 0 3px #080808}
    .casino-chip-button:hover{transform:translateY(-3px) rotate(-2deg);filter:brightness(1.18)}
    .casino-chip-button--ivory{background:radial-gradient(circle at 35% 30%,#fff7db,#c9b98e 52%,#5e4b27 55%)!important;color:#221708!important;border-color:#fff1b8}
    .casino-chip-button--red{background:radial-gradient(circle at 35% 30%,#ff8b82,#c73537 52%,#511014 55%)!important;border-color:#ffaaa0}
    .casino-chip-button--blue{background:radial-gradient(circle at 35% 30%,#9ee7ff,#3473b8 52%,#101f4e 55%)!important;border-color:#a9efff}
    .casino-chip-button--gold{background:radial-gradient(circle at 35% 30%,#fff1ad,#d6a84b 52%,#5a330d 55%)!important;border-color:#ffe7a2;color:#271607!important}
    .lightning-stage{background:linear-gradient(180deg,rgba(3,5,18,.12),rgba(3,5,18,.76)),url('/images/lootra_visual_pack/realista/optimized/roulette-table.webp') center/cover no-repeat,#050718}
    .lightning-stage .roulette-shell{background:radial-gradient(circle,#0b1228 0 54%,#7dd3fc 55% 57%,#111938 58% 69%,#312e81 70% 79%,#a5f3fc 80% 82%,#11152f 83% 91%,#4338ca 92% 96%,#050816 97%);box-shadow:0 34px 64px #000c,inset 0 0 24px #67e8f977,0 0 0 2px #a5f3fc,0 0 32px #6366f144}
    .lightning-stage .roulette-ball-track{border-color:#090d22;box-shadow:inset 0 0 0 2px #67e8f9,inset 0 0 15px #000,0 0 0 2px #4338ca}
    .lightning-stage .roulette-hub{border-color:#a5f3fc;box-shadow:inset 0 0 18px #000b,0 0 22px #22d3ee55}
    .lightning-stage .roulette-marker{background:linear-gradient(90deg,#312e81,#cffafe 45%,#818cf8 62%,#1e1b4b)}
    .lightning-stage .roulette-edition{border-color:rgba(103,232,249,.3);color:#a5f3fc}
    .number-cell{transition:transform .16s,border-color .16s,background-color .16s}
    @media (hover:hover){.number-cell:hover{transform:translateY(-2px)}}
    @media (max-width:639px){
        .roulette-stage{min-height:500px;padding-inline:.75rem}
        .roulette-studio-bar{font-size:.48rem;letter-spacing:.1em}
        .roulette-camera{width:min(100%,350px)}
        .roulette-camera.zooming{transform:rotateX(3deg) scale(1.02)}
        .roulette-camera.holding{transform:rotateX(1deg) scale(1.05)}
        .roulette-edition{font-size:.55rem;letter-spacing:.12em}
    }
    @media (prefers-reduced-motion:reduce){.roulette-stage::before,.roulette-camera{animation:none}}
</style>
@endsection

@section('game-content')
<div class="mx-auto max-w-[1400px] px-4 pt-5 sm:px-6"><x-campaign.rickyedit.sidebar /></div>
<div class="mx-auto max-w-[1450px] px-4 py-7 sm:px-6 sm:py-10" x-data="rouletteGame()">
    <header class="mb-6 flex flex-wrap items-center justify-between gap-4">
        <div><div class="flex items-center gap-3"><span class="grid h-11 w-11 place-items-center rounded-xl bg-gradient-to-br {{ $variant === 'lightning' ? 'from-cyan-400 to-violet-600' : 'from-amber-300 to-red-600' }} shadow-lg">◆</span><div><h1 class="game-heading font-extrabold">{{ $gameName }}</h1><p class="mt-1 text-sm text-slate-500">Elige una casilla, confirma el boleto y sigue la bola</p></div></div></div>
        <div class="flex w-full flex-wrap gap-2 sm:w-auto"><a href="{{ route('games.roulette.european') }}" class="rounded-xl border px-3 py-2 text-xs font-bold {{ $variant === 'european' ? 'border-amber-400/40 bg-amber-400/10 text-amber-300' : 'border-white/10 text-slate-400' }}">Europea</a><a href="{{ route('games.roulette.lightning') }}" class="rounded-xl border px-3 py-2 text-xs font-bold {{ $variant === 'lightning' ? 'border-cyan-400/40 bg-cyan-400/10 text-cyan-300' : 'border-white/10 text-slate-400' }}">Lightning</a><div class="rounded-xl border border-white/10 bg-white/5 px-3 sm:px-4 py-2 text-sm whitespace-nowrap"><span class="text-slate-500">Saldo</span> <b class="ml-1 text-brand-400" x-text="money(saldo)"></b></div></div>
    </header>

    <div class="grid gap-6 lg:grid-cols-[minmax(390px,1.08fr)_minmax(420px,.92fr)]">
        <section class="roulette-stage game-stage rounded-[1.75rem] border border-amber-200/10 p-4 sm:p-7 {{ $variant === 'lightning' ? 'lightning-stage' : '' }}" role="region" aria-label="Mesa de ruleta">
            @php($wheelOrder = $rouletteConfig['wheel_order'])
            @php($redNumbers = $rouletteConfig['red_numbers'])
            <div class="roulette-studio-bar">
                <span class="roulette-studio-pill"><i aria-hidden="true"></i> Studio table</span>
                <span class="hidden sm:inline">Resultado validado · {{ $variant === 'lightning' ? 'Lightning' : 'Europea' }}</span>
            </div>
            <div class="roulette-camera" x-ref="camera" :class="[cameraStage===1?'zooming':cameraStage===2?'holding':'',spinning?'is-spinning':'']">
                <div class="roulette-marker" aria-hidden="true"></div>
                <div class="roulette-shell" x-ref="shell">
                    <div class="roulette-ball-track" aria-hidden="true"></div>
                    <div class="roulette-rotor" x-ref="rotor">
                        @foreach($wheelOrder as $index => $number)
                            <div data-pocket="{{ $number }}" class="pocket {{ $number === 0 ? 'pocket-green' : (in_array($number, $redNumbers) ? 'pocket-red' : 'pocket-black') }}" style="transform:rotate({{ $index * (360 / 37) }}deg)"><span>{{ $number }}</span></div>
                        @endforeach
                        <div class="roulette-hub"><span class="roulette-spindle">L</span></div>
                    </div>
                    <div class="roulette-ball" x-ref="ball"></div>
                </div>
            </div>
            <div class="roulette-edition">{{ $variant === 'lightning' ? 'Lightning multipliers · single zero' : 'European wheel · single zero' }}</div>
            <div class="roulette-status-glass min-h-20 text-center" aria-live="polite">
                <p class="text-xs font-black uppercase tracking-[.2em]" :class="spinning?'text-cyan-300':'text-slate-500'" x-text="statusText"></p>
                <div x-show="resultVisible" class="roulette-result mt-3" :class="'roulette-result--' + resultTier"><b class="text-xl">Número ganador: <span :class="lastColor==='rojo'?'text-red-400':lastColor==='negro'?'text-slate-300':'text-emerald-400'" x-text="lastNumero"></span></b><p class="mt-1 text-sm capitalize text-slate-400" x-text="lastColor"></p><p class="mt-1 text-sm" :class="ganancia>0?'text-emerald-300':'text-slate-500'" x-text="ganancia>0?'Premio bruto '+money(ganancia):'La próxima puede ser la tuya'"></p><p class="mt-1 text-xs font-bold" :class="netResult>0?'text-emerald-200':netResult===0?'text-amber-200':'text-slate-400'" x-text="netMessage"></p></div>
            </div>
        </section>

        <section class="roulette-console rounded-[1.75rem] border border-white/10 p-4 sm:p-7">
            <div class="mb-5 flex flex-wrap items-start justify-between gap-3"><div><p class="text-[10px] font-black uppercase tracking-[.2em] text-cyan-400">Paso 1</p><h2 class="mt-1 text-xl font-black">Elige dónde apostar</h2></div><span class="max-w-full rounded-lg bg-white/5 px-3 py-2 text-xs text-slate-400 break-words">Seleccionado: <b class="text-white" x-text="selectionLabel"></b></span></div>
            <div class="mb-5 grid grid-cols-3 gap-2">
                <button @click="select('rojo')" :class="selected('rojo')?'ring-2 ring-white':''" class="rounded-xl bg-red-600 py-3 font-black">Rojo <small class="block opacity-70">x2</small></button>
                <button @click="select('negro')" :class="selected('negro')?'ring-2 ring-white':''" class="rounded-xl bg-slate-800 py-3 font-black">Negro <small class="block opacity-70">x2</small></button>
                <button @click="select('numero',0)" :class="selected('numero',0)?'ring-2 ring-white':''" class="rounded-xl bg-emerald-700 py-3 font-black">Cero <small class="block opacity-70">x35</small></button>
            </div>
            <div class="mb-3 flex items-center justify-between"><div><p class="text-[10px] font-black uppercase tracking-[.2em] text-fuchsia-400">Número exacto</p><h3 class="font-bold">Pulsa directamente del 1 al 36</h3></div><span class="rounded-lg bg-fuchsia-500/10 px-2 py-1 text-xs font-bold text-fuchsia-300">Paga x35</span></div>
            <div class="grid grid-cols-6 gap-1.5 rounded-2xl border border-white/5 bg-black/20 p-3">
                <template x-for="n in 36" :key="n"><button @click="select('numero',n)" class="number-cell aspect-square rounded-lg border text-xs font-black" :class="numberClass(n)" :aria-label="'Apostar al número '+n+' a x35'" x-text="n"></button></template>
            </div>
            <div class="mt-5 grid grid-cols-2 gap-2"><button @click="select('par')" :class="selected('par')?'border-brand-400 bg-brand-400/15 text-brand-300':'border-white/10 bg-white/5 text-slate-400'" class="rounded-xl border py-3 font-bold">Par · x2</button><button @click="select('impar')" :class="selected('impar')?'border-brand-400 bg-brand-400/15 text-brand-300':'border-white/10 bg-white/5 text-slate-400'" class="rounded-xl border py-3 font-bold">Impar · x2</button></div>
            <div class="mt-2 grid grid-cols-3 gap-2"><template x-for="d in [{id:'docena1',t:'1–12'},{id:'docena2',t:'13–24'},{id:'docena3',t:'25–36'}]"><button @click="select(d.id)" :class="selected(d.id)?'border-cyan-400 bg-cyan-400/15 text-cyan-300':'border-white/10 bg-white/5 text-slate-400'" class="rounded-xl border py-3 text-sm font-bold" x-text="d.t+' · x3'"></button></template></div>
        </section>

        <aside class="grid gap-5 sm:grid-cols-2 lg:col-span-2 xl:grid-cols-[1.1fr_.9fr]">
            <section class="roulette-ticket rounded-2xl border border-amber-200/10 p-5"><p class="text-[10px] font-black uppercase tracking-[.2em] text-brand-400">Mesa privada · Paso 2</p><h2 class="mt-1 text-lg font-black">Confirma tu boleto</h2><div class="mt-4 rounded-xl border border-white/10 bg-black/20 p-3"><p class="text-xs text-slate-500">Tu selección</p><b class="mt-1 block text-amber-200" x-text="selectionLabel"></b></div><label class="mt-4 block text-xs text-slate-500" for="roulette-bet">Importe de la apuesta</label><div class="mt-2 flex items-center rounded-xl border border-white/10 bg-black/30 px-3"><span class="text-amber-300">€</span><input id="roulette-bet" x-model.number="apuesta" type="number" min=".1" max="500" step=".1" :disabled="spinning" class="w-full bg-transparent px-2 py-3 font-bold outline-none"></div><div class="mt-3 grid grid-cols-4 gap-2"><template x-for="chip in [{value:1,tone:'ivory'},{value:5,tone:'red'},{value:10,tone:'blue'},{value:25,tone:'gold'}]" :key="chip.value"><button @click="apuesta=chip.value;window.lootraAudio?.play('select')" class="casino-chip-button aspect-square rounded-full text-xs font-black" :class="'casino-chip-button--'+chip.tone" x-text="chip.value+'€'"></button></template></div><button @click="play" :disabled="spinning||apuesta>saldo||!apuesta" class="mt-4 w-full rounded-xl bg-gradient-to-r {{ $variant === 'lightning' ? 'from-cyan-300 via-indigo-300 to-violet-400' : 'from-[#f5dc92] via-[#d7a94f] to-[#a65d22]' }} py-4 font-black uppercase tracking-[.12em] text-slate-950 shadow-[0_12px_30px_rgba(214,170,79,.22)] transition hover:brightness-110 disabled:opacity-40" x-text="spinning?'La bola está girando…':'Girar ruleta'"></button><p x-show="error" class="mt-3 text-center text-xs text-red-300" x-text="error"></p></section>
            @if($variant === 'lightning')<section class="rounded-2xl border border-cyan-400/20 bg-cyan-400/[.06] p-4"><h3 class="text-sm font-bold text-cyan-300">⚡ Números Lightning</h3><p class="mt-2 text-xs leading-relaxed text-slate-400">En cada giro se cargan cinco números con multiplicadores de x50 a x500. Se revelan con el resultado.</p><div x-show="Object.keys(multipliers).length" class="mt-3 flex flex-wrap gap-1"><template x-for="(boost,n) in multipliers"><span class="rounded-lg bg-violet-500/15 px-2 py-1 text-xs text-violet-300" x-text="n+' · x'+boost"></span></template></div></section>@endif
            <section class="rounded-2xl border border-white/10 bg-white/[.035] p-5"><h3 class="mb-3 text-sm font-black uppercase tracking-wider">Últimos giros</h3><div class="space-y-2"><template x-for="h in historial.slice(0,8)"><div class="flex items-center text-xs"><span class="grid h-7 w-7 place-items-center rounded-full font-black" :class="resultClass(h.color)" x-text="h.numero"></span><span class="ml-2 text-slate-500" x-text="h.tipo"></span><b class="ml-auto" :class="h.ganancia>0?'text-emerald-300':'text-slate-600'" x-text="h.ganancia>0?'+'+money(h.ganancia):'-'+money(h.apuesta)"></b></div></template><p x-show="!historial.length" class="py-3 text-center text-xs text-slate-600">Todavía no hay giros.</p></div></section>
        </aside>
    </div>
</div>

@push('scripts')
<script>
function rouletteGame(){return{
    saldo:{{ $gameBalance }},apuesta:1,roundBet:0,tipo:'rojo',valor:null,spinning:false,ganancia:0,lastNumero:null,lastColor:null,cameraStage:0,resultVisible:false,resultTier:'return',error:'',multipliers:{},wheelRotation:0,
    wheelOrder:@js($rouletteConfig['wheel_order']),
    historial:@js($partidas->map(fn($p)=>['numero'=>$p->detalles['numero']??0,'color'=>$p->detalles['color']??'verde','tipo'=>$p->detalles['tipo_apuesta']??'','ganancia'=>(float)$p->ganancia,'apuesta'=>(float)$p->apuesta])->all()),
    red:@js($rouletteConfig['red_numbers']),animations:[],spinSequence:0,reducedMotion:window.matchMedia('(prefers-reduced-motion: reduce)').matches,
    get selectionLabel(){if(this.tipo==='numero')return `Número ${this.valor}`;return {rojo:'Rojo',negro:'Negro',par:'Par',impar:'Impar',docena1:'Primera docena',docena2:'Segunda docena',docena3:'Tercera docena'}[this.tipo]},
    get statusText(){return this.spinning?(this.cameraStage===0?'La pelota recorre el carril exterior…':this.cameraStage===1?'La pelota pierde velocidad y rebota…':'Confirmando la casilla ganadora…'):(this.lastNumero===null?'Selecciona una apuesta para empezar':'Resultado confirmado')},
    get netResult(){return Number((this.ganancia-this.roundBet).toFixed(2))},
    get netMessage(){return this.netResult>0?'Ganancia neta +'+this.money(this.netResult):this.netResult===0?'Apuesta devuelta íntegramente':'Resultado neto -'+this.money(Math.abs(this.netResult))},
    select(tipo,valor=null){if(this.spinning)return;this.tipo=tipo;this.valor=valor;window.lootraAudio?.play('select')},selected(t,v=null){return this.tipo===t&&(t!=='numero'||this.valor===v)},
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
        window.lootraAudio?.play('land');
        const normalizedWheel=((wheelEnd%360)+360)%360,normalizedBall=((finalAngle%360)+360)%360;
        this.$refs.rotor.style.transform=`rotate(${normalizedWheel}deg)`;
        this.$refs.ball.style.transform=`rotate(${normalizedBall}deg) translateY(-${settled}px)`;
        this.animations.forEach(animation=>animation.cancel());this.animations=[];
        this.wheelRotation=normalizedWheel;this.cameraStage=2;this.$refs.ball.classList.remove('travelling');
        this.$root.querySelector(`[data-pocket="${number}"]`)?.classList.add('winner');
        return true;
    },
    async play(){if(this.spinning||this.apuesta>this.saldo)return;this.spinning=true;this.roundBet=Number(this.apuesta);window.lootraAudio?.play('spin');this.cameraStage=0;this.resultVisible=false;this.lastNumero=null;this.ganancia=0;this.error='';try{const r=await fetch(@js($playRoute),{method:'POST',headers:{'Content-Type':'application/json','Accept':'application/json','X-CSRF-TOKEN':document.querySelector('meta[name="csrf-token"]').content},body:JSON.stringify({apuesta:this.apuesta,tipo:this.tipo,valor:this.valor,request_token:window.lootraRequestToken()})});const d=await r.json();if(!r.ok)throw new Error(d.error||d.message||'No se pudo completar el giro.');const landed=await this.animateSpin(d.numero);if(!landed)return;this.lastNumero=d.numero;this.lastColor=d.color;this.multipliers=d.multipliers||{};this.ganancia=Number(d.ganancia);this.resultTier=this.ganancia>=this.roundBet*10?'jackpot':this.ganancia>0?'win':'return';this.saldo=Number(d.saldo);this.$store.wallet.saldo=this.saldo;this.historial.unshift({numero:d.numero,color:d.color,tipo:this.selectionLabel,ganancia:this.ganancia,apuesta:this.roundBet});this.resultVisible=true;window.lootraAudio?.play(this.ganancia>0?(this.ganancia>=this.roundBet*10?'jackpot':'win'):'lose');window.dispatchEvent(new CustomEvent('saldo-updated',{detail:{saldo:this.saldo}}));await this.wait(this.reducedMotion?80:550);}catch(e){if(e.name!=='AbortError')this.error=e.message;window.lootraAudio?.play('error');this.cancelAnimations();}finally{this.cameraStage=0;this.spinning=false;}}
}}
</script>
@endpush
@endsection
