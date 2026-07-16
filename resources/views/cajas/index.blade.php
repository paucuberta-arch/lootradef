@extends('layouts.app')

@section('title', 'Loot Boxes — Lootra Casino')

@section('styles')
<style>
    .case-hero { background: radial-gradient(circle at 75% 30%, rgba(168,85,247,.38), transparent 28rem), linear-gradient(125deg,#090719,#17103d 55%,#071827); }
    .case-card { --accent:#a855f7; transform-style:preserve-3d; transition:transform .45s cubic-bezier(.2,.8,.2,1),border-color .3s,box-shadow .3s; }
    .case-card:hover { transform:translateY(-10px) rotateX(2deg); border-color:color-mix(in srgb,var(--accent),transparent 55%); box-shadow:0 30px 70px -25px var(--accent); }
    .case-card img { transition:transform .7s cubic-bezier(.2,.8,.2,1),filter .4s; }
    .case-card:hover img { transform:scale(1.1); filter:saturate(1.25); }
    .case-glint { position:absolute; inset:-50%; background:linear-gradient(110deg,transparent 43%,rgba(255,255,255,.22) 50%,transparent 57%); transform:translateX(-60%) rotate(8deg); transition:transform .8s; pointer-events:none; z-index:4; }
    .case-card:hover .case-glint { transform:translateX(60%) rotate(8deg); }
    .loot-box { position:relative;width:150px;height:120px;margin:auto;perspective:600px;filter:drop-shadow(0 24px 32px rgba(0,0,0,.5)); }
    .loot-box__base { position:absolute;left:12px;right:12px;bottom:0;height:88px;border-radius:12px 12px 24px 24px;background:linear-gradient(145deg,#7c3aed,#312e81 65%,#171744);border:2px solid rgba(255,255,255,.28);box-shadow:inset 0 0 32px rgba(34,211,238,.22); }
    .loot-box__base::after { content:"L";position:absolute;inset:22px 44px;display:grid;place-items:center;border-radius:50%;background:linear-gradient(145deg,#fde68a,#f59e0b);color:#271405;font:900 22px "Space Grotesk";box-shadow:0 0 24px #f59e0b; }
    .loot-box__lid { position:absolute;left:2px;right:2px;top:9px;height:42px;border-radius:18px 18px 8px 8px;background:linear-gradient(135deg,#d946ef,#7c3aed 55%,#22d3ee);border:2px solid rgba(255,255,255,.35);transform-origin:15px 38px;z-index:2; }
    .loot-box.is-opening { animation:box-rumble .12s linear 9; }
    .loot-box.is-opening .loot-box__lid { animation:lid-open 1.45s .8s cubic-bezier(.2,.9,.2,1) forwards; }
    .loot-box.is-opening::after { content:"";position:absolute;inset:-80px;border-radius:50%;background:radial-gradient(circle,rgba(255,255,255,.9),rgba(217,70,239,.28) 25%,transparent 65%);animation:prize-burst 1.4s 1.1s both; }
    @keyframes box-rumble { 25%{transform:translateX(-4px) rotate(-2deg)}75%{transform:translateX(4px) rotate(2deg)} }
    @keyframes lid-open { to{transform:translateY(-65px) rotateZ(-12deg) rotateX(75deg);opacity:.1} }
    @keyframes prize-burst { from{opacity:0;transform:scale(.1)}50%{opacity:1}to{opacity:0;transform:scale(1.35)} }
    .prize-reveal { animation:reveal .65s cubic-bezier(.16,1,.3,1) both; }
    @keyframes reveal { from{opacity:0;transform:translateY(35px) scale(.82)}to{opacity:1;transform:none} }
    .rarity-comun { color:#cbd5e1;background:rgba(148,163,184,.12);border-color:rgba(148,163,184,.3); }
    .rarity-poco_comun { color:#67e8f9;background:rgba(6,182,212,.12);border-color:rgba(34,211,238,.3); }
    .rarity-raro { color:#c084fc;background:rgba(168,85,247,.12);border-color:rgba(192,132,252,.3); }
    .rarity-epico { color:#fbbf24;background:rgba(245,158,11,.12);border-color:rgba(251,191,36,.3); }
    .rarity-legendario { color:#fb7185;background:rgba(244,63,94,.12);border-color:rgba(251,113,133,.35);box-shadow:0 0 35px rgba(244,63,94,.15); }
</style>
@endsection

@section('contenido')
<div class="max-w-[1400px] mx-auto px-4 sm:px-6 lg:px-8 py-6 sm:py-10" x-data="caseCenter()">
    <section class="case-hero relative min-h-[390px] rounded-[2rem] overflow-hidden border border-white/10 mb-10 flex items-center">
        <img src="https://images.unsplash.com/photo-1513542789411-b6a5d4f31634?auto=format&fit=crop&w=1800&q=85" alt="Colección de premios" class="absolute inset-0 w-full h-full object-cover opacity-20">
        <div class="absolute inset-0 bg-gradient-to-r from-[#090719] via-[#0b0920]/85 to-transparent"></div>
        <div class="relative z-10 p-8 sm:p-12 lg:p-16 max-w-3xl">
            <span class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-fuchsia-400/10 border border-fuchsia-300/20 text-fuchsia-200 text-xs font-bold uppercase tracking-[.18em] mb-5"><i class="w-2 h-2 rounded-full bg-cyan-300 animate-pulse"></i> Lootra Drops</span>
            <h1 class="font-display text-4xl sm:text-6xl font-bold tracking-[-.06em] leading-[.95] mb-5">Abre. Descubre.<br><span class="text-transparent bg-clip-text bg-gradient-to-r from-fuchsia-400 via-brand-300 to-cyan-300">Hazlo tuyo.</span></h1>
            <p class="text-slate-300 text-lg max-w-xl leading-relaxed">Cada apertura genera un premio real en el servidor. Guárdalo en tu inventario o conviértelo en saldo cuando quieras.</p>
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
            <div class="flex gap-2">
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
                    <div class="h-52 relative overflow-hidden">
                        <img src="{{ $caja['imagen'] }}" alt="{{ $caja['nombre'] }}" class="w-full h-full object-cover" loading="lazy">
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
                        <button @auth @click="selectCase('{{ $key }}')" @else onclick="window.location='{{ route('login') }}'" @endauth
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
        <div class="flex items-end justify-between mb-6">
            <div><p class="text-xs font-bold uppercase tracking-[.2em] text-cyan-400">Tu colección</p><h2 class="text-3xl font-bold mt-1">Inventario</h2></div>
            <div class="text-right"><p class="text-xs text-slate-500">Valor canjeable</p><p class="font-display text-xl font-bold text-emerald-400" x-text="money(inventoryValue)"></p></div>
        </div>
        <div x-show="availableCount===0" class="rounded-2xl border border-dashed border-white/10 p-12 text-center bg-white/[0.02]"><p class="text-slate-400 font-semibold">Tu inventario está vacío</p><p class="text-sm text-slate-600 mt-1">Abre una caja y tu premio aparecerá aquí.</p></div>
        <div class="grid sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
            <template x-for="item in inventory.filter(i => i.estado === 'disponible')" :key="item.id">
                <article class="rounded-2xl bg-white/[0.035] border border-white/10 overflow-hidden prize-reveal">
                    <div class="h-40 relative"><img :src="item.imagen" :alt="item.nombre" class="w-full h-full object-cover"><span class="absolute top-3 left-3 px-2 py-1 rounded-lg border text-[10px] uppercase font-bold" :class="'rarity-'+item.rareza" x-text="rarityName(item.rareza)"></span></div>
                    <div class="p-4"><h3 class="font-bold text-white" x-text="item.nombre"></h3><p class="text-xs text-slate-500 mt-1">Obtenido <span x-text="item.created_at"></span></p><button @click="redeem(item)" :disabled="redeeming===item.id" class="w-full mt-4 py-2.5 rounded-xl bg-emerald-500/15 border border-emerald-400/25 text-emerald-300 text-sm font-bold hover:bg-emerald-500/25 disabled:opacity-50 transition"><span x-text="redeeming===item.id ? 'Canjeando...' : 'Canjear por '+money(item.valor_canje)"></span></button></div>
                </article>
            </template>
        </div>
    </section>
    @endauth

    <div x-show="selected" x-cloak class="fixed inset-0 z-[200] grid place-items-center p-4">
        <div class="absolute inset-0 bg-[#03030a]/90 backdrop-blur-xl" @click="!opening && closeModal()"></div>
        <div class="relative w-full max-w-lg rounded-[2rem] bg-gradient-to-b from-[#191132] to-[#090914] border border-white/15 p-7 sm:p-9 text-center overflow-hidden shadow-2xl shadow-fuchsia-950/60">
            <button x-show="!opening" @click="closeModal()" class="absolute right-5 top-5 z-20 w-9 h-9 rounded-full bg-white/5 text-slate-400 hover:text-white">×</button>
            <template x-if="!prize">
                <div>
                    <div class="loot-box my-8" :class="opening && 'is-opening'"><div class="loot-box__lid"></div><div class="loot-box__base"></div></div>
                    <h2 class="text-2xl font-bold" x-text="selected?.nombre"></h2>
                    <p class="text-slate-500 text-sm mt-2" x-text="opening ? 'Generando y guardando tu premio...' : 'El premio se añadirá automáticamente a tu inventario.'"></p>
                    <p x-show="error" class="mt-4 text-sm text-red-400" x-text="error"></p>
                    <button x-show="!opening" @click="openSelected()" class="cta-shine mt-6 px-8 py-3 rounded-xl bg-gradient-to-r from-fuchsia-500 to-brand-400 text-black font-extrabold">Confirmar apertura · <span x-text="money(selected?.precio || 0)"></span></button>
                </div>
            </template>
            <template x-if="prize">
                <div class="prize-reveal">
                    <p class="text-xs uppercase tracking-[.25em] text-fuchsia-300 font-bold mb-4">Nuevo premio</p>
                    <div class="relative w-56 h-56 mx-auto rounded-3xl overflow-hidden border-2" :class="'rarity-'+prize.rareza"><img :src="prize.imagen" :alt="prize.nombre" class="w-full h-full object-cover"><div class="absolute inset-0 bg-gradient-to-t from-black/70 to-transparent"></div></div>
                    <span class="inline-block mt-5 px-3 py-1 rounded-full border text-[10px] uppercase font-bold" :class="'rarity-'+prize.rareza" x-text="rarityName(prize.rareza)"></span>
                    <h2 class="text-2xl font-bold mt-3" x-text="prize.nombre"></h2>
                    <p class="text-sm text-slate-400 mt-2">Valor de canje: <strong class="text-emerald-400" x-text="money(prize.valor_canje)"></strong></p>
                    <button @click="closeModal(); document.querySelector('#inventario').scrollIntoView({behavior:'smooth'})" class="mt-6 px-7 py-3 rounded-xl bg-white text-slate-950 font-extrabold">Ver en inventario</button>
                </div>
            </template>
        </div>
    </div>

    <div x-show="toast" x-transition class="fixed right-5 bottom-5 z-[250] max-w-sm px-5 py-4 rounded-xl bg-emerald-950/95 border border-emerald-400/30 text-emerald-200 shadow-2xl" x-text="toast"></div>
</div>

@push('scripts')
<script>
function caseCenter() {
    return {
        cases: @js($cajas),
        inventory: @js($inventario->map(fn ($item) => ['id' => $item->id, 'nombre' => $item->nombre, 'imagen' => $item->imagen, 'rareza' => $item->rareza, 'valor_canje' => $item->valor_canje, 'estado' => $item->estado, 'created_at' => $item->created_at->diffForHumans()])),
        filter: 'all', selectedKey: null, selected: null, opening: false, prize: null, error: '', redeeming: null, toast: '',
        get availableCount() { return this.inventory.filter(item => item.estado === 'disponible').length; },
        get inventoryValue() { return this.inventory.filter(item => item.estado === 'disponible').reduce((sum, item) => sum + Number(item.valor_canje), 0); },
        money(value) { return new Intl.NumberFormat('es-ES', { style:'currency', currency:'EUR' }).format(Number(value || 0)); },
        rarityName(value) { return ({comun:'Común', poco_comun:'Poco común', raro:'Raro', epico:'Épico', legendario:'Legendario'})[value] || value; },
        selectCase(key) { this.selectedKey=key; this.selected=this.cases[key]; this.prize=null; this.error=''; document.body.style.overflow='hidden'; },
        closeModal() { if (this.opening) return; this.selected=null; this.selectedKey=null; this.prize=null; this.error=''; document.body.style.overflow=''; },
        async openSelected() {
            if (this.opening || !this.selectedKey) return;
            this.opening=true; this.error='';
            const started=Date.now();
            try {
                const url=@js(route('cases.open',['caja'=>'__CASE__'])).replace('__CASE__',encodeURIComponent(this.selectedKey));
                const response=await fetch(url, { method:'POST', headers:{'X-CSRF-TOKEN':document.querySelector('meta[name="csrf-token"]').content,'Accept':'application/json'} });
                const data=await response.json();
                if (!response.ok) throw new Error(data.message || 'No se pudo abrir la caja.');
                await new Promise(resolve => setTimeout(resolve, Math.max(0, 2400-(Date.now()-started))));
                this.prize=data.item; this.inventory.unshift(data.item);
                Alpine.store('wallet').saldo=Number(data.saldo);
                window.dispatchEvent(new CustomEvent('saldo-updated',{detail:{saldo:Number(data.saldo)}}));
            } catch (error) { this.error=error.message; }
            this.opening=false;
        },
        async redeem(item) {
            if (this.redeeming) return;
            this.redeeming=item.id;
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
