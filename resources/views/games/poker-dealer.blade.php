@extends('layouts.game')
@section('title', "Texas Hold'em contra el Dealer — Lootra")

@section('styles')
<style>
    @keyframes card-deal{from{opacity:0;transform:translate3d(72px,-38px,0) rotate(10deg) scale(.82)}to{opacity:1;transform:none}}
    .dealer-table{background:radial-gradient(ellipse at 50% 42%,#176347,#06291e 68%);border:14px solid #4a220c;border-bottom-width:24px;box-shadow:inset 0 0 90px #0009,0 35px 80px #000b}
    .holdem-card{width:clamp(56px,8vw,82px);aspect-ratio:.7;border-radius:10px;background:linear-gradient(145deg,#fff,#e2e8f0);color:#111827;position:relative;box-shadow:0 12px 25px #0008;border:1px solid white;animation:card-deal .32s cubic-bezier(.16,1,.3,1) both;backface-visibility:hidden}
    .holdem-card.red{color:#dc2626}.holdem-card.back{background:repeating-linear-gradient(45deg,#172554 0 6px,#7c3aed 6px 12px);border:4px solid white}
    .holdem-card b{position:absolute;left:7px;top:5px;font-size:16px}.holdem-card i{position:absolute;inset:0;display:grid;place-items:center;font-style:normal;font-size:30px}
    .phase-line::before{content:"";position:absolute;left:8%;right:8%;top:15px;height:2px;background:#ffffff12}.phase-step{position:relative;z-index:1}
    @media (max-width:639px){
        .dealer-table{border-width:6px;border-bottom-width:10px;border-radius:1.5rem}
        .holdem-card{width:clamp(38px,12vw,50px);border-radius:7px}
        .holdem-card.back{border-width:2px}.holdem-card b{left:4px;top:3px;font-size:12px}.holdem-card i{font-size:21px}
        .phase-line::before{left:10%;right:10%}.phase-step small{font-size:8px}
    }
</style>
@endsection

@section('game-content')
<div class="mx-auto max-w-[1450px] px-4 py-8" x-data="dealerPoker()" :aria-busy="busy.toString()">
    <header class="mb-6 flex flex-wrap items-center justify-between gap-4"><div class="min-w-0"><p class="text-xs font-black uppercase tracking-[.2em] text-emerald-400">Poker por fases</p><h1 class="game-heading mt-1 font-black">Texas Hold'em contra el Dealer</h1><p class="mt-2 text-sm text-slate-500">Toma una decisión en cada calle y llega al showdown.</p></div><div class="flex w-full flex-wrap gap-2 sm:w-auto"><a href="{{ route('games.poker.all-in') }}" class="rounded-xl border border-white/10 px-3 py-2 text-xs text-slate-400">Poker All-In</a><span class="rounded-xl border border-emerald-400/20 bg-emerald-400/10 px-3 sm:px-4 py-2 text-sm whitespace-nowrap">Saldo <b class="ml-1 text-emerald-300" x-text="money(saldo)"></b></span></div></header>

    <div class="grid gap-6 xl:grid-cols-[1fr_330px]">
        <main>
            <div class="phase-line relative mb-5 grid grid-cols-5 text-center"><template x-for="(step,i) in ['Preflop','Flop','Turn','River','Showdown']"><div class="phase-step"><span class="mx-auto grid h-8 w-8 place-items-center rounded-full border text-xs font-black" :class="phaseIndex>=i?'border-emerald-300 bg-emerald-400 text-slate-950':'border-white/10 bg-[#0a0a12] text-slate-600'" x-text="i+1"></span><small class="mt-2 block text-[10px]" :class="phaseIndex>=i?'text-emerald-300':'text-slate-600'" x-text="step"></small></div></template></div>
            <section class="dealer-table relative min-h-[490px] sm:min-h-[610px] overflow-hidden rounded-[3rem] p-3 sm:p-9">
                <div class="relative z-10 flex h-full min-h-[430px] sm:min-h-[530px] flex-col justify-between">
                    <div class="text-center"><div class="mb-3 flex items-center justify-center gap-2"><b class="text-sm text-emerald-200">Dealer</b><span x-show="hand?.dealer_hand" class="rounded-full bg-black/25 px-2 py-1 text-xs text-amber-200" x-text="hand.dealer_hand"></span></div><div class="flex min-h-[118px] justify-center gap-2"><template x-for="(card,i) in dealerCards" :key="cardKey(card, 'dealer', i)"><div class="holdem-card" :class="card.hidden?'back':cardColor(card)" :style="`animation-delay:${i*.08}s`"><template x-if="!card.hidden"><div><b x-text="rank(card.rank)"></b><i x-text="suit(card.suit)"></i></div></template></div></template></div></div>
                    <div class="rounded-2xl sm:rounded-3xl border border-emerald-200/15 bg-black/15 px-2 sm:px-3 py-3 sm:py-5 text-center"><p class="mb-3 text-[10px] font-black uppercase tracking-[.2em] text-emerald-200/60">Cartas comunitarias</p><div class="flex min-h-[78px] sm:min-h-[105px] flex-wrap justify-center gap-1.5 sm:gap-2"><template x-for="(card,i) in communitySlots" :key="cardKey(card, 'community', i)"><div class="holdem-card" :class="card.placeholder?'border-dashed !bg-white/5 opacity-30':cardColor(card)" :style="`animation-delay:${i*.07}s`"><template x-if="!card.placeholder"><div><b x-text="rank(card.rank)"></b><i x-text="suit(card.suit)"></i></div></template></div></template></div></div>
                    <div class="text-center"><div class="mb-3 flex items-center justify-center gap-2"><b class="text-sm text-emerald-200">Tu mano</b><span x-show="hand?.player_hand" class="rounded-full bg-black/25 px-2 py-1 text-xs text-cyan-200" x-text="hand.player_hand"></span></div><div class="flex min-h-[118px] justify-center gap-2"><template x-for="(card,i) in (hand?.player||[])" :key="cardKey(card, 'player', i)"><div class="holdem-card" :class="cardColor(card)" :style="`animation-delay:${i*.08}s`"><b x-text="rank(card.rank)"></b><i x-text="suit(card.suit)"></i></div></template><template x-if="!hand"><div class="flex gap-2"><div class="holdem-card back opacity-40"></div><div class="holdem-card back opacity-40"></div></div></template></div></div>
                </div>
            </section>
            <div x-show="finished" class="mt-5 rounded-2xl border p-5 text-center" :class="hand?.result==='ganada'?'border-emerald-400/30 bg-emerald-400/10':hand?.result==='empate'?'border-amber-400/30 bg-amber-400/10':'border-red-400/20 bg-red-400/5'" aria-live="polite"><h2 class="text-2xl font-black capitalize" x-text="hand?.result"></h2><p x-show="hand?.dealer_hand" class="mt-1 text-sm text-slate-400"><span x-text="hand?.player_hand"></span> contra <span x-text="hand?.dealer_hand"></span></p><p x-show="hand?.result==='retirada'" class="mt-1 text-sm text-slate-400">Has conservado el saldo que todavía no estaba en la mesa.</p><b x-show="hand?.winnings>0" class="mt-2 block text-emerald-300" x-text="'Recibes '+money(hand.winnings)"></b></div>
        </main>

        <aside class="space-y-5">
            <section class="rounded-2xl border border-white/10 bg-white/[.035] p-5"><p class="text-[10px] font-black uppercase tracking-[.2em] text-brand-400" x-text="hand&&!finished?'Decisión actual':'Nueva partida'"></p><h2 class="mt-1 text-lg font-black" x-text="instruction"></h2>
                <template x-if="!hand||finished"><div class="mt-4"><label class="text-xs text-slate-500">Ante inicial</label><div class="mt-2 flex items-center rounded-xl border bg-black/20 px-3" :class="canStart?'border-white/10':'border-red-400/30'"><span>€</span><input x-model.number="ante" :disabled="busy" type="number" min="1" max="1000" step="1" class="w-full bg-transparent px-3 py-3 font-bold outline-none"></div><div class="mt-2 grid grid-cols-4 gap-1"><template x-for="v in [5,10,25,50]"><button @click="ante=v" :disabled="busy" class="rounded-lg bg-white/5 py-2 text-xs hover:bg-white/10 disabled:opacity-40" x-text="v+'€'"></button></template></div><button @click="start" :disabled="busy||!canStart" class="mt-4 w-full rounded-xl bg-gradient-to-r from-emerald-400 to-cyan-400 py-3.5 font-black text-slate-950 disabled:opacity-40" x-text="busy?'Repartiendo…':'Repartir nueva mano'"></button></div></template>
                <template x-if="hand&&!finished"><div class="mt-4"><div class="rounded-xl border border-white/10 bg-black/20 p-3 text-sm"><div class="flex justify-between"><span class="text-slate-500">Ante</span><b x-text="money(hand.ante)"></b></div><div class="mt-2 flex justify-between"><span class="text-slate-500">Total en mesa</span><b class="text-amber-300" x-text="money(hand.wagered)"></b></div></div>
                    <template x-if="hand.phase==='preflop'"><div class="mt-4 space-y-2"><button @click="act('jugar')" :disabled="busy||hand.ante>saldo" class="w-full rounded-xl bg-emerald-500 py-3 font-black text-slate-950 disabled:opacity-40">Jugar · añadir <span x-text="money(hand.ante)"></span></button><button @click="act('retirarse')" :disabled="busy" class="w-full rounded-xl bg-white/5 py-3 font-bold text-slate-400 disabled:opacity-40">Retirarme</button><p class="text-xs leading-relaxed text-slate-500">Jugar iguala el ante y revela el flop. Retirarte termina la mano sin cobrar más.</p></div></template>
                    <template x-if="hand.phase!=='preflop'"><div class="mt-4 space-y-3"><button @click="act('pasar')" :disabled="busy" class="w-full rounded-xl bg-cyan-400 py-3 font-black text-slate-950 disabled:opacity-40" x-text="checkLabel"></button><div class="rounded-xl border border-amber-300/15 bg-amber-300/5 p-3"><label class="text-[10px] font-black uppercase tracking-wider text-amber-200/70">Apuesta de esta calle</label><div class="mt-2 flex items-center rounded-lg border border-white/10 bg-black/20 px-3"><span>€</span><input x-model.number="streetBet" :disabled="busy" type="number" min="1" max="1000" step="1" class="w-full bg-transparent px-2 py-2 font-bold outline-none"></div><div class="mt-2 grid grid-cols-3 gap-1"><template x-for="multiplier in [.5,1,2]"><button @click="streetBet=Math.max(1, hand.ante*multiplier)" :disabled="busy" class="rounded-lg bg-white/5 py-1.5 text-[10px] hover:bg-white/10" x-text="multiplier+'x ante'"></button></template></div><button @click="act('apostar')" :disabled="busy||!canStreetBet" class="mt-2 w-full rounded-lg bg-amber-400 py-2.5 font-black text-slate-950 disabled:opacity-40">Apostar <span x-text="money(streetBet)"></span> y continuar</button></div><button @click="act('retirarse')" :disabled="busy" class="w-full rounded-xl bg-white/5 py-2.5 text-sm text-slate-400 disabled:opacity-40">Retirarme de la mano</button></div></template>
                </div></template>
                <p x-show="notice" role="status" class="mt-3 rounded-lg bg-cyan-500/10 p-2 text-center text-xs text-cyan-200" x-text="notice"></p><p x-show="error" role="alert" class="mt-3 rounded-lg bg-red-500/10 p-2 text-center text-xs text-red-300" x-text="error"></p>
            </section>
            <section class="rounded-2xl border border-white/10 bg-white/[.035] p-5"><h3 class="font-bold">Cómo se juega</h3><ol class="mt-3 space-y-3 text-xs text-slate-400"><li><b class="text-white">1.</b> El ante reparte dos cartas para ti y dos ocultas al dealer.</li><li><b class="text-white">2.</b> Iguala el ante para entrar en la mano y ver el flop.</li><li><b class="text-white">3.</b> En flop, turn y river puedes pasar, apostar o retirarte.</li><li><b class="text-white">4.</b> En el showdown, la mejor mano de cinco cartas gana el total apostado.</li></ol></section>
            <section class="rounded-2xl border border-white/10 bg-white/[.035] p-5"><h3 class="font-bold">Últimas manos</h3><div class="mt-3 space-y-2">@forelse($history as $item)@php($net = (float) $item->ganancia - (float) $item->apuesta)<div class="flex justify-between text-xs"><span class="text-slate-400">{{ ucfirst($item->detalles['resultado'] ?? 'finalizada') }}</span><b class="{{ $net > 0 ? 'text-emerald-300' : ($net < 0 ? 'text-red-300' : 'text-amber-300') }}">{{ $net > 0 ? '+' : ($net < 0 ? '-' : '±') }}€{{ number_format(abs($net), 2, ',', '.') }}</b></div>@empty<p class="text-xs text-slate-600">Aún no hay manos finalizadas.</p>@endforelse</div></section>
        </aside>
    </div>
</div>

@push('scripts')
<script>
function dealerPoker() {
    const active = @js($activeHand);

    return {
        hand: active,
        saldo: {{ auth()->user()->saldo }},
        ante: active?.ante ?? 10,
        streetBet: active?.ante ?? 10,
        busy: false,
        error: '',
        notice: active ? 'Hemos recuperado la mano que tenías en curso.' : '',
        reducedMotion: window.matchMedia('(prefers-reduced-motion: reduce)').matches,
        get finished() { return ['finalizada', 'retirada'].includes(this.hand?.phase); },
        get canStart() {
            const amount = Number(this.ante);
            return Number.isFinite(amount) && amount >= 1 && amount <= 1000 && amount <= this.saldo;
        },
        get canStreetBet() {
            const amount = Number(this.streetBet);
            return Number.isFinite(amount) && amount >= 1 && amount <= 1000 && amount <= this.saldo;
        },
        get phaseIndex() { return {preflop: 0, flop: 1, turn: 2, river: 3, finalizada: 4, retirada: 4}[this.hand?.phase] ?? 0; },
        get dealerCards() { return this.hand?.dealer || [{hidden: true}, {hidden: true}]; },
        get communitySlots() {
            return [...(this.hand?.community || []), ...Array.from({length: Math.max(0, 5 - (this.hand?.community?.length || 0))}, (_, index) => ({placeholder: true, slot: index}))];
        },
        get instruction() {
            if (!this.hand || this.finished) return 'Elige un ante fácil de controlar';
            return {
                preflop: '¿Juegas estas dos cartas?',
                flop: 'Decide después del flop',
                turn: 'Decide después del turn',
                river: 'Última decisión antes del showdown',
            }[this.hand.phase];
        },
        get checkLabel() {
            return {flop: 'Pasar y ver el turn', turn: 'Pasar y ver el river', river: 'Pasar e ir al showdown'}[this.hand?.phase];
        },
        money(value) { return new Intl.NumberFormat('es-ES', {style: 'currency', currency: 'EUR'}).format(Number(value) || 0); },
        rank(value) { return ({11: 'J', 12: 'Q', 13: 'K', 14: 'A'})[value] || value; },
        suit(value) { return ({S: '♠', H: '♥', D: '♦', C: '♣'})[value]; },
        cardColor(card) { return ['H', 'D'].includes(card.suit) ? 'red' : ''; },
        cardKey(card, group, index) { return card.rank ? `${group}-${card.rank}-${card.suit}` : `${group}-slot-${card.slot ?? index}`; },
        pause(ms) { return new Promise(resolve => setTimeout(resolve, ms)); },
        updateBalance(value) {
            this.saldo = Number(value);
            this.$store.wallet.saldo = this.saldo;
            window.dispatchEvent(new CustomEvent('saldo-updated', {detail: {saldo: this.saldo}}));
        },
        async syncHand() {
            const suffix = this.hand?.id ? `?hand_id=${encodeURIComponent(this.hand.id)}` : '';
            try {
                const response = await fetch(`${@js(route('games.poker.dealer.status'))}${suffix}`, {headers: {'Accept': 'application/json'}});
                if (!response.ok) return false;
                this.hand = await response.json();
                this.streetBet = this.hand.ante;
                this.updateBalance(this.hand.balance);
                this.notice = 'La mano se ha sincronizado correctamente con el servidor.';
                return true;
            } catch {
                return false;
            }
        },
        async request(url, body) {
            if (this.busy) return;
            this.busy = true;
            this.error = '';
            this.notice = '';
            const previous = this.hand?.community || [];

            try {
                const response = await fetch(url, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    },
                    body: JSON.stringify(body),
                });
                const data = await response.json().catch(() => ({}));
                if (!response.ok) {
                    const error = new Error(data.message || 'No se pudo completar la acción.');
                    error.status = response.status;
                    throw error;
                }

                const incoming = data.community || [];
                const isShowdown = data.phase === 'finalizada';
                this.hand = {
                    ...data,
                    phase: isShowdown ? this.hand?.phase : data.phase,
                    result: isShowdown ? null : data.result,
                    dealer: isShowdown ? [{hidden: true}, {hidden: true}] : data.dealer,
                    dealer_hand: isShowdown ? null : data.dealer_hand,
                    community: [...previous],
                };
                for (const card of incoming.slice(previous.length)) {
                    this.hand.community.push(card);
                    await this.pause(this.reducedMotion ? 0 : 180);
                }
                if (isShowdown) {
                    await this.pause(this.reducedMotion ? 0 : 260);
                    this.hand = data;
                }
                this.streetBet = data.ante;
                this.updateBalance(data.balance);
            } catch (error) {
                const shouldSync = error.status === 409 || error.status === undefined;
                if (!shouldSync || !await this.syncHand()) this.error = error.message;
            } finally {
                this.busy = false;
            }
        },
        start() {
            if (!this.canStart || this.busy) return;
            if (this.finished) this.hand = null;
            return this.request(@js(route('games.poker.dealer.start')), {ante: this.ante});
        },
        act(action) {
            if (!this.hand || this.finished || this.busy) return;
            return this.request(@js(route('games.poker.dealer.action')), {
                accion: action,
                fase: this.hand.phase,
                cantidad: action === 'apostar' ? this.streetBet : undefined,
            });
        },
    };
}
</script>
@endpush
@endsection
