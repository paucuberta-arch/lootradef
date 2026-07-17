@extends('layouts.game')
@section('title', 'Poker All-In — Lootra')
@section('styles')
<style>
    @keyframes deal-pro{from{opacity:0;transform:translate(120px,-60px) rotate(14deg) scale(.7)}to{opacity:1;transform:none}}
    .allin-table{background:radial-gradient(ellipse at center,#174f3a,#041d16 72%);border:14px solid #351408;border-bottom-width:24px;box-shadow:inset 0 0 90px #0009,0 35px 90px #000b}
    .poker-card{width:clamp(55px,8vw,82px);aspect-ratio:.7;border-radius:10px;background:linear-gradient(145deg,#fff,#e2e8f0);color:#111827;position:relative;box-shadow:0 14px 28px #0009;border:1px solid white;animation:deal-pro .5s cubic-bezier(.16,1,.3,1) both}.poker-card.red{color:#dc2626}.poker-card.back{background:repeating-linear-gradient(45deg,#111827 0 7px,#312e81 7px 14px);border:4px solid #d4af37}.poker-card b{position:absolute;left:7px;top:5px}.poker-card i{position:absolute;inset:0;display:grid;place-items:center;font-size:30px;font-style:normal}
    @media (max-width:639px){
        .allin-table{border-width:6px;border-bottom-width:10px;border-radius:1.5rem}
        .poker-card{width:clamp(38px,12vw,50px);border-radius:7px}
        .poker-card.back{border-width:2px}.poker-card b{left:4px;top:3px;font-size:12px}.poker-card i{font-size:21px}
    }
</style>
@endsection
@section('game-content')
<div class="mx-auto max-w-[1350px] px-4 py-8" x-data="pokerAllIn()">
    <header class="mb-6 flex flex-wrap items-center justify-between gap-4"><div class="min-w-0"><p class="text-xs font-black uppercase tracking-[.2em] text-fuchsia-400">Resolución instantánea</p><h1 class="game-heading mt-1 font-black">Poker All-In</h1><p class="mt-2 text-sm text-slate-500">Una apuesta, una mano completa y showdown inmediato.</p></div><div class="flex w-full flex-wrap gap-2 sm:w-auto"><a href="{{ route('games.poker.dealer') }}" class="rounded-xl border border-white/10 px-3 py-2 text-xs text-slate-400">Jugar contra el dealer</a><span class="rounded-xl border border-brand-400/20 bg-brand-400/10 px-3 sm:px-4 py-2 text-sm whitespace-nowrap">Saldo <b class="text-brand-300" x-text="money(saldo)"></b></span></div></header>
    <section class="allin-table rounded-[3rem] p-3 sm:p-9">
        <div class="grid min-h-[470px] sm:min-h-[590px] content-between gap-5 sm:gap-7 text-center">
            <div><div class="mb-3 flex justify-center gap-2"><b class="text-sm text-emerald-200">Dealer</b><span x-show="result?.dealer_hand" class="rounded-full bg-black/20 px-2 text-xs text-amber-200" x-text="result?.dealer_hand"></span></div><div class="flex min-h-[112px] justify-center gap-2"><template x-for="(c,i) in result?.dealer||[{},{ }]"><div class="poker-card" :class="result?.dealer?color(c):'back'" :style="`animation-delay:${i*.12}s`"><template x-if="result?.dealer"><div><b x-text="rank(c.rank)"></b><i x-text="suit(c.suit)"></i></div></template></div></template></div></div>
            <div class="rounded-2xl sm:rounded-3xl border border-white/10 bg-black/15 p-3 sm:p-5"><p class="mb-3 text-[10px] font-black uppercase tracking-[.2em] text-emerald-200/60">Mesa</p><div class="flex min-h-[78px] sm:min-h-[105px] flex-wrap justify-center gap-1.5 sm:gap-2"><template x-for="(c,i) in result?.community||Array(5).fill({hidden:true})"><div class="poker-card" :class="result?color(c):'back opacity-30'" :style="`animation-delay:${.2+i*.1}s`"><template x-if="result"><div><b x-text="rank(c.rank)"></b><i x-text="suit(c.suit)"></i></div></template></div></template></div></div>
            <div><div class="mb-3 flex justify-center gap-2"><b class="text-sm text-emerald-200">Tu mano</b><span x-show="result" class="rounded-full bg-black/20 px-2 text-xs text-cyan-200" x-text="result?.player_hand"></span></div><div class="flex min-h-[112px] justify-center gap-2"><template x-for="(c,i) in result?.player||[{},{ }]"><div class="poker-card" :class="result?color(c):'back opacity-50'" :style="`animation-delay:${i*.12}s`"><template x-if="result"><div><b x-text="rank(c.rank)"></b><i x-text="suit(c.suit)"></i></div></template></div></template></div></div>
        </div>
    </section>
    <section class="mx-auto mt-5 max-w-2xl rounded-2xl border border-white/10 bg-[#10101d] p-5"><div x-show="result" class="mb-4 rounded-xl p-3 text-center" :class="result?.ganancia>result?.apuesta?'bg-emerald-500/10 text-emerald-300':result?.ganancia==result?.apuesta?'bg-amber-500/10 text-amber-300':'bg-red-500/10 text-red-300'" aria-live="polite"><b x-text="message"></b></div><label class="text-xs text-slate-500">Importe del all-in</label><div class="mt-2 flex items-center rounded-xl border bg-black/20 px-3" :class="canDeal?'border-white/10':'border-red-400/30'"><span>€</span><input x-model.number="apuesta" :disabled="playing" type="number" min=".2" max="500" step=".2" class="w-full bg-transparent px-3 py-3 font-bold outline-none"></div><div class="mt-2 grid grid-cols-5 gap-1"><template x-for="v in [2,5,10,25,50]"><button @click="apuesta=v" :disabled="playing" class="rounded-lg bg-white/5 py-2 text-xs hover:bg-white/10 disabled:opacity-40" x-text="v+'€'"></button></template></div><button @click="deal" :disabled="playing||!canDeal" class="mt-4 w-full rounded-xl bg-gradient-to-r from-fuchsia-500 to-brand-400 py-3.5 font-black text-slate-950 disabled:opacity-40" x-text="playing?'Repartiendo…':'Ir All-In'"></button><p x-show="error" role="alert" class="mt-3 rounded-lg bg-red-500/10 p-2 text-center text-xs text-red-300" x-text="error"></p></section>
</div>
@push('scripts')
<script>
function pokerAllIn() {
    return {
        saldo: {{ auth()->user()->saldo }},
        apuesta: 10,
        playing: false,
        result: null,
        error: '',
        roundToken: null,
        reducedMotion: window.matchMedia('(prefers-reduced-motion: reduce)').matches,
        get canDeal() {
            const bet = Number(this.apuesta);
            return Number.isFinite(bet) && bet >= .2 && bet <= 500 && bet <= this.saldo;
        },
        get message() {
            if (!this.result?.dealer_hand) return '';
            if (this.result.ganancia > this.result.apuesta) return `Has ganado ${this.money(this.result.ganancia)} con ${this.result.player_hand}`;
            return this.result.ganancia == this.result.apuesta ? 'Empate · apuesta devuelta' : `Gana el dealer con ${this.result.dealer_hand}`;
        },
        money(value) { return new Intl.NumberFormat('es-ES', { style: 'currency', currency: 'EUR' }).format(Number(value) || 0); },
        rank(value) { return ({11: 'J', 12: 'Q', 13: 'K', 14: 'A'})[value] || value; },
        suit(value) { return ({S: '♠', H: '♥', D: '♦', C: '♣'})[value]; },
        color(card) { return ['H', 'D'].includes(card.suit) ? 'red' : ''; },
        pause(ms) { return new Promise(resolve => setTimeout(resolve, ms)); },
        requestToken() {
            const webCrypto = globalThis.crypto;
            if (typeof webCrypto?.randomUUID === 'function') return webCrypto.randomUUID();
            const bytes = new Uint8Array(16);
            webCrypto.getRandomValues(bytes);
            bytes[6] = (bytes[6] & 15) | 64;
            bytes[8] = (bytes[8] & 63) | 128;
            return Array.from(bytes, (byte, index) => ([4, 6, 8, 10].includes(index) ? '-' : '') + byte.toString(16).padStart(2, '0')).join('');
        },
        updateBalance(value) {
            this.saldo = Number(value);
            this.$store.wallet.saldo = this.saldo;
            window.dispatchEvent(new CustomEvent('saldo-updated', { detail: { saldo: this.saldo } }));
        },
        async deal() {
            if (this.playing || !this.canDeal) return;
            this.playing = true;
            this.result = null;
            this.error = '';
            this.roundToken ||= this.requestToken();

            try {
                const response = await fetch(@js(route('games.poker.all-in.play')), {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    },
                    body: JSON.stringify({ apuesta: this.apuesta, request_token: this.roundToken }),
                });
                const data = await response.json().catch(() => ({}));
                if (!response.ok) throw new Error(data.message || 'La mesa no está disponible.');

                this.roundToken = null;
                const delay = this.reducedMotion ? 0 : 170;
                this.result = { ...data, community: [], dealer: null };
                for (const card of data.community) {
                    this.result.community.push(card);
                    await this.pause(delay);
                }
                this.result.dealer = data.dealer;
                await this.pause(delay);
                this.result = data;
                this.updateBalance(data.saldo);
            } catch (error) {
                this.error = error.message;
            } finally {
                this.playing = false;
            }
        },
    };
}
</script>
@endpush
@endsection
