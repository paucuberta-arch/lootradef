@extends('layouts.game')
@section('title', $gameName . ' — Lootra Casino')

@section('styles')
<style>
    @keyframes reel-scroll { 0% { transform:translateY(-34%) scale(.96); } 100% { transform:translateY(34%) scale(1.04); } }
    @keyframes reel-lock { 0%{transform:translateY(-55%) scale(.92)} 65%{transform:translateY(8%) scale(1.05)} 82%{transform:translateY(-4%) scale(.98)} 100%{transform:none} }
    .reel-container {
        overflow: hidden;
        position: relative;
        background: radial-gradient(circle at 50% 40%, rgba(255,255,255,.25), rgba(3,7,18,.94));
        box-shadow: inset 0 0 30px rgba(0,0,0,.85), inset 0 2px 2px rgba(255,255,255,.35), 0 12px 30px rgba(0,0,0,.45);
    }
    .reel-container::before{content:"";position:absolute;z-index:2;inset:0;background:linear-gradient(105deg,rgba(255,255,255,.26),transparent 20% 70%,rgba(0,0,0,.2));pointer-events:none}
    .reel-container::after{content:"";position:absolute;z-index:2;left:0;right:0;top:50%;height:25%;transform:translateY(-50%);border-top:1px solid rgba(255,255,255,.28);border-bottom:1px solid rgba(0,0,0,.45);background:linear-gradient(180deg,transparent,rgba(255,255,255,.055),transparent);pointer-events:none}
    .reel-strip { position:absolute; inset:7%; display:grid; place-items:center; transition:filter .2s ease; }
    .reel-strip.spinning { animation:reel-scroll .16s linear infinite alternate; filter:blur(2px) saturate(1.2) brightness(1.15); will-change:transform; }
    .reel-strip.stopping { animation:reel-lock .58s cubic-bezier(.16,1,.3,1); }
    .win-flash { border-color: rgba(245,158,11,0.5) !important; }
    @keyframes win-bounce { 0%,100% { transform: scale(1); } 50% { transform: scale(1.15); } }
    .win-bounce { animation: win-bounce 0.4s ease-in-out 3; }
    @keyframes coin-rain {
        0% { transform: translateY(-20px) rotate(0deg); opacity: 1; }
        100% { transform: translateY(200px) rotate(720deg); opacity: 0; }
    }
    .coin-particle {
        animation: coin-rain 1.2s ease-in forwards;
        position: absolute;
        pointer-events: none;
        width:14px;height:14px;border-radius:50%;background:radial-gradient(circle at 35% 30%,#fff7a8,#f59e0b 48%,#92400e);box-shadow:0 0 6px #fbbf24;will-change:transform,opacity;
    }
    .slot-cabinet { background:linear-gradient(145deg,rgba(29,11,55,.94),rgba(5,8,25,.98));box-shadow:inset 0 0 55px rgba(217,70,239,.18),0 35px 90px rgba(0,0,0,.65); }
    .slot-cabinet::before { content:""; position:absolute; inset:0; background:var(--game-art) center/cover; opacity:.22; mix-blend-mode:screen;filter:saturate(1.2); }
    .slot-cabinet::after{content:"";position:absolute;inset:0;background:radial-gradient(circle at 50% 25%,rgba(255,255,255,.13),transparent 38%);pointer-events:none}
    .slot-marquee{position:relative;z-index:2;display:flex;align-items:center;justify-content:space-between;gap:.6rem;margin:-.1rem 0 .8rem;border-bottom:1px solid rgba(255,255,255,.11);padding:0 .15rem .65rem;color:rgba(247,214,145,.8);font-size:.58rem;font-weight:900;letter-spacing:.16em;text-transform:uppercase}
    .slot-marquee span:first-child{display:flex;align-items:center;gap:.45rem}.slot-marquee i{display:block;width:.4rem;height:.4rem;border-radius:50%;background:#f0ab42;box-shadow:0 0 11px #f0ab42}
    .slot-symbol-art{display:block;width:100%;aspect-ratio:1;border-radius:14px;background-repeat:no-repeat;background-color:rgba(2,6,23,.2);filter:drop-shadow(0 10px 8px rgba(0,0,0,.48));transform:translateZ(0)}
    .slot-symbol-frame{display:grid;aspect-ratio:1;place-items:center;overflow:hidden;border:1px solid rgba(255,255,255,.08);background:radial-gradient(circle,rgba(255,255,255,.08),rgba(2,6,23,.28));}
    .reel-shine { position:absolute; inset:0; background:linear-gradient(110deg,transparent 25%,rgba(255,255,255,.45) 48%,transparent 70%); transform:translateX(-100%); pointer-events:none; z-index:3; }
    .reel-strip.spinning ~ .reel-shine { animation:shimmer .7s linear infinite; }
    @media (max-width:639px){.slot-cabinet{border-radius:1.15rem}.slot-symbol-art{border-radius:10px}.reel-strip{inset:5%}}
</style>
@endsection

@section('game-content')
<div class="mx-auto max-w-[1400px] px-4 pt-5 sm:px-6"><x-campaign.rickyedit.banner variant="horizontal" /></div>
<div class="max-w-[1400px] mx-auto px-4 sm:px-6 lg:px-8 py-6 sm:py-10"
     x-data="slotsGame()" x-init="initReels()">

    <div class="flex flex-col lg:flex-row gap-6">

        <div class="flex-1 min-w-0">

            {{-- Header --}}
            <div class="mb-6 flex flex-wrap items-start justify-between gap-3">
                <div class="min-w-0 flex-1">
                    <div class="flex items-center gap-3"><span class="w-11 h-11 rounded-xl bg-gradient-to-br from-fuchsia-500 to-cyan-400 grid place-items-center shadow-lg shadow-fuchsia-500/20"><svg class="w-6 h-6 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M13 2 4 14h7l-1 8 10-13h-7V2Z" stroke-width="2" stroke-linejoin="round"/></svg></span><h1 class="game-heading font-extrabold">{{ $gameName }}</h1></div>
                    <p class="text-sm text-slate-500 mt-1">{{ $gameTagline }}</p>
                </div>
                <div class="balance-chip shrink-0 px-3 py-2 sm:px-4 rounded-xl">
                    <span class="text-xs text-slate-500">Saldo</span>
                    <span class="ml-2 text-sm font-bold text-brand-400" x-text="'€' + saldo.toFixed(2)"></span>
                </div>
            </div>

            {{-- Maquina tragaperras --}}
            <div class="game-stage rounded-[1.75rem] bg-white/[0.03] border border-white/5 p-3 min-[420px]:p-5 sm:p-8 mb-6 overflow-hidden">
                <div class="max-w-md mx-auto">
                    {{-- Carretes --}}
                    <div class="slot-cabinet rounded-[1.6rem] border-2 border-fuchsia-400/20 p-3 min-[420px]:p-5 mb-6 relative overflow-hidden"
                         style="--game-art:url('{{ $gameHero }}')"
                         :class="ganancia > 0 && !spinning ? 'win-flash' : ''">
                        <div class="slot-marquee"><span><i aria-hidden="true"></i> Lootra · 3 reels</span><span x-text="spinning ? 'Spin in progress' : (lastResult ? 'Round complete' : 'Ready')"></span></div>
                        <div class="grid grid-cols-3 gap-3">
                            <template x-for="(reel, i) in reels" :key="i">
                                <div class="reel-container aspect-square rounded-xl border border-white/40 flex items-center justify-center"
                                     :class="ganancia > 0 && !spinning ? 'win-bounce border-brand-500/50' : ''">
                                    <div class="reel-strip" :id="'reel-' + i"
                                         :class="reelSpinning[i] ? 'spinning' : (reelStopping[i] ? 'stopping' : '')">
                                        <div class="slot-symbol-art" :style="symbolStyle(reel)"></div>
                                    </div>
                                    <div class="reel-shine"></div>
                                </div>
                            </template>
                        </div>

                        {{-- Linea central --}}
                        <div class="absolute left-0 right-0 top-1/2 -translate-y-1/2 h-0.5 bg-brand-500/30 pointer-events-none"></div>

                        {{-- Coin rain effect --}}
                        <template x-if="showCoins">
                            <div class="absolute inset-0 pointer-events-none">
                                <template x-for="c in coinParticles" :key="c.id">
                                    <span class="coin-particle" :style="'left:' + c.x + '%; top:' + c.y + '%; animation-delay:' + c.delay + 's'"></span>
                                </template>
                            </div>
                        </template>
                    </div>

                    {{-- Resultado --}}
                    <div x-show="lastResult" class="text-center mb-4">
                        <template x-if="ganancia > 0">
                            <div class="text-emerald-400 font-bold text-lg win-bounce">
                                Premio bruto <span x-text="'€' + ganancia.toFixed(2)"></span><span class="ml-1 text-sm text-emerald-200/70" x-show="payoutMultiplier > 0" x-text="'· x' + payoutMultiplier.toFixed(2)"></span>
                            </div>
                        </template>
                        <template x-if="ganancia === 0 && lastResult">
                            <div class="text-red-400 font-bold text-lg">Sin suerte esta vez</div>
                        </template>
                        <p class="mt-1 text-xs font-bold" :class="netResult > 0 ? 'text-emerald-200' : (netResult === 0 ? 'text-amber-200' : 'text-slate-400')" x-text="netMessage"></p>
                    </div>
                    <p x-show="error" class="text-red-400 text-sm text-center mb-4" x-text="error"></p>

                    {{-- Controles --}}
                    <div class="flex flex-col gap-3 min-[420px]:flex-row min-[420px]:items-end">
                        <div class="flex-1">
                            <label class="text-xs text-slate-500 mb-1 block">Apuesta (€)</label>
                            <input type="number" x-model.number="apuesta" min="0.10" max="500" step="0.10"
                                   :disabled="spinning"
                                   class="w-full px-4 py-3 rounded-xl bg-white/5 border border-white/10 text-white text-sm outline-none focus:border-brand-500 transition disabled:opacity-50">
                        </div>
                        <div>
                            <button @click="spin()" :disabled="spinning || apuesta > saldo"
                                    class="cta-shine w-full min-[420px]:w-auto px-8 py-3 rounded-xl bg-gradient-to-r from-brand-400 via-orange-400 to-fuchsia-500 hover:scale-105 text-black font-bold transition shadow-lg shadow-fuchsia-500/20 disabled:opacity-50 disabled:cursor-not-allowed">
                                <span x-text="spinning ? 'Girando...' : 'Girar'"></span>
                            </button>
                        </div>
                    </div>

                    {{-- Botones apuesta rapida --}}
                    <div class="grid grid-cols-3 gap-2 mt-3 min-[420px]:grid-cols-5">
                        <template x-for="val in [0.50, 1, 2, 5, 10]" :key="val">
                            <button @click="apuesta = val" :disabled="spinning"
                                    class="py-2 rounded-lg bg-white/5 border border-white/10 text-xs text-slate-400 hover:text-white hover:bg-white/10 transition disabled:opacity-50"
                                    x-text="'€' + val">
                            </button>
                        </template>
                    </div>
                </div>
            </div>

            {{-- Paytable --}}
            <div class="rounded-2xl bg-white/[0.03] border border-white/5 p-5">
                <h3 class="text-sm font-bold text-white uppercase tracking-wider mb-4">Tabla de pagos</h3>
                <div class="grid auto-rows-fr grid-cols-2 sm:grid-cols-3 gap-3 text-sm">
                    <template x-for="p in paytable" :key="p.label">
                        <div class="flex h-full flex-col items-center rounded-xl bg-white/[0.02] border border-white/5 p-3 text-center">
                            <div class="slot-symbol-frame mx-auto mb-3 h-14 w-14 rounded-xl"><div class="slot-symbol-art" :style="symbolStyle(p.icon)"></div></div>
                            <div class="mb-1 mt-auto text-[10px] uppercase tracking-wider text-slate-500" x-text="p.symbols === 'X X X' ? 'Cualquier pareja' : '3 símbolos'"></div>
                            <div class="font-bold text-brand-400" x-text="p.label"></div>
                        </div>
                    </template>
                </div>
            </div>
        </div>

        {{-- Sidebar --}}
        <aside class="w-full lg:w-72 shrink-0 space-y-5">
            <div class="rounded-2xl bg-white/[0.03] border border-white/5 p-5">
                <h3 class="text-sm font-bold text-white uppercase tracking-wider mb-3">Info</h3>
                <div class="space-y-3 text-sm">
                    <div class="flex justify-between"><span class="text-slate-500">Min apuesta</span><span class="text-white font-semibold">€0.10</span></div>
                    <div class="flex justify-between"><span class="text-slate-500">Max apuesta</span><span class="text-white font-semibold">€500</span></div>
                    <div class="flex justify-between"><span class="text-slate-500">Max win</span><span class="text-brand-400 font-bold">x50</span></div>
                    <div class="flex justify-between"><span class="text-slate-500">Carretes</span><span class="text-white font-semibold">3x3</span></div>
                    <div class="flex justify-between"><span class="text-slate-500">RTP teórico</span><span class="text-emerald-400 font-semibold">{{ $rtp }}%</span></div>
                    <div class="flex justify-between"><span class="text-slate-500">Proveedor</span><span class="text-white font-semibold">{{ $gameProvider }}</span></div>
                </div>
            </div>
            <div class="rounded-2xl bg-gradient-to-br from-brand-500/20 to-brand-600/10 border border-brand-500/20 p-5">
                <h3 class="text-sm font-bold text-white mb-2">¿Cómo funciona?</h3>
                <p class="text-xs text-slate-400 leading-relaxed">Gira los 3 carretes. Consigue 3 iguales para ganar el maximo premio. Dos iguales consecutivos también pagan x2.</p>
            </div>

            {{-- Historial --}}
            <div class="rounded-2xl bg-white/[0.03] border border-white/5 p-5">
                <h3 class="text-sm font-bold text-white uppercase tracking-wider mb-3">Historial</h3>
                <div class="space-y-2">
                    <template x-for="(h, i) in historial.slice(0, 8)" :key="i">
                        <div class="flex items-center justify-between text-xs">
                            <span class="flex gap-1"><template x-for="symbol in h.reels"><span class="slot-symbol-frame h-7 w-7 rounded-md"><i class="slot-symbol-art" :style="symbolStyle(symbol)"></i></span></template></span>
                            <span :class="h.ganancia > 0 ? 'text-emerald-400' : 'text-red-400'"
                                  x-text="Number(h.ganancia) > 0 ? '+€' + Number(h.ganancia).toFixed(2) : '-€' + Number(h.apuesta).toFixed(2)"></span>
                        </div>
                    </template>
                </div>
            </div>
        </aside>
    </div>
</div>

@push('scripts')
<script>
function slotsGame() {
    return {
        saldo: {{ $saldo }},
        apuesta: 1,
        roundBet: 0,
        reels: [],
        reelSpinning: [false, false, false],
        reelStopping: [false, false, false],
        spinning: false,
        ganancia: 0,
        payoutMultiplier: 0,
        lastResult: false,
        error: '',
        showCoins: false,
        coinParticles: [],
        historial: @js($partidas->map(fn($p) => ['reels' => $p->detalles['reels'] ?? ['?','?','?'], 'ganancia' => $p->ganancia, 'apuesta' => $p->apuesta])->take(10)->all()),
        paytable: @js($paytable),
        symbols: @js($symbols),
        atlas: @js($symbolAtlas),
        spinTimer: null,
        visibilityHandler: null,
        reducedMotion: window.matchMedia('(prefers-reduced-motion: reduce)').matches,
        get netResult() { return Number((Number(this.ganancia) - Number(this.roundBet)).toFixed(2)); },
        get netMessage() {
            if (this.netResult > 0) return `Ganancia neta +€${this.netResult.toFixed(2)}`;
            if (this.netResult === 0) return 'Apuesta devuelta íntegramente';
            return `Resultado neto -€${Math.abs(this.netResult).toFixed(2)}`;
        },

        symbolStyle(symbol) {
            const index = Math.max(0, this.symbols.indexOf(symbol));
            const x = (index % 4) * 33.333333;
            const y = Math.floor(index / 4) * 100;
            return `background-image:url('${this.atlas}');background-size:400% 200%;background-position:${x}% ${y}%`;
        },

        initReels() {
            this.reels = [
                this.symbols[Math.floor(Math.random() * this.symbols.length)],
                this.symbols[Math.floor(Math.random() * this.symbols.length)],
                this.symbols[Math.floor(Math.random() * this.symbols.length)],
            ];
            this.visibilityHandler = () => {
                if (document.hidden) {
                    clearInterval(this.spinTimer);
                    this.spinTimer = null;
                } else if (this.spinning && !this.spinTimer) {
                    this.spinTimer = setInterval(() => {
                        this.reels = this.reels.map((symbol, index) => this.reelSpinning[index]
                            ? this.symbols[Math.floor(Math.random() * this.symbols.length)]
                            : symbol);
                    }, 110);
                }
            };
            document.addEventListener('visibilitychange', this.visibilityHandler);
        },

        destroy() {
            clearInterval(this.spinTimer);
            document.removeEventListener('visibilitychange', this.visibilityHandler);
        },

        requestToken() {
            const webCrypto = globalThis.crypto;
            if (typeof webCrypto?.randomUUID === 'function') {
                return webCrypto.randomUUID();
            }

            const bytes = new Uint8Array(16);
            webCrypto.getRandomValues(bytes);
            bytes[6] = (bytes[6] & 0x0f) | 0x40;
            bytes[8] = (bytes[8] & 0x3f) | 0x80;
            const hex = Array.from(bytes, byte => byte.toString(16).padStart(2, '0'));

            return `${hex.slice(0, 4).join('')}-${hex.slice(4, 6).join('')}-${hex.slice(6, 8).join('')}-${hex.slice(8, 10).join('')}-${hex.slice(10).join('')}`;
        },

        async spin() {
            if (this.spinning || this.apuesta > this.saldo) return;
            this.spinning = true;
            window.lootraAudio?.play('spin');
            this.roundBet = Number(this.apuesta);
            this.ganancia = 0;
            this.payoutMultiplier = 0;
            this.lastResult = false;
            this.error = '';
            this.showCoins = false;

            this.reelSpinning = [true, true, true];
            clearInterval(this.spinTimer);
            this.spinTimer = setInterval(() => {
                this.reels = this.reels.map((symbol, index) => this.reelSpinning[index]
                    ? this.symbols[Math.floor(Math.random() * this.symbols.length)]
                    : symbol);
            }, 110);

            try {
                const res = await fetch(@js(route('games.slots.play', ['slug' => $gameSlug])), {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({ apuesta: this.apuesta, game: '{{ $gameSlug }}', request_token: this.requestToken() }),
                });
                const data = await res.json();

                if (!res.ok) {
                    throw new Error(data.message || data.error || 'No se pudo completar el giro.');
                }

                // Frenado escalonado con inercia: cada carrete bloquea el resultado por separado.
                const delays = this.reducedMotion ? [180, 300, 420] : [700, 1100, 1550];
                for (let i = 0; i < 3; i++) {
                    await new Promise(r => setTimeout(r, delays[i] - (i > 0 ? delays[i-1] : 0)));
                    this.reelSpinning[i] = false;
                    this.reelStopping[i] = true;
                    window.lootraAudio?.play('reel-stop');

                    // Animate to final position
                    await new Promise(r => setTimeout(r, 50));
                    this.reels[i] = data.reels[i];

                    await new Promise(r => setTimeout(r, this.reducedMotion ? 30 : 480));
                    this.reelStopping[i] = false;
                }

                this.ganancia = Number(data.ganancia);
                this.payoutMultiplier = Number(data.multiplicador || 0);
                this.lastResult = true;
                this.saldo = Number(data.saldo);
                Alpine.store('wallet').saldo = this.saldo;
                window.dispatchEvent(new CustomEvent('saldo-updated', { detail: { saldo: this.saldo } }));
                this.historial.unshift({ reels: data.reels, ganancia: Number(data.ganancia), apuesta: Number(this.apuesta) });
                window.lootraAudio?.play(this.ganancia > 0 ? (this.ganancia >= this.apuesta * 10 ? 'jackpot' : 'win') : 'lose');

                // Show coins on win
                if (this.ganancia > 0) {
                    this.spawnCoins();
                }
            } catch (e) {
                this.error = e.message || 'No se pudo conectar con el servidor.';
                window.lootraAudio?.play('error');
                this.reelSpinning = [false, false, false];
            } finally {
                clearInterval(this.spinTimer);
                this.spinTimer = null;
            }

            this.spinning = false;
        },

        spawnCoins() {
            this.coinParticles = [];
            for (let i = 0; i < 2; i++) {
                this.coinParticles.push({
                    id: i,
                    x: 10 + Math.random() * 80,
                    y: Math.random() * 30,
                    delay: Math.random() * 0.5,
                });
            }
            this.showCoins = true;
            setTimeout(() => { this.showCoins = false; }, 2000);
        },
    };
}
</script>
@endpush
@endsection
