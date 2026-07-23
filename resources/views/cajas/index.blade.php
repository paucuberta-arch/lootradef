@extends('layouts.app')

@section('title', 'Loot Boxes — Lootra Casino')

@section('styles')
<style>
    .case-hero { background: radial-gradient(circle at 75% 30%, rgba(168,85,247,.38), transparent 28rem), linear-gradient(125deg,#090719,#17103d 55%,#071827); }
    .case-card { --accent:#a855f7; transform-style:preserve-3d; transition:transform .45s cubic-bezier(.2,.8,.2,1),border-color .3s,box-shadow .3s; }
    .case-card:hover { transform:translateY(-10px) rotateX(2deg); border-color:color-mix(in srgb,var(--accent),transparent 55%); box-shadow:0 30px 70px -25px var(--accent); }
    .case-card__image { transition:transform .7s cubic-bezier(.2,.8,.2,1),filter .4s; filter:drop-shadow(0 22px 24px rgba(0,0,0,.48)); }
    .case-card:hover .case-card__image { transform:scale(1.08) translateY(-3px); filter:saturate(1.2) drop-shadow(0 28px 28px rgba(0,0,0,.52)); }
    .case-glint { position:absolute; inset:-50%; background:linear-gradient(110deg,transparent 43%,rgba(255,255,255,.22) 50%,transparent 57%); transform:translateX(-60%) rotate(8deg); transition:transform .8s; pointer-events:none; z-index:4; }
    .case-card:hover .case-glint { transform:translateX(60%) rotate(8deg); }
    .loot-box { position:relative;width:min(240px,72vw);height:180px;margin:auto;perspective:600px;filter:drop-shadow(0 24px 32px rgba(0,0,0,.5)); }
    .loot-box__image { position:absolute;inset:0;width:100%;height:100%;object-fit:contain; }
    .loot-box.is-opening { animation:box-rumble .12s linear 9; }
    .loot-box.is-opening .loot-box__image { animation:box-charge 2.6s cubic-bezier(.2,.8,.2,1) both; }
    .loot-box.is-opening::after { content:"";position:absolute;inset:-80px;border-radius:50%;background:radial-gradient(circle,rgba(255,255,255,.9),rgba(217,70,239,.28) 25%,transparent 65%);animation:prize-burst 1.4s 1.1s both; }
    @keyframes box-rumble { 25%{transform:translateX(-4px) rotate(-2deg)}75%{transform:translateX(4px) rotate(2deg)} }
    @keyframes box-charge { 0%,100%{transform:none;filter:none}55%{transform:translateY(-9px) scale(1.08);filter:brightness(1.3) saturate(1.3)} }
    @keyframes prize-burst { from{opacity:0;transform:scale(.1)}50%{opacity:1}to{opacity:0;transform:scale(1.35)} }
    .prize-reveal { animation:reveal .65s cubic-bezier(.16,1,.3,1) both; }
    @keyframes reveal { from{opacity:0;transform:translateY(35px) scale(.82)}to{opacity:1;transform:none} }
    .rarity-comun { color:#cbd5e1;background:rgba(148,163,184,.12);border-color:rgba(148,163,184,.3); }
    .rarity-poco_comun { color:#67e8f9;background:rgba(6,182,212,.12);border-color:rgba(34,211,238,.3); }
    .rarity-raro { color:#c084fc;background:rgba(168,85,247,.12);border-color:rgba(192,132,252,.3); }
    .rarity-epico { color:#fbbf24;background:rgba(245,158,11,.12);border-color:rgba(251,191,36,.3); }
    .rarity-legendario { color:#fb7185;background:rgba(244,63,94,.12);border-color:rgba(251,113,133,.35);box-shadow:0 0 35px rgba(244,63,94,.15); }
    .case-reel{overflow:hidden;mask-image:linear-gradient(90deg,transparent,#000 12%,#000 88%,transparent)}
    .case-reel__track{display:flex;gap:12px;will-change:transform}
    .case-reel__item{flex:0 0 112px;border:1px solid rgba(255,255,255,.12);background:#101426;border-radius:14px;padding:8px}
</style>
@endsection

@section('contenido')
@if($rickyeditCampaignEnabled ?? false)
<div class="mx-auto max-w-[1400px] px-4 pt-6 sm:px-6"><div class="flex items-center gap-3 rounded-xl border border-fuchsia-400/20 bg-fuchsia-400/10 p-4"><img src="{{ app(\App\Services\CampaignManager::class)->asset('badge') }}" alt="" class="h-9 w-9" width="36" height="36" decoding="async"><div><b class="text-fuchsia-100">Distintivo RickyEdit</b><p class="text-xs text-slate-400">Las cajas se muestran en la campaña, pero sus aperturas no consumen ni generan saldo del reto.</p></div></div></div>
@endif
<div class="max-w-[1400px] mx-auto px-4 sm:px-6 lg:px-8 py-6 sm:py-10" x-data="caseCenter()" x-init="init()">
    <section class="case-hero relative mb-8 flex min-h-[340px] items-center overflow-hidden rounded-3xl border border-white/10 sm:mb-10 sm:min-h-[390px] sm:rounded-[2rem]">
        <img src="{{ asset('images/lootra_visual_pack/12_cases/cases_hero_960x540.webp') }}" srcset="{{ asset('images/lootra_visual_pack/12_cases/cases_hero_960x540.webp') }} 960w, {{ asset('images/lootra_visual_pack/12_cases/cases_hero_1672x941.webp') }} 1672w" sizes="(min-width: 1400px) 1344px, calc(100vw - 2rem)" width="1672" height="941" alt="Cajas Lootra abiertas con recompensas digitales" class="absolute inset-0 h-full w-full object-cover object-center opacity-90" fetchpriority="high" decoding="async">
        <div class="absolute inset-0 bg-gradient-to-r from-[#090719] via-[#0b0920]/88 to-[#090719]/15"></div>
        <div class="relative z-10 max-w-3xl p-6 sm:p-12 lg:p-16">
            <span class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-fuchsia-400/10 border border-fuchsia-300/20 text-fuchsia-200 text-xs font-bold uppercase tracking-[.18em] mb-5"><i class="w-2 h-2 rounded-full bg-cyan-300 animate-pulse"></i> Lootra Drops</span>
            <h1 class="font-display text-3xl min-[420px]:text-4xl sm:text-6xl font-bold tracking-[-.06em] leading-[.95] mb-5">Abre. Descubre.<br><span class="text-transparent bg-clip-text bg-gradient-to-r from-fuchsia-400 via-brand-300 to-cyan-300">Hazlo tuyo.</span></h1>
            <p class="max-w-xl text-base leading-relaxed text-slate-300 sm:text-lg">Cada apertura genera un premio real en el servidor. Guárdalo en tu inventario o conviértelo en saldo cuando quieras.</p>
            <div class="flex flex-wrap gap-3 mt-7">
                <button @click="document.querySelector('#cajas').scrollIntoView({behavior:'smooth'})" class="cta-shine px-6 py-3 rounded-xl bg-gradient-to-r from-fuchsia-500 to-brand-400 text-black font-extrabold">Ver cajas</button>
                @auth
                    <button @click="document.querySelector('#inventario').scrollIntoView({behavior:'smooth'})" class="px-6 py-3 rounded-xl bg-white/10 border border-white/15 font-bold hover:bg-white/15 transition">Mi inventario <span class="ml-1 text-cyan-300" x-text="availableCount"></span></button>
                @endauth
            </div>
        </div>
    </section>

    <div id="cajas" class="scroll-mt-24">
        <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-4 mb-6">
            <div><p class="text-xs font-bold uppercase tracking-[.2em] text-fuchsia-400">Colección activa</p><h2 class="text-3xl font-bold mt-1">Elige tu drop</h2></div>
            <div class="flex flex-wrap gap-2">
                @foreach(['all' => 'Todas', 'low' => 'Starter', 'mid' => 'Premium', 'high' => 'Exclusivas'] as $key => $label)
                    <button @click="filter='{{ $key }}'" :class="filter==='{{ $key }}' ? 'bg-white text-slate-950' : 'bg-white/5 text-slate-400 border-white/10'" class="px-4 py-2 rounded-xl border border-transparent text-xs font-bold transition">{{ $label }}</button>
                @endforeach
            </div>
        </div>

        <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-5 mb-16">
            @foreach($cajas as $key => $caja)
                @php
                    $accent = ['cyan' => '#22d3ee', 'purple' => '#a855f7', 'blue' => '#3b82f6', 'amber' => '#f59e0b'][$caja['color']];
                    $top = collect($caja['premios'])->sortByDesc('valor')->first();
                @endphp
                <article x-show="filter==='all' || filter==='{{ $caja['tier'] }}'" x-transition
                         class="case-card group relative rounded-[1.5rem] bg-white/[0.035] border border-white/10 overflow-hidden" style="--accent:{{ $accent }}">
                    <div class="case-glint"></div>
                    <div class="relative h-52 overflow-hidden bg-[radial-gradient(circle_at_50%_42%,color-mix(in_srgb,var(--accent)_24%,transparent),transparent_62%)]">
                        <img src="{{ $caja['imagen'] }}" alt="{{ $caja['nombre'] }}" class="case-card__image h-full w-full object-contain px-3 pt-2" width="1254" height="1254" loading="lazy" decoding="async">
                        <div class="absolute inset-0 bg-gradient-to-t from-[#090914] via-transparent to-transparent"></div>
                        <span class="absolute top-3 right-3 px-2.5 py-1 rounded-lg bg-black/50 border border-white/10 backdrop-blur text-[10px] uppercase tracking-widest">Hasta €{{ number_format($top['valor'], 0) }}</span>
                    </div>
                    <div class="p-5 -mt-7 relative z-10">
                        <div class="w-11 h-11 rounded-xl grid place-items-center mb-3 border border-white/15" style="background:color-mix(in srgb,{{ $accent }},transparent 82%);color:{{ $accent }}">
                            <svg class="w-6 h-6" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M4 8h16v12H4V8Zm-1-4h18v4H3V4Zm9 0v16M7 4c0-2 4-2 5 0M17 4c0-2-4-2-5 0" stroke-width="1.7"/></svg>
                        </div>
                        <h3 class="text-xl font-bold">{{ $caja['nombre'] }}</h3>
                        <p class="text-xs text-slate-500 mt-1 min-h-9">{{ $caja['descripcion'] }}</p>
                        <div class="flex items-center justify-between mt-5 mb-4"><strong class="text-2xl" style="color:{{ $accent }}">€{{ number_format($caja['precio'], 2) }}</strong><span class="text-[11px] text-slate-500">{{ count($caja['premios']) }} premios</span></div>
                        <button @auth @click="selectCase('{{ $key }}')" @if($rickyeditActiveChallenge ?? null) disabled title="No disponible durante el reto" @endif @else onclick="window.location='{{ route('login') }}'" @endauth
                                class="cta-shine w-full py-3 rounded-xl text-sm font-extrabold text-black transition hover:scale-[1.02]" style="background:linear-gradient(90deg,{{ $accent }},#fbbf24)">
                            @auth Abrir ahora @else Inicia sesión para abrir @endauth
                        </button>
                    </div>
                </article>
            @endforeach
        </div>
    </div>

    @auth
    <section id="inventario" class="scroll-mt-24 mb-16">
        <div class="mb-6 flex flex-col items-start justify-between gap-3 min-[480px]:flex-row min-[480px]:items-end">
            <div><p class="text-xs font-bold uppercase tracking-[.2em] text-cyan-400">Tu colección</p><h2 class="text-3xl font-bold mt-1">Inventario</h2></div>
            <div class="text-right"><p class="text-xs text-slate-500">Valor canjeable</p><p class="font-display text-xl font-bold text-emerald-400" x-text="money(inventoryValue)"></p></div>
        </div>
        <div x-show="availableCount===0" class="rounded-2xl border border-dashed border-white/10 p-12 text-center bg-white/[0.02]"><p class="text-slate-400 font-semibold">Tu inventario está vacío</p><p class="text-sm text-slate-600 mt-1">Abre una caja y tu premio aparecerá aquí.</p></div>
        <div class="grid sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
            <template x-for="item in inventory.filter(i => i.estado === 'disponible')" :key="item.id">
                <article class="rounded-2xl bg-white/[0.035] border border-white/10 overflow-hidden prize-reveal">
                    <div class="h-40 relative"><img :src="item.imagen" :alt="item.nombre" class="w-full h-full object-cover" loading="lazy" decoding="async"><span class="absolute top-3 left-3 px-2 py-1 rounded-lg border text-[10px] uppercase font-bold" :class="'rarity-'+item.rareza" x-text="rarityName(item.rareza)"></span></div>
                    <div class="p-4"><h3 class="font-bold text-white" x-text="item.nombre"></h3><p class="text-xs text-slate-500 mt-1">Obtenido <span x-text="item.created_at"></span></p><button @click="confirming=item" :disabled="redeeming===item.id" class="w-full mt-4 py-2.5 rounded-xl bg-emerald-500/15 border border-emerald-400/25 text-emerald-300 text-sm font-bold hover:bg-emerald-500/25 disabled:opacity-50 transition"><span x-text="redeeming===item.id ? 'Canjeando...' : 'Canjear por '+money(item.valor_canje)"></span></button></div>
                </article>
            </template>
        </div>
    </section>
    @endauth

    <div x-show="selected" x-cloak class="fixed inset-0 z-[200] grid place-items-center p-4">
        <div class="absolute inset-0 bg-[#03030a]/90 backdrop-blur-xl" @click="!opening && closeModal()"></div>
        <div class="relative max-h-[calc(100dvh-2rem)] w-full max-w-lg overflow-y-auto rounded-3xl bg-gradient-to-b from-[#191132] to-[#090914] border border-white/15 p-5 sm:rounded-[2rem] sm:p-9 text-center shadow-2xl shadow-fuchsia-950/60">
            <button x-show="!opening" @click="closeModal()" class="absolute right-5 top-5 z-20 w-9 h-9 rounded-full bg-white/5 text-slate-400 hover:text-white">×</button>
            <template x-if="!prize">
                <div>
                    <div class="loot-box my-7" :class="opening && 'is-opening'"><img :src="selected?.imagen" :alt="selected?.nombre" class="loot-box__image" width="1254" height="1254" decoding="async"></div>
                    <div x-show="opening" class="case-reel relative my-5 rounded-2xl border border-white/10 bg-black/25 p-3" x-ref="caseViewport"><div class="absolute bottom-0 left-1/2 top-0 z-10 w-0.5 -translate-x-1/2 bg-amber-300 shadow-[0_0_12px_#fbbf24]"></div><div class="case-reel__track" x-ref="caseTrack"><template x-for="(item,index) in reelItems" :key="`${spinId}-${index}`"><div class="case-reel__item" :data-reel-index="index" :data-prize-key="item.prize_key" :class="spinSettled && index === winnerIndex && 'ring-2 ring-amber-300 shadow-[0_0_24px_rgba(251,191,36,.35)]'"><img :src="item.imagen" :alt="item.nombre" class="h-20 w-full rounded-lg object-cover" decoding="async"><p class="mt-2 truncate text-[10px]" x-text="item.nombre"></p></div></template></div></div>
                    <p x-show="spinSettled" x-transition class="-mt-2 text-xs font-bold text-amber-200">Premio señalado: <span x-text="reelItems[winnerIndex]?.nombre"></span></p>
                    <h2 class="text-2xl font-bold" x-text="selected?.nombre"></h2>
                    <p class="text-slate-500 text-sm mt-2" x-text="opening ? 'Generando y guardando tu premio...' : 'El premio se añadirá automáticamente a tu inventario.'"></p>
                    <p x-show="error" class="mt-4 text-sm text-red-400" x-text="error"></p>
                    <button x-show="!opening" @click="openSelected()" class="cta-shine mt-6 w-full px-5 py-3 rounded-xl bg-gradient-to-r from-fuchsia-500 to-brand-400 text-black font-extrabold sm:w-auto sm:px-8">Confirmar apertura · <span x-text="money(selected?.precio || 0)"></span></button>
                </div>
            </template>
            <template x-if="prize">
                <div class="prize-reveal">
                    <p class="text-xs uppercase tracking-[.25em] text-fuchsia-300 font-bold mb-4">Nuevo premio</p>
                    <div class="relative mx-auto aspect-square w-44 overflow-hidden rounded-3xl border-2 sm:w-56" :class="'rarity-'+prize.rareza"><img :src="prize.imagen" :alt="prize.nombre" class="w-full h-full object-cover" decoding="async"><div class="absolute inset-0 bg-gradient-to-t from-black/70 to-transparent"></div></div>
                    <span class="inline-block mt-5 px-3 py-1 rounded-full border text-[10px] uppercase font-bold" :class="'rarity-'+prize.rareza" x-text="rarityName(prize.rareza)"></span>
                    <h2 class="text-2xl font-bold mt-3" x-text="prize.nombre"></h2>
                    <p class="text-sm text-slate-400 mt-2">Valor de canje: <strong class="text-emerald-400" x-text="money(prize.valor_canje)"></strong></p>
                    <button @click="closeModal(); document.querySelector('#inventario').scrollIntoView({behavior:'smooth'})" class="mt-6 px-7 py-3 rounded-xl bg-white text-slate-950 font-extrabold">Ver en inventario</button>
                </div>
            </template>
        </div>
    </div>

    <div x-show="confirming" x-cloak class="fixed inset-0 z-[220] grid place-items-center p-4"><div class="absolute inset-0 bg-black/80" @click="confirming=null"></div><div class="relative max-w-sm rounded-2xl border border-white/15 bg-[#111827] p-6 text-center"><h2 class="text-xl font-bold">Confirmar canje</h2><p class="mt-3 text-sm text-slate-400">Convertirás <b class="text-white" x-text="confirming?.nombre"></b> en <b class="text-emerald-300" x-text="money(confirming?.valor_canje)"></b>. No se puede deshacer.</p><div class="mt-6 grid grid-cols-2 gap-3"><button @click="confirming=null" class="rounded-xl bg-white/5 py-3">Cancelar</button><button @click="redeem(confirming)" class="rounded-xl bg-emerald-400 py-3 font-bold text-slate-950">Confirmar</button></div></div></div>
    <div x-show="toast" x-transition class="fixed inset-x-4 bottom-4 z-[250] max-w-sm px-5 py-4 rounded-xl bg-emerald-950/95 border border-emerald-400/30 text-emerald-200 shadow-2xl sm:inset-x-auto sm:bottom-5 sm:right-5" x-text="toast"></div>
</div>

@push('scripts')
<script>
function caseCenter() {
    return {
        cases: @js($cajas),
        inventory: @js($inventario->map(fn ($item) => ['id' => $item->id, 'nombre' => $item->nombre, 'imagen' => $item->imagen, 'rareza' => $item->rareza, 'valor_canje' => $item->valor_canje, 'estado' => $item->estado, 'created_at' => $item->created_at->diffForHumans()])),
        filter: 'all', selectedKey: null, selected: null, requestToken: '', opening: false, prize: null, committedPrize: null, error: '', redeeming: null, confirming: null, toast: '', reelItems: [], spinId: 0, winnerIndex: 0, spinSettled: false,
        get availableCount() { return this.inventory.filter(item => item.estado === 'disponible').length; },
        get inventoryValue() { return this.inventory.filter(item => item.estado === 'disponible').reduce((sum, item) => sum + Number(item.valor_canje), 0); },
        money(value) { return new Intl.NumberFormat('es-ES', { style:'currency', currency:'EUR' }).format(Number(value || 0)); },
        rarityName(value) { return ({comun:'Común', poco_comun:'Poco común', raro:'Raro', epico:'Épico', legendario:'Legendario'})[value] || value; },
        init(){this.escapeHandler=e=>{if(e.key==='Escape'&&!this.opening){this.confirming=null;this.closeModal()}};document.addEventListener('keydown',this.escapeHandler)},
        destroy(){document.removeEventListener('keydown',this.escapeHandler);document.body.style.overflow=''},
        newRequestToken() { const webCrypto=globalThis.crypto; if(typeof webCrypto?.randomUUID==='function')return webCrypto.randomUUID(); const bytes=new Uint8Array(16); webCrypto.getRandomValues(bytes); bytes[6]=(bytes[6]&15)|64; bytes[8]=(bytes[8]&63)|128; return Array.from(bytes,(b,i)=>([4,6,8,10].includes(i)?'-':'')+b.toString(16).padStart(2,'0')).join(''); },
        selectCase(key) { this.selectedKey=key; this.selected=this.cases[key]; this.requestToken=this.newRequestToken(); this.prize=null; this.committedPrize=null; this.reelItems=[]; this.spinSettled=false; this.error=''; document.body.style.overflow='hidden'; },
        closeModal() { if (this.opening) return; this.selected=null; this.selectedKey=null; this.prize=null; this.committedPrize=null; this.reelItems=[]; this.spinSettled=false; this.error=''; document.body.style.overflow=''; },
        addToInventory(item) {
            const saved={...item,estado:item.estado || 'disponible',created_at:item.created_at || 'ahora'};
            this.inventory=[saved,...this.inventory.filter(current=>Number(current.id)!==Number(saved.id))];
            window.dispatchEvent(new CustomEvent('inventory-updated',{detail:{item:saved}}));
        },
        samePrize(left,right) {
            return Boolean(left && right
                && String(left.prize_key) === String(right.prize_key)
                && String(left.nombre) === String(right.nombre)
                && String(left.imagen) === String(right.imagen)
                && String(left.rareza) === String(right.rareza)
                && Math.abs(Number(left.valor_canje)-Number(right.valor_canje)) < .001);
        },
        async preloadReel(items) {
            const sources=[...new Set(items.map(item=>item.imagen).filter(Boolean))];
            const loading=Promise.allSettled(sources.map(source=>new Promise(resolve=>{const image=new Image();image.onload=image.onerror=resolve;image.src=source;})));
            await Promise.race([loading,new Promise(resolve=>setTimeout(resolve,900))]);
        },
        frame() { return new Promise(resolve=>requestAnimationFrame(()=>requestAnimationFrame(resolve))); },
        centeredReelIndex(track,viewport) {
            const viewportRect=viewport.getBoundingClientRect();
            const centerX=viewportRect.left+viewportRect.width/2;
            return [...track.children].reduce((nearest,item)=>{
                const rect=item.getBoundingClientRect();
                const distance=Math.abs((rect.left+rect.width/2)-centerX);
                return !nearest || distance < nearest.distance ? {index:Number(item.dataset.reelIndex),distance} : nearest;
            },null)?.index;
        },
        async spinToPrize(winner,reel,winnerIndex) {
            const items=Array.isArray(reel) ? [...reel] : [];
            const parsedIndex=Number(winnerIndex);
            if (!Number.isInteger(parsedIndex) || parsedIndex < 0 || parsedIndex >= items.length) {
                throw new Error('El servidor no devolvió una tirada válida. No se ha alterado el premio guardado.');
            }
            if (!this.samePrize(winner,items[parsedIndex])) {
                throw new Error('El premio guardado y el ganador de la rueda no coinciden. Actualiza la página para ver tu inventario.');
            }
            this.winnerIndex=parsedIndex;
            this.spinSettled=false;
            this.spinId+=1;
            this.reelItems=items;
            await this.$nextTick();
            await this.preloadReel(items);
            await this.frame();

            const track=this.$refs.caseTrack;
            const viewport=this.$refs.caseViewport;
            const target=track?.children[this.winnerIndex];
            if (!track || !viewport || !target) return;

            track.getAnimations().forEach(animation=>animation.cancel());
            track.style.transform='translate3d(0,0,0)';
            await this.frame();
            const viewportRect=viewport.getBoundingClientRect();
            const targetRect=target.getBoundingClientRect();
            let shift=(viewportRect.left+viewportRect.width/2)-(targetRect.left+targetRect.width/2);
            const reduceMotion=window.matchMedia('(prefers-reduced-motion: reduce)').matches;
            const duration=reduceMotion ? 80 : 3000;
            const animation=track.animate(
                [
                    {transform:'translate3d(0,0,0)',offset:0},
                    {transform:`translate3d(${shift-26}px,0,0)`,offset:.86},
                    {transform:`translate3d(${shift+8}px,0,0)`,offset:.95},
                    {transform:`translate3d(${shift}px,0,0)`,offset:1},
                ],
                {duration,easing:'cubic-bezier(.12,.68,.16,1)',fill:'forwards'}
            );
            await animation.finished;
            track.style.transform=`translate3d(${shift}px,0,0)`;
            animation.cancel();
            for (let attempt=0;attempt<3;attempt++) {
                await this.frame();
                const settledViewport=viewport.getBoundingClientRect();
                const settledTarget=target.getBoundingClientRect();
                const correction=(settledViewport.left+settledViewport.width/2)-(settledTarget.left+settledTarget.width/2);
                if (Math.abs(correction)<=.5) break;
                shift+=correction;
                track.style.transform=`translate3d(${shift}px,0,0)`;
            }
            await this.frame();
            if (this.centeredReelIndex(track,viewport) !== this.winnerIndex) {
                throw new Error('La rueda no pudo alinear el premio. El objeto sí está guardado en tu inventario.');
            }

            this.spinSettled=true;
            await this.$nextTick();
            await new Promise(resolve=>setTimeout(resolve,reduceMotion ? 80 : 650));
        },
        async openSelected() {
            if (this.opening || !this.selectedKey) return;
            this.opening=true; this.error=''; this.prize=null;
            try {
                const url=@js(route('cases.open',['caja'=>'__CASE__'])).replace('__CASE__',encodeURIComponent(this.selectedKey));
                const response=await fetch(url, { method:'POST', headers:{'Content-Type':'application/json','X-CSRF-TOKEN':document.querySelector('meta[name="csrf-token"]').content,'Accept':'application/json'}, body:JSON.stringify({request_token:this.requestToken}) });
                const data=await response.json();
                if (!response.ok) throw new Error(data.message || 'No se pudo abrir la caja.');
                const reelWinner=data.reel?.[Number(data.winner_index)];
                if (!this.samePrize(data.item,data.winner) || !this.samePrize(data.item,reelWinner)) {
                    throw new Error('La respuesta de apertura no es consistente. Actualiza la página para comprobar tu inventario.');
                }
                this.committedPrize=data.item;
                this.addToInventory(data.item);
                await this.$nextTick();
                Alpine.store('wallet').saldo=Number(data.saldo);
                window.dispatchEvent(new CustomEvent('saldo-updated',{detail:{saldo:Number(data.saldo)}}));
                await this.spinToPrize(data.winner,data.reel,data.winner_index);
                this.prize=data.item;
            } catch (error) {
                if (this.committedPrize) {
                    this.prize=this.committedPrize;
                    this.toast='El premio se guardó correctamente en tu inventario.';
                    setTimeout(()=>this.toast='',3500);
                } else {
                    this.error=error.message;
                }
            }
            this.opening=false;
        },
        async redeem(item) {
            if (this.redeeming) return;
            this.redeeming=item.id;this.confirming=null;
            try {
                const url=@js(route('inventory.redeem',['item'=>'__ITEM__'])).replace('__ITEM__',encodeURIComponent(item.id));
                const response=await fetch(url, {method:'POST',headers:{'X-CSRF-TOKEN':document.querySelector('meta[name="csrf-token"]').content,'Accept':'application/json'}});
                const data=await response.json();
                if (!response.ok) throw new Error(data.message || 'No se pudo canjear el premio.');
                item.estado='canjeado'; Alpine.store('wallet').saldo=Number(data.saldo);
                window.dispatchEvent(new CustomEvent('saldo-updated',{detail:{saldo:Number(data.saldo)}}));
                this.toast=`${item.nombre} canjeado por ${this.money(data.valor)}`; setTimeout(()=>this.toast='',3500);
            } catch (error) { this.toast=error.message; setTimeout(()=>this.toast='',3500); }
            this.redeeming=null;
        }
    };
}
</script>
@endpush
@endsection
