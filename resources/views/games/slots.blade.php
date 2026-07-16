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
    .reel-strip { position:absolute; inset:8%; transition:filter .2s ease; }
    .reel-strip.spinning { animation:reel-scroll .13s linear infinite alternate; filter:blur(5px) saturate(1.35) brightness(1.25); }
    .reel-strip.stopping { animation:reel-lock .58s cubic-bezier(.16,1,.3,1); }
    @keyframes win-flash { 0%,100% { box-shadow: 0 0 0 rgba(245,158,11,0); } 50% { box-shadow: 0 0 25px rgba(245,158,11,0.5); } }
    .win-flash { animation: win-flash 0.5s ease-in-out 3; border-color: rgba(245,158,11,0.5) !important; }
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
        width:14px;height:14px;border-radius:50%;background:radial-gradient(circle at 35% 30%,#fff7a8,#f59e0b 48%,#92400e);box-shadow:0 0 12px #fbbf24;
    }
    .slot-cabinet { background:linear-gradient(145deg,rgba(29,11,55,.94),rgba(5,8,25,.98));box-shadow:inset 0 0 55px rgba(217,70,239,.18),0 35px 90px rgba(0,0,0,.65); }
    .slot-cabinet::before { content:""; position:absolute; inset:0; background:var(--game-art) center/cover; opacity:.22; mix-blend-mode:screen;filter:saturate(1.2); }
    .slot-cabinet::after{content:"";position:absolute;inset:0;background:radial-gradient(circle at 50% 25%,rgba(255,255,255,.13),transparent 38%);pointer-events:none}
    .slot-symbol-art{width:100%;height:100%;border-radius:14px;background-repeat:no-repeat;filter:drop-shadow(0 10px 8px rgba(0,0,0,.48));transform:translateZ(0)}
    .reel-shine { position:absolute; inset:0; background:linear-gradient(110deg,transparent 25%,rgba(255,255,255,.45) 48%,transparent 70%); transform:translateX(-100%); pointer-events:none; z-index:3; }
    .reel-strip.spinning ~ .reel-shine { animation:shimmer .7s linear infinite; }
</style>
@endsection

@section('game-content')
<div class="max-w-[1400px] mx-auto px-4 sm:px-6 lg:px-8 py-6 sm:py-10"
     x-data="slotsGame()" x-init="initReels()">

    <div class="flex flex-col lg:flex-row gap-6">

        <div class="flex-1 min-w-0">

            {{-- Header --}}
            <div class="flex items-center justify-between mb-6">
                <div>
                    <div class="flex items-center gap-3"><span class="w-11 h-11 rounded-xl bg-gradient-to-br from-fuchsia-500 to-cyan-400 grid place-items-center shadow-lg shadow-fuchsia-500/20"><svg class="w-6 h-6 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M13 2 4 14h7l-1 8 10-13h-7V2Z" stroke-width="2" stroke-linejoin="round"/></svg></span><h1 class="game-heading font-extrabold">{{ $gameName }}</h1></div>
                    <p class="text-sm text-slate-500 mt-1">{{ $gameTagline }}</p>
                </div>
                <div class="balance-chip px-4 py-2 rounded-xl">
                    <span class="text-xs text-slate-500">Saldo</span>
                    <span class="ml-2 text-sm font-bold text-brand-400" x-text="'€' + saldo.toFixed(2)"></span>
                </div>
            </div>

            {{-- Maquina tragaperras --}}
            <div class="game-stage rounded-[1.75rem] bg-white/[0.03] border border-white/5 p-6 sm:p-8 mb-6 overflow-hidden">
                <div class="max-w-md mx-auto">
                    {{-- Carretes --}}
                    <div class="slot-cabinet rounded-[1.6rem] border-2 border-fuchsia-400/20 p-5 mb-6 relative overflow-hidden"
                         style="--game-art:url('{{ $gameHero }}')"
                         :class="ganancia > 0 && !spinning ? 'win-flash' : ''">
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
                                ¡Ganaste <span x-text="'€' + ganancia.toFixed(2)"></span>!
                            </div>
                        </template>
                        <template x-if="ganancia === 0 && lastResult">
                            <div class="text-red-400 font-bold text-lg">Sin suerte esta vez</div>
                        </template>
                    </div>
                    <p x-show="error" class="text-red-400 text-sm text-center mb-4" x-text="error"></p>

                    {{-- Controles --}}
                    <div class="flex items-center gap-4">
                        <div class="flex-1">
                            <label class="text-xs text-slate-500 mb-1 block">Apuesta (€)</label>
                            <input type="number" x-model.number="apuesta" min="0.10" max="500" step="0.10"
                                   :disabled="spinning"
                                   class="w-full px-4 py-3 rounded-xl bg-white/5 border border-white/10 text-white text-sm outline-none focus:border-brand-500 transition disabled:opacity-50">
                        </div>
                        <div class="mt-5">
                            <button @click="spin()" :disabled="spinning || apuesta > saldo"
                                    class="cta-shine px-8 py-3 rounded-xl bg-gradient-to-r from-brand-400 via-orange-400 to-fuchsia-500 hover:scale-105 text-black font-bold transition shadow-lg shadow-fuchsia-500/20 disabled:opacity-50 disabled:cursor-not-allowed">
                                <span x-text="spinning ? 'Girando...' : 'Girar'"></span>
                            </button>
                        </div>
                    </div>

                    {{-- Botones apuesta rapida --}}
                    <div class="flex gap-2 mt-3">
                        <template x-for="val in [0.50, 1, 2, 5, 10]" :key="val">
                            <button @click="apuesta = val" :disabled="spinning"
                                    class="flex-1 py-1.5 rounded-lg bg-white/5 border border-white/10 text-xs text-slate-400 hover:text-white hover:bg-white/10 transition disabled:opacity-50"
                                    x-text="'€' + val">
                            </button>
                        </template>
                    </div>
                </div>
            </div>

            {{-- Paytable --}}
            <div class="rounded-2xl bg-white/[0.03] border border-white/5 p-5">
                <h3 class="text-sm font-bold text-white uppercase tracking-wider mb-4">Tabla de pagos</h3>
                <div class="grid grid-cols-2 sm:grid-cols-3 gap-3 text-sm">
                    <template x-for="p in paytable" :key="p.label">
                        <div class="p-3 rounded-xl bg-white/[0.02] border border-white/5 text-center">
                            <div class="mx-auto mb-2 h-12 w-12 rounded-lg slot-symbol-art" :style="symbolStyle(p.icon)"></div>
                            <div class="mb-1 text-[10px] uppercase tracking-wider text-slate-500" x-text="p.symbols === 'X X X' ? 'Cualquier pareja' : '3 símbolos'"></div>
                            <div class="text-brand-400 font-bold" x-text="p.label"></div>
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
                            <span class="flex gap-1"><template x-for="symbol in h.reels"><i class="slot-symbol-art h-6 w-6 rounded" :style="symbolStyle(symbol)"></i></template></span>
                            <span :class="h.ganancia > 0 ? 'text-emerald-400' : 'text-red-400'"
                                  x-text="h.ganancia > 0 ? '+€' + h.ganancia.toFixed(2) : '-€' + h.apuesta.toFixed(2)"></span>
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
        reels: [],
        reelSpinning: [false, false, false],
        reelStopping: [false, false, false],
        spinning: false,
        ganancia: 0,
        lastResult: false,
        error: '',
        showCoins: false,
        coinParticles: [],
        historial: @js($partidas->map(fn($p) => ['reels' => $p->detalles['reels'] ?? ['?','?','?'], 'ganancia' => $p->ganancia, 'apuesta' => $p->apuesta])->take(10)->all()),
        paytable: @js($paytable),
        symbols: @js($symbols),
        atlas: @js($symbolAtlas),
        spinTimers: [],

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
        },

        async spin() {
            if (this.spinning || this.apuesta > this.saldo) return;
            this.spinning = true;
            this.ganancia = 0;
            this.lastResult = false;
            this.error = '';
            this.showCoins = false;

            this.reelSpinning = [true, true, true];
            this.spinTimers = this.reels.map((_, i) => setInterval(() => {
                this.reels[i] = this.symbols[Math.floor(Math.random() * this.symbols.length)];
            }, 72 + i * 9));

            try {
                const res = await fetch(@js(route('games.slots.play', ['slug' => $gameSlug])), {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({ apuesta: this.apuesta, game: '{{ $gameSlug }}', request_token: crypto.randomUUID() }),
                });
                const data = await res.json();

                if (data.error) {
                    this.error = data.error;
                    this.spinTimers.forEach(clearInterval);
                    this.reelSpinning = [false, false, false];
                    this.spinning = false;
                    return;
                }

                // Frenado escalonado con inercia: cada carrete bloquea el resultado por separado.
                const delays = [850, 1350, 1900];
                for (let i = 0; i < 3; i++) {
                    await new Promise(r => setTimeout(r, delays[i] - (i > 0 ? delays[i-1] : 0)));
                    this.reelSpinning[i] = false;
                    clearInterval(this.spinTimers[i]);
                    this.reelStopping[i] = true;

                    // Animate to final position
                    await new Promise(r => setTimeout(r, 50));
                    this.reels[i] = data.reels[i];

                    await new Promise(r => setTimeout(r, 580));
                    this.reelStopping[i] = false;
                }

                this.ganancia = data.ganancia;
                this.lastResult = true;
                this.saldo = data.saldo;
                Alpine.store('wallet').saldo = data.saldo;
                window.dispatchEvent(new CustomEvent('saldo-updated', { detail: { saldo: data.saldo } }));
                this.historial.unshift({ reels: data.reels, ganancia: data.ganancia, apuesta: this.apuesta });

                // Show coins on win
                if (data.ganancia > 0) {
                    this.spawnCoins();
                }
            } catch (e) {
                this.error = 'Error de conexion.';
                this.spinTimers.forEach(clearInterval);
                this.reelSpinning = [false, false, false];
            }

            this.spinning = false;
        },

        spawnCoins() {
            this.coinParticles = [];
            for (let i = 0; i < 15; i++) {
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
        destroy() {
            this.spinTimers.forEach(clearInterval);
        },
    };
}
</script>
@endpush
@endsection
