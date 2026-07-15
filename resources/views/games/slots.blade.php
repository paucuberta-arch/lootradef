@extends('layouts.app')
@section('title', $gameName . ' — Lootra Casino')

@section('styles')
<style>
    @keyframes reel-scroll {
        0% { transform: translateY(0); }
        100% { transform: translateY(-100%); }
    }
    .reel-container {
        overflow: hidden;
        position: relative;
        background: linear-gradient(155deg, rgba(255,255,255,.96), rgba(203,213,225,.9));
        box-shadow: inset 0 0 22px rgba(15,23,42,.28), 0 8px 24px rgba(0,0,0,.32);
    }
    .reel-strip {
        display: flex;
        flex-direction: column;
        transition: transform 0.1s linear;
    }
    .reel-strip.spinning {
        animation: reel-scroll 0.15s linear infinite;
    }
    .reel-strip.stopping {
        transition: transform 0.5s cubic-bezier(0.25, 0.46, 0.45, 0.94);
    }
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
        font-size: 1.2rem;
    }
    .slot-cabinet { background: linear-gradient(145deg, rgba(29,11,55,.97), rgba(5,8,25,.98)); box-shadow: inset 0 0 55px rgba(217,70,239,.12), 0 30px 80px rgba(0,0,0,.5); }
    .slot-cabinet::before { content:""; position:absolute; inset:0; background:var(--game-art) center/cover; opacity:.14; mix-blend-mode:screen; }
    .slot-symbol { width:100%; aspect-ratio:1; display:grid; place-items:center; color:#111827; font-size:clamp(2.2rem,6vw,4.2rem); filter:drop-shadow(0 7px 7px rgba(15,23,42,.25)); text-shadow:0 2px 0 white; }
    .reel-shine { position:absolute; inset:0; background:linear-gradient(110deg,transparent 25%,rgba(255,255,255,.45) 48%,transparent 70%); transform:translateX(-100%); pointer-events:none; z-index:3; }
    .reel-strip.spinning ~ .reel-shine { animation:shimmer .7s linear infinite; }
</style>
@endsection

@section('contenido')
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
                         style="--game-art:url('https://images.unsplash.com/photo-1511512578047-dfb367046420?auto=format&fit=crop&w=1000&q=80')"
                         :class="ganancia > 0 && !spinning ? 'win-flash' : ''">
                        <div class="grid grid-cols-3 gap-3">
                            <template x-for="(reel, i) in reels" :key="i">
                                <div class="reel-container aspect-square rounded-xl border border-white/40 flex items-center justify-center"
                                     :class="ganancia > 0 && !spinning ? 'win-bounce border-brand-500/50' : ''">
                                    <div class="reel-strip" :id="'reel-' + i"
                                         :class="reelSpinning[i] ? 'spinning' : (reelStopping[i] ? 'stopping' : '')">
                                        <div class="slot-symbol" x-text="reel"></div>
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
                                    <span class="coin-particle" :style="'left:' + c.x + '%; top:' + c.y + '%; animation-delay:' + c.delay + 's'" x-text="c.emoji"></span>
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
                            <div class="text-2xl mb-1" x-text="p.symbols"></div>
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
                            <span x-text="h.reels.join(' ')"></span>
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
        reels: ['🍒', '🍋', '🍊'],
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

            try {
                const res = await fetch('{{ route("slots.play") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({ apuesta: this.apuesta, game: '{{ $gameSlug }}' }),
                });
                const data = await res.json();

                if (data.error) {
                    this.error = data.error;
                    this.spinning = false;
                    return;
                }

                // Start all reels spinning
                this.reelSpinning = [true, true, true];

                // Staggered stop: reel 0 at 600ms, reel 1 at 1000ms, reel 2 at 1400ms
                const delays = [600, 1000, 1400];
                for (let i = 0; i < 3; i++) {
                    await new Promise(r => setTimeout(r, delays[i] - (i > 0 ? delays[i-1] : 0)));
                    this.reelSpinning[i] = false;
                    this.reelStopping[i] = true;

                    // Animate to final position
                    await new Promise(r => setTimeout(r, 50));
                    this.reels[i] = data.reels[i];

                    await new Promise(r => setTimeout(r, 500));
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
            }

            this.spinning = false;
        },

        spawnCoins() {
            const emojis = ['🪙', '💰', '✨', '💎'];
            this.coinParticles = [];
            for (let i = 0; i < 15; i++) {
                this.coinParticles.push({
                    id: i,
                    emoji: emojis[Math.floor(Math.random() * emojis.length)],
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
