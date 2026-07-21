@extends('layouts.game')
@section('title', $gameName . ' — Lootra Casino')

@section('styles')
<style>
    @keyframes deal-card { 0% { transform:translate3d(190px,-115px,80px) rotateZ(19deg) rotateY(38deg) scale(.64);opacity:0 } 68%{transform:translate3d(-5px,3px,4px) rotateZ(-2deg) rotateY(-3deg) scale(1.025)} 100% { transform:rotateX(2deg);opacity:1 } }
    @keyframes table-focus{0%,100%{opacity:.22;transform:translateX(-18%) skewX(-12deg)}50%{opacity:.5;transform:translateX(18%) skewX(-12deg)}}
    @keyframes win-aura{0%,100%{box-shadow:0 0 0 rgba(251,191,36,0)}50%{box-shadow:0 0 55px rgba(251,191,36,.24)}}
    .deal-card { position:relative;animation:deal-card .52s cubic-bezier(.16,1,.3,1) both;animation-delay:calc(var(--deal-index,0) * 90ms);box-shadow:0 16px 24px #0009,inset 0 0 0 1px #ffffffaa;backface-visibility:hidden;transform-origin:center bottom;transform-style:preserve-3d; }
    .casino-table{isolation:isolate;background:linear-gradient(180deg,rgba(2,8,6,.18),rgba(2,8,6,.42)),url('/images/lootra_visual_pack/realista/optimized/blackjack-table.webp') center/cover no-repeat,#031b12;box-shadow:inset 0 0 100px #0008,0 34px 80px #000b;border:1px solid rgba(243,211,137,.2);transform:translateZ(0)}
    .casino-table::before{content:"";position:absolute;z-index:-1;inset:-15%;background:linear-gradient(105deg,transparent 36%,rgba(255,229,170,.12) 47%,transparent 58%);animation:table-focus 10s ease-in-out infinite;pointer-events:none}
    .casino-table::after{content:"BLACKJACK PAYS 3 TO 2  •  DEALER STANDS ON 17";position:absolute;left:50%;top:49%;transform:translate(-50%,-50%);width:max-content;max-width:76%;color:rgba(241,216,160,.38);font-size:clamp(.48rem,1.2vw,.7rem);font-weight:900;letter-spacing:.18em;text-align:center;text-shadow:0 2px 8px #000;pointer-events:none}
    .table-vip{--table-accent:#e8c477}.table-classic{--table-accent:#8ed7ff}
    .hand-zone{position:relative;z-index:7;display:flex;min-height:150px;flex-direction:column;align-items:center;justify-content:center;perspective:900px}
    .hand-zone-player{margin-top:5rem}.hand-label{display:flex;align-items:center;gap:.5rem;margin-bottom:.75rem;border:1px solid rgba(255,255,255,.1);border-radius:999px;background:rgba(2,9,7,.72);padding:.38rem .8rem;box-shadow:0 8px 24px #0006;backdrop-filter:blur(10px)}
    .hand-cards{display:flex;min-height:112px;justify-content:center;padding-left:18px}.hand-cards .deal-card{margin-left:-18px}.hand-cards .deal-card:hover{z-index:20;transform:translateY(-7px) rotateX(0deg)}
    .playing-card{width:74px;height:104px;border-radius:10px;background:linear-gradient(145deg,#fffef8,#e8e2d4);border:1px solid #fff;transition:transform .2s ease}
    .playing-card.is-hidden{background:linear-gradient(145deg,#172a33,#071014);border-color:#d9bd71}
    .playing-card.is-hidden::after{content:"L";display:grid;position:absolute;inset:7px;place-items:center;border:1px solid #d9bd71;border-radius:6px;background:repeating-linear-gradient(45deg,#081a20 0 5px,#18343e 5px 10px);color:#ddc27e;font:900 1.4rem serif}
    .card-corner{position:absolute;left:7px;top:6px;display:flex;flex-direction:column;align-items:center;line-height:.88}.card-center{font-size:2rem;filter:drop-shadow(0 2px 1px #0002)}
    .result-plaque{position:absolute;z-index:30;left:50%;top:50%;transform:translate(-50%,-50%);animation:win-aura 1.5s ease-in-out infinite;border:1px solid rgba(255,255,255,.2);box-shadow:0 18px 60px #000c!important;backdrop-filter:blur(16px)}
    .blackjack-controls{background:linear-gradient(145deg,rgba(17,23,25,.96),rgba(5,8,10,.98));box-shadow:0 22px 55px #0007,inset 0 1px #fff1}
    .action-button{position:relative;overflow:hidden;border:1px solid rgba(244,215,151,.28);box-shadow:0 10px 25px #0007,inset 0 1px #fff2;text-transform:uppercase;letter-spacing:.08em}
    .action-button::after{content:"";position:absolute;inset:0;background:linear-gradient(110deg,transparent 20%,rgba(255,255,255,.2),transparent 70%);transform:translateX(-130%);transition:transform .55s ease}.action-button:hover::after{transform:translateX(130%)}
    .bet-chip{aspect-ratio:1;border-radius:999px;background:radial-gradient(circle at 35% 28%,#66532b,#18140e 50%,#050505 52%);border:2px dashed #d9bc77;box-shadow:0 7px 15px #0009,inset 0 0 0 3px #0c0c0c;color:#f3dfac;font-size:.68rem;font-weight:900;transition:transform .18s}.bet-chip:hover{transform:translateY(-4px) rotate(-3deg)}
    @media (max-width:639px) {
        .casino-table{border-radius:1.5rem;box-shadow:inset 0 0 45px #0009,0 22px 45px #0008}
        .casino-table::after{top:50%;font-size:.43rem;letter-spacing:.1em}
        .hand-zone{min-height:125px}.hand-zone-player{margin-top:3.5rem}.playing-card{width:60px;height:86px}.hand-cards{min-height:90px}
        .card-center{font-size:1.5rem}
    }
    @media (prefers-reduced-motion:reduce){.casino-table::before,.result-plaque{animation:none}.deal-card{animation-duration:.01ms}}
</style>
@endsection

@section('game-content')
<div class="mx-auto max-w-[1400px] px-4 pt-5 sm:px-6"><x-campaign.rickyedit.sidebar /></div>
<div class="max-w-[1400px] mx-auto px-4 sm:px-6 lg:px-8 py-6 sm:py-10"
     x-data="blackjackGame()" :aria-busy="busy.toString()">

    <div class="flex flex-col lg:flex-row gap-6">

        <div class="flex-1 min-w-0">

            {{-- Header --}}
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
                <div class="min-w-0">
                    <h1 class="game-heading font-extrabold">{{ $gameName }}</h1>
                    <p class="text-sm text-slate-500 mt-1">21 puntos — vence al dealer</p>
                </div>
                <div class="flex flex-wrap items-center gap-2"><a href="{{ route('games.blackjack.vip') }}" class="rounded-xl border px-3 py-2 text-xs font-bold {{ $variant === 'vip' ? 'border-amber-400/40 bg-amber-400/10 text-amber-300' : 'border-white/10 text-slate-400' }}">VIP</a><a href="{{ route('games.blackjack.classic') }}" class="rounded-xl border px-3 py-2 text-xs font-bold {{ $variant === 'classic' ? 'border-cyan-400/40 bg-cyan-400/10 text-cyan-300' : 'border-white/10 text-slate-400' }}">Classic</a>
                <div class="px-3 sm:px-4 py-2 rounded-xl bg-white/5 border border-white/10 whitespace-nowrap">
                    <span class="text-xs text-slate-500">Saldo</span>
                    <span class="ml-2 text-sm font-bold text-brand-400" x-text="'€' + saldo.toFixed(2)"></span>
                </div>
                </div>
            </div>

            {{-- Mesa --}}
            <div class="casino-table {{ $variant === 'vip' ? 'table-vip' : 'table-classic' }} game-stage rounded-[2rem] p-3 sm:p-8 mb-6 relative overflow-hidden min-h-[540px] sm:min-h-[650px]" :class="{'is-dealing':busy}">
                <div class="relative z-10">

                    {{-- Dealer --}}
                    <div class="hand-zone">
                        <div class="hand-label">
                            <span class="text-sm font-bold text-emerald-300">Dealer</span>
                            <span x-show="manoDealer.length > 0" class="text-sm text-emerald-400/70"
                                  x-text="'— ' + puntosDealer + ' pts'"></span>
                        </div>
                        <div class="hand-cards">
                            <template x-for="(carta, i) in manoDealer" :key="carta._key">
                                <div class="deal-card playing-card flex flex-col items-center justify-center text-sm font-bold"
                                     :class="carta.oculta ? 'is-hidden' : ''"
                                     :style="'--deal-index:' + (carta._dealIndex ?? i)">
                                    <template x-if="!carta.oculta">
                                        <div>
                                            <span class="card-corner" :class="isRed(carta)?'text-red-600':'text-slate-900'"><b x-text="carta.valor"></b><small x-text="carta.palo"></small></span><span class="card-center" :class="isRed(carta)?'text-red-600':'text-slate-900'" x-text="carta.palo"></span>
                                        </div>
                                    </template>
                                </div>
                            </template>
                            <div x-show="manoDealer.length === 0" class="text-emerald-700 text-sm">Esperando apuesta...</div>
                        </div>
                    </div>

                    {{-- Jugador --}}
                    <div class="hand-zone hand-zone-player">
                        <div class="hand-label">
                            <span class="text-sm font-bold text-emerald-300">Tu mano</span>
                            <span x-show="manoJugador.length > 0" class="text-sm text-emerald-400/70"
                                  x-text="'— ' + puntosJugador + ' pts'"
                                  :class="puntosJugador > 21 ? 'text-red-400' : ''"></span>
                        </div>
                        <div class="hand-cards">
                            <template x-for="(carta, i) in manoJugador" :key="carta._key">
                                <div class="deal-card playing-card flex flex-col items-center justify-center text-sm font-bold"
                                     :style="'--deal-index:' + (carta._dealIndex ?? i)">
                                    <span class="card-corner" :class="isRed(carta)?'text-red-600':'text-slate-900'"><b x-text="carta.valor"></b><small x-text="carta.palo"></small></span><span class="card-center" :class="isRed(carta)?'text-red-600':'text-slate-900'" x-text="carta.palo"></span>
                                </div>
                            </template>
                        </div>
                    </div>

                    {{-- Resultado --}}
                    <div x-show="estado !== 'jugando' && estado !== ''" class="text-center" aria-live="polite">
                        <div class="result-plaque inline-block px-7 py-4 rounded-2xl font-black text-lg"
                             :class="{
                                'bg-emerald-500/20 text-emerald-400 border border-emerald-500/30': estado === 'win' || estado === 'blackjack',
                                'bg-slate-500/20 text-slate-300 border border-slate-500/30': estado === 'push',
                                'bg-red-500/20 text-red-400 border border-red-500/30': estado === 'lose' || estado === 'bust',
                             }">
                            <span x-show="estado === 'blackjack'">¡BLACKJACK! </span>
                            <span x-show="estado === 'win'">¡Ganaste! </span>
                            <span x-show="estado === 'push'">Empate · apuesta devuelta</span>
                            <span x-show="estado === 'bust'">¡Te pasaste!</span>
                            <span x-show="estado === 'lose'">Dealer gana</span>
                            <span x-show="ganancia > 0 && estado !== 'push'" x-text="' · premio €' + ganancia.toFixed(2)"></span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Controles --}}
            <div class="blackjack-controls rounded-2xl border border-white/10 p-5">
                <div x-show="notice" x-transition class="mb-4 rounded-xl border border-cyan-400/20 bg-cyan-400/10 px-4 py-3 text-sm text-cyan-200" role="status" x-text="notice"></div>
                <div x-show="error" x-transition class="mb-4 rounded-xl border border-red-400/20 bg-red-400/10 px-4 py-3 text-sm text-red-200" role="alert" x-text="error"></div>
                <template x-if="fase === ''">
                    <div class="flex flex-col min-[420px]:flex-row min-[420px]:items-center gap-4">
                        <div class="flex-1">
                            <label class="text-xs text-slate-500 mb-1 block">Apuesta (€)</label>
                            <input type="number" x-model.number="apuesta" min="{{ $variant === 'vip' ? 5 : 1 }}" max="{{ $variant === 'vip' ? 5000 : 2000 }}" step="1"
                                   :class="canDeal ? 'border-white/10' : 'border-red-400/40'"
                                   class="w-full px-4 py-3 rounded-xl bg-white/5 border text-white text-sm outline-none focus:border-brand-500 transition">
                            <div class="mt-3 flex gap-2"><template x-for="value in [5,10,25,100]" :key="value"><button type="button" class="bet-chip h-11" @click="apuesta=Math.min(value,maxBet)" x-text="value+'€'"></button></template></div>
                        </div>
                        <div class="min-[420px]:mt-5">
                            <button @click="deal()" :disabled="busy || !canDeal"
                                    class="action-button w-full min-[420px]:w-auto px-9 py-4 rounded-xl bg-gradient-to-r from-[#f5dc92] to-[#b9792f] text-black font-black transition disabled:opacity-50">
                                <span x-text="busy ? 'Repartiendo…' : 'Repartir'"></span>
                            </button>
                        </div>
                    </div>
                </template>
                <template x-if="fase === 'jugando' && puntosJugador < 21">
                    <div class="flex items-center gap-3">
                        <button @click="hit()" :disabled="busy"
                                class="action-button flex-1 py-4 rounded-xl bg-gradient-to-b from-emerald-500 to-emerald-800 text-white font-black transition">
                            Pedir carta
                        </button>
                        <button @click="stand()" :disabled="busy"
                                class="action-button flex-1 py-4 rounded-xl bg-gradient-to-b from-red-500 to-red-900 text-white font-black transition">
                            Plantarse
                        </button>
                    </div>
                </template>
                <template x-if="fase === 'terminado'">
                    <div class="text-center">
                        <button @click="reset()"
                                class="px-8 py-3 rounded-xl bg-brand-500 hover:bg-brand-400 text-black font-bold transition shadow-lg shadow-brand-500/20">
                            Nueva mano
                        </button>
                    </div>
                </template>
            </div>
        </div>

        {{-- Sidebar --}}
        <aside class="w-full lg:w-72 shrink-0 space-y-5">
            <div class="rounded-2xl bg-white/[0.03] border border-white/5 p-5">
                <h3 class="text-sm font-bold text-white uppercase tracking-wider mb-3">Info</h3>
                <div class="space-y-3 text-sm">
                    <div class="flex justify-between"><span class="text-slate-500">Min apuesta</span><span class="text-white font-semibold">€{{ $variant === 'vip' ? '5' : '1' }}</span></div>
                    <div class="flex justify-between"><span class="text-slate-500">Max apuesta</span><span class="text-white font-semibold">€{{ $variant === 'vip' ? '5,000' : '2,000' }}</span></div>
                    <div class="flex justify-between"><span class="text-slate-500">Blackjack</span><span class="text-brand-400 font-bold">x2.5</span></div>
                    <div class="flex justify-between"><span class="text-slate-500">Barajas</span><span class="text-white font-semibold">{{ $variant === 'classic' ? '6' : '1' }}</span></div>
                    <div class="flex justify-between"><span class="text-slate-500">RTP</span><span class="text-emerald-400 font-semibold">{{ $variant === 'classic' ? '99.91%' : '99.28%' }}</span></div>
                </div>
            </div>
            <div class="rounded-2xl bg-gradient-to-br from-brand-500/20 to-brand-600/10 border border-brand-500/20 p-5">
                <h3 class="text-sm font-bold text-white mb-2">¿Cómo funciona?</h3>
                <p class="text-xs text-slate-400 leading-relaxed">Acércate a 21 puntos sin pasarte. Pide carta o plántate. Blackjack natural (A + 10) paga x2.5. El dealer se planta en 17.</p>
            </div>
            <div class="rounded-2xl bg-white/[0.03] border border-white/5 p-5">
                <h3 class="text-sm font-bold text-white uppercase tracking-wider mb-3">Historial</h3>
                <div class="space-y-2">
                    <template x-for="(h, i) in historial.slice(0, 8)" :key="i">
                        <div class="flex items-center justify-between text-xs">
                            <span class="text-slate-400" x-text="h.puntos + ' pts'"></span>
                            <span class="text-slate-500" x-text="h.dealer_puntos ? 'vs ' + h.dealer_puntos : ''"></span>
                            <span :class="netResult(h) > 0 ? 'text-emerald-400' : (netResult(h) < 0 ? 'text-red-400' : 'text-amber-300')"
                                  x-text="signedMoney(netResult(h))"></span>
                        </div>
                    </template>
                </div>
            </div>
        </aside>
    </div>
</div>

@push('scripts')
<script>
function blackjackGame() {
    const active = @js($activeHand);
    return {
        handId: active?.id ?? null,
        saldo: {{ $gameBalance }},
        apuesta: active?.apuesta ?? {{ $variant === 'vip' ? 10 : 5 }},
        minBet: {{ $variant === 'vip' ? 5 : 1 }},
        maxBet: {{ $variant === 'vip' ? 5000 : 2000 }},
        manoJugador: active?.mano_jugador ?? [],
        manoDealer: active?.mano_dealer ?? [],
        puntosJugador: active?.puntos_jugador ?? 0,
        puntosDealer: active?.puntos_dealer ?? 0,
        estado: active?.estado ?? '',
        fase: active ? 'jugando' : '',
        busy: false,
        ganancia: 0,
        error: '',
        notice: active ? 'Hemos recuperado la mano que tenías en curso.' : '',
        dealToken: null,
        cardSequence: 0,
        recordedHands: [],
        reducedMotion: window.matchMedia('(prefers-reduced-motion: reduce)').matches,
        historial: @js($partidas->take(10)->map(fn($p) => ['puntos' => $p->detalles['puntos_jugador'] ?? 0, 'dealer_puntos' => $p->detalles['puntos_dealer'] ?? 0, 'ganancia' => $p->ganancia, 'apuesta' => $p->apuesta])->all()),
        get canDeal() {
            const bet = Number(this.apuesta);
            return Number.isFinite(bet) && bet >= this.minBet && bet <= this.maxBet && bet <= this.saldo;
        },
        init() {
            this.manoJugador = this.decorateCards(this.manoJugador, 'player-active');
            this.manoDealer = this.decorateCards(this.manoDealer, 'dealer-active');
        },
        isRed(card) { return ['♥','♦'].includes(card.palo); },
        netResult(hand) { return Number(hand.ganancia) - Number(hand.apuesta); },
        signedMoney(value) {
            const amount = Number(value);
            if (amount === 0) return '±€0.00';
            return `${amount > 0 ? '+' : '-'}€${Math.abs(amount).toFixed(2)}`;
        },
        decorateCards(cards, group) {
            const sequence = ++this.cardSequence;
            return (cards || []).map((card, index) => ({ ...card, _key: `${group}-${sequence}-${index}`, _dealIndex: index }));
        },
        requestToken() {
            const webCrypto = globalThis.crypto;
            if (typeof webCrypto?.randomUUID === 'function') return webCrypto.randomUUID();
            const bytes = new Uint8Array(16);
            webCrypto.getRandomValues(bytes);
            bytes[6] = (bytes[6] & 15) | 64;
            bytes[8] = (bytes[8] & 63) | 128;
            return Array.from(bytes, (byte, index) => ([4,6,8,10].includes(index) ? '-' : '') + byte.toString(16).padStart(2, '0')).join('');
        },
        async request(url, options = {}) {
            const response = await fetch(url, {
                method: options.method || 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json',
                },
                body: options.body === undefined ? undefined : JSON.stringify(options.body),
            });
            const data = await response.json().catch(() => ({}));
            if (!response.ok) {
                const error = new Error(data.error || data.message || 'No se pudo completar la acción.');
                error.status = response.status;
                error.data = data;
                throw error;
            }
            return data;
        },
        updateBalance(value) {
            const balance = Number(value);
            if (!Number.isFinite(balance) || balance === this.saldo) return;
            this.saldo = balance;
            Alpine.store('wallet').saldo = balance;
            window.dispatchEvent(new CustomEvent('saldo-updated', { detail: { saldo: balance } }));
        },

        async deal() {
            if (this.busy || this.fase !== '' || !this.canDeal) return;
            this.busy = true;
            this.estado = '';
            this.ganancia = 0;
            this.error = '';
            this.notice = '';
            this.dealToken ||= this.requestToken();

            try {
                const data = await this.request(@js($dealRoute), { body: { apuesta: this.apuesta, request_token: this.dealToken } });
                this.dealToken = null;
                this.handId = data.id;
                this.manoJugador = this.decorateCards(data.mano_jugador, 'player-deal');
                this.manoDealer = this.decorateCards(data.mano_dealer, 'dealer-deal');
                this.puntosJugador = data.puntos_jugador;
                this.puntosDealer = data.puntos_dealer;
                this.updateBalance(data.saldo);
                this.fase = data.estado === 'jugando' ? 'jugando' : 'terminado';
                await this.pause(this.reducedMotion ? 0 : 410);
                this.estado = data.estado;

                if (data.estado !== 'jugando') this.finishClientHand(data);
            } catch (e) {
                if (e.status === 409 && e.data?.active_hand) {
                    this.applyRecoveredHand(e.data.active_hand);
                    this.notice = e.message;
                    this.dealToken = null;
                } else if (!await this.syncHand({ requestToken: this.dealToken })) {
                    this.error = e.message;
                }
            } finally { this.busy = false; }
        },

        async hit() {
            if (this.busy || this.fase !== 'jugando') return;
            this.busy = true;
            this.error = '';
            this.notice = '';
            try {
                const data = await this.request(@js($hitRoute), { body: {} });

                const newCards = data.mano_jugador.slice(this.manoJugador.length);
                this.manoJugador = [...this.manoJugador, ...this.decorateCards(newCards, 'player-hit')];
                this.puntosJugador = data.puntos_jugador;
                await this.pause(this.reducedMotion ? 0 : 350);

                if (data.estado !== 'jugando') {
                    await this.revealDealer(data.mano_dealer || this.manoDealer);
                    this.finishClientHand(data);
                } else {
                    this.puntosDealer = data.puntos_dealer;
                }
            } catch (e) {
                if (!await this.syncHand({ handId: this.handId })) this.error = e.message;
            } finally { this.busy = false; }
        },

        async stand() {
            if (this.busy || this.fase !== 'jugando') return;
            this.busy = true;
            this.error = '';
            this.notice = '';

            try {
                const data = await this.request(@js($standRoute), { body: {} });
                await this.revealDealer(data.mano_dealer);
                this.finishClientHand(data);
            } catch (e) {
                if (!await this.syncHand({ handId: this.handId })) this.error = e.message;
            } finally { this.busy = false; }
        },

        reset() {
            this.handId = null;
            this.manoJugador = [];
            this.manoDealer = [];
            this.puntosJugador = 0;
            this.puntosDealer = 0;
            this.estado = '';
            this.fase = '';
            this.ganancia = 0;
            this.error = '';
            this.notice = '';
            this.dealToken = null;
        },
        pause(ms) { return new Promise(resolve => setTimeout(resolve, ms)); },
        async revealDealer(cards) {
            if (!cards?.length) { this.manoDealer = []; return; }
            const firstKey = this.manoDealer[0]?._key || `dealer-first-${++this.cardSequence}`;
            this.manoDealer = [
                { ...cards[0], _key: firstKey },
                ...this.decorateCards(cards.slice(1), 'dealer-reveal'),
            ];
            await this.pause(this.reducedMotion ? 0 : 350 + Math.max(0, cards.length - 2) * 55);
        },
        finishClientHand(data) {
            this.handId = data.id ?? this.handId;
            this.puntosJugador = Number(data.puntos_jugador);
            this.puntosDealer = Number(data.puntos_dealer);
            this.estado = data.estado;
            this.ganancia = Number(data.ganancia || 0);
            this.fase = 'terminado';
            this.updateBalance(data.saldo);
            if (!this.recordedHands.includes(this.handId)) {
                this.recordedHands.push(this.handId);
                this.historial.unshift({
                    puntos: this.puntosJugador,
                    dealer_puntos: this.puntosDealer,
                    ganancia: this.ganancia,
                    apuesta: Number(data.apuesta ?? this.apuesta),
                });
            }
        },
        applyRecoveredHand(data) {
            this.handId = data.id;
            this.apuesta = Number(data.apuesta);
            this.manoJugador = this.decorateCards(data.mano_jugador, 'player-sync');
            this.manoDealer = this.decorateCards(data.mano_dealer, 'dealer-sync');
            this.puntosJugador = Number(data.puntos_jugador);
            this.puntosDealer = Number(data.puntos_dealer);
            this.estado = data.estado;
            this.ganancia = Number(data.ganancia || 0);
            this.fase = data.estado === 'jugando' ? 'jugando' : 'terminado';
            this.updateBalance(data.saldo);
            if (data.estado !== 'jugando') this.finishClientHand(data);
        },
        async syncHand({ handId = null, requestToken = null } = {}) {
            const params = new URLSearchParams();
            if (handId) params.set('hand_id', handId);
            else if (requestToken) params.set('request_token', requestToken);
            else return false;
            try {
                const data = await this.request(`${@js($statusRoute)}?${params}`, { method: 'GET' });
                if (data.estado === 'jugando') {
                    this.applyRecoveredHand(data);
                } else {
                    this.manoJugador = this.decorateCards(data.mano_jugador, 'player-recovered');
                    await this.revealDealer(data.mano_dealer);
                    this.finishClientHand(data);
                }
                this.notice = 'La mano se ha sincronizado correctamente con el servidor.';
                if (requestToken) this.dealToken = null;
                return true;
            } catch {
                return false;
            }
        },
    };
}
</script>
@endpush
@endsection
