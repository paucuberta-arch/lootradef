@extends('layouts.app')
@section('title', 'Crash — Lootra Casino')

@section('styles')
<style>
    .crash-line { transition: all 0.1s linear; }
    @keyframes pulse-glow { 0%, 100% { box-shadow: 0 0 20px rgba(245,158,11,0.3); } 50% { box-shadow: 0 0 40px rgba(245,158,11,0.6); } }
    .pulse-glow { animation: pulse-glow 1.5s ease-in-out infinite; }
    @keyframes crash-shake { 0%,100% { transform: translateX(0); } 25% { transform: translateX(-5px); } 75% { transform: translateX(5px); } }
    .crash-shake { animation: crash-shake 0.3s ease-in-out 3; }
    @keyframes rocket-flame { 50% { transform:scaleY(1.45); filter:brightness(1.5); } }
    .rocket-craft { width:30px; height:62px; border-radius:55% 55% 35% 35%; background:linear-gradient(90deg,#2563eb,#f8fafc 48%,#67e8f9); border:2px solid rgba(255,255,255,.75); box-shadow:0 0 24px rgba(34,211,238,.65); transition:bottom .1s linear; }
    .rocket-craft::before { content:""; position:absolute; width:15px; height:25px; left:6px; bottom:-23px; border-radius:0 0 60% 60%; background:linear-gradient(180deg,#fbbf24,#fb7185 55%,transparent); filter:drop-shadow(0 7px 8px #ef4444); transform-origin:top; animation:rocket-flame .16s infinite; }
    .rocket-craft::after { content:""; position:absolute; left:-8px; bottom:4px; width:42px; height:20px; background:linear-gradient(90deg,#7c3aed 0 22%,transparent 23% 77%,#7c3aed 78%); clip-path:polygon(0 100%,20% 0,80% 0,100% 100%,72% 68%,28% 68%); }
    .rocket-window { position:absolute; z-index:1; width:11px; height:11px; border-radius:50%; left:8px; top:17px; background:#0f172a; border:2px solid #a5f3fc; }
</style>
@endsection

@section('contenido')
<div class="max-w-[1400px] mx-auto px-4 sm:px-6 lg:px-8 py-6 sm:py-10"
     x-data="crashGame()" x-init="init()">

    <div class="flex flex-col lg:flex-row gap-6">

        <div class="flex-1 min-w-0">

            <div class="flex items-center justify-between mb-6">
                <div>
                    <h1 class="game-heading font-extrabold">Crash Rocket</h1>
                    <p class="text-sm text-slate-500 mt-1">Multiplicador creciente — cobra antes de que explote</p>
                </div>
                <div class="px-4 py-2 rounded-xl bg-white/5 border border-white/10">
                    <span class="text-xs text-slate-500">Saldo</span>
                    <span class="ml-2 text-sm font-bold text-brand-400" x-text="'€' + saldo.toFixed(2)"></span>
                </div>
            </div>

            <div class="game-stage rounded-[1.75rem] bg-white/[0.03] border border-white/5 overflow-hidden mb-6">
                <div class="relative h-64 sm:h-80 flex items-center justify-center"
                     :class="fase === 'crashed' ? 'crash-shake' : ''">

                    <div class="absolute inset-0 p-4">
                        <svg class="w-full h-full" viewBox="0 0 100 60" preserveAspectRatio="none">
                            <line x1="0" y1="58" x2="100" y2="58" stroke="rgba(255,255,255,0.05)" stroke-width="0.5"/>
                            <line x1="0" y1="40" x2="100" y2="40" stroke="rgba(255,255,255,0.03)" stroke-width="0.5"/>
                            <line x1="0" y1="20" x2="100" y2="20" stroke="rgba(255,255,255,0.03)" stroke-width="0.5"/>
                            <line x1="0" y1="0" x2="100" y2="0" stroke="rgba(255,255,255,0.03)" stroke-width="0.5"/>
                            <polyline :points="graphPoints" fill="none" stroke="url(#crash-gradient)" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                            <defs>
                                <linearGradient id="crash-gradient" x1="0%" y1="100%" x2="100%" y2="0%">
                                    <stop offset="0%" stop-color="#f59e0b"/>
                                    <stop offset="100%" stop-color="#ef4444"/>
                                </linearGradient>
                            </defs>
                        </svg>
                    </div>

                    <div class="relative z-10 text-center">
                        <template x-if="fase === 'esperando'">
                            <div>
                                <div class="text-5xl sm:text-6xl font-black text-slate-600">1.00x</div>
                                <p class="text-sm text-slate-600 mt-2">Esperando siguiente ronda...</p>
                            </div>
                        </template>
                        <template x-if="fase === 'subiendo'">
                            <div class="pulse-glow rounded-2xl px-8 py-4">
                                <div class="text-5xl sm:text-6xl font-black text-brand-400" x-text="multiplier.toFixed(2) + 'x'"></div>
                                <p class="text-sm text-emerald-400 mt-2">Subiendo...</p>
                            </div>
                        </template>
                        <template x-if="fase === 'crashed'">
                            <div>
                                <div class="text-5xl sm:text-6xl font-black text-red-500" x-text="crashAt.toFixed(2) + 'x'"></div>
                                <p class="text-sm text-red-400 mt-2">Explotado!</p>
                            </div>
                        </template>
                        <template x-if="fase === 'cobrado'">
                            <div>
                                <div class="text-5xl sm:text-6xl font-black text-emerald-400" x-text="cashoutAt.toFixed(2) + 'x'"></div>
                                <p class="text-sm text-emerald-400 mt-2">Cobrado!</p>
                                <p class="text-lg text-emerald-300 font-bold mt-1" x-text="'+€' + ganancia.toFixed(2)"></p>
                            </div>
                        </template>
                    </div>

                    <template x-if="fase === 'subiendo'">
                        <div class="absolute z-10 rocket-craft"
                             :style="'left:calc(12% - 15px);bottom:' + Math.min(78, (multiplier - 1) * 10 + 8) + '%'">
                            <span class="rocket-window"></span>
                        </div>
                    </template>
                </div>

                <div class="border-t border-white/5 p-4 sm:p-6">
                    <div class="flex flex-col sm:flex-row items-center gap-4">
                        <div class="flex-1 w-full">
                            <label class="text-xs text-slate-500 mb-1 block">Apuesta (€)</label>
                            <input type="number" x-model.number="apuesta" min="0.10" max="1000" step="0.10"
                                   :disabled="fase === 'subiendo'"
                                   class="w-full px-4 py-3 rounded-xl bg-white/5 border border-white/10 text-white text-sm outline-none focus:border-brand-500 transition disabled:opacity-50">
                        </div>
                        <div class="flex-1 w-full">
                            <label class="text-xs text-slate-500 mb-1 block">Auto-cobrar en (x)</label>
                            <input type="number" x-model.number="autoCashout" min="1.01" max="100" step="0.01"
                                   :disabled="fase === 'subiendo'"
                                   class="w-full px-4 py-3 rounded-xl bg-white/5 border border-white/10 text-white text-sm outline-none focus:border-brand-500 transition disabled:opacity-50">
                        </div>
                        <div class="w-full sm:w-auto mt-2 sm:mt-6">
                            <template x-if="fase === 'esperando' || fase === 'cobrado' || fase === 'crashed'">
                                <button @click="startRound()"
                                        class="w-full sm:w-auto px-8 py-3 rounded-xl bg-brand-500 hover:bg-brand-400 text-black font-bold transition shadow-lg shadow-brand-500/20">
                                    Apostar
                                </button>
                            </template>
                            <template x-if="fase === 'subiendo'">
                                <button @click="cashout()"
                                        class="w-full sm:w-auto px-8 py-3 rounded-xl bg-emerald-500 hover:bg-emerald-400 text-white font-bold transition shadow-lg shadow-emerald-500/20">
                                    Cobrar <span x-text="multiplier.toFixed(2) + 'x'"></span>
                                </button>
                            </template>
                        </div>
                    </div>
                    <p x-show="error" class="text-red-400 text-sm mt-3" x-text="error"></p>
                </div>
            </div>

            <div class="rounded-2xl bg-white/[0.03] border border-white/5 p-5">
                <h3 class="text-sm font-bold text-white uppercase tracking-wider mb-4">Historial reciente</h3>
                <div class="flex flex-wrap gap-2">
                    <template x-for="(h, i) in historial" :key="i">
                        <span class="px-3 py-1.5 rounded-lg text-xs font-bold"
                              :class="h >= 2 ? 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/20' : 'bg-red-500/10 text-red-400 border border-red-500/20'"
                              x-text="h.toFixed(2) + 'x'"></span>
                    </template>
                </div>
            </div>
        </div>

        <aside class="w-full lg:w-72 shrink-0 space-y-5">
            <div class="rounded-2xl bg-white/[0.03] border border-white/5 p-5">
                <h3 class="text-sm font-bold text-white uppercase tracking-wider mb-3">Info</h3>
                <div class="space-y-3 text-sm">
                    <div class="flex justify-between"><span class="text-slate-500">Min apuesta</span><span class="text-white font-semibold">€0.10</span></div>
                    <div class="flex justify-between"><span class="text-slate-500">Max apuesta</span><span class="text-white font-semibold">€1,000</span></div>
                    <div class="flex justify-between"><span class="text-slate-500">Max win</span><span class="text-brand-400 font-bold">x∞</span></div>
                    <div class="flex justify-between"><span class="text-slate-500">RTP</span><span class="text-emerald-400 font-semibold">97%</span></div>
                </div>
            </div>
            <div class="rounded-2xl bg-gradient-to-br from-brand-500/20 to-brand-600/10 border border-brand-500/20 p-5">
                <h3 class="text-sm font-bold text-white mb-2">Como funciona?</h3>
                <p class="text-xs text-slate-400 leading-relaxed">El multiplicador sube desde 1.00x. Puedes cobrar en cualquier momento haciendo clic en "Cobrar". Si el cohete explota antes de cobrar, pierdes la apuesta.</p>
            </div>
        </aside>
    </div>
</div>

@push('scripts')
<script>
function crashGame() {
    const activeRound = @js($activeRound);

    return {
        saldo: {{ Auth::user()?->cartera?->saldo ?? 1000 }},
        apuesta: 1,
        autoCashout: 2,
        fase: activeRound ? 'subiendo' : 'esperando',
        multiplier: Number(activeRound?.multiplier || 1),
        crashAt: 0,
        cashoutAt: 0,
        ganancia: 0,
        error: '',
        graphPoints: '0,58',
        historial: @js($partidas->pluck('detalles.crash_point')->filter()->take(15)->values()->all()),
        interval: null,
        statusInFlight: false,
        actionInFlight: false,
        autoCashoutTriggered: false,
        roundId: activeRound?.round_id || null,
        ratePerSecond: Number(activeRound?.rate_per_second || 0.2),

        init() {
            if (this.roundId) this.animateCrash(this.multiplier);
        },

        async startRound() {
            this.error = '';
            if (this.actionInFlight || this.fase === 'subiendo') return;
            if (this.apuesta < 0.10 || this.apuesta > this.saldo) {
                this.error = 'Apuesta no valida.';
                return;
            }

            this.actionInFlight = true;
            try {
                const res = await fetch('{{ route("games.crash.play") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({ apuesta: this.apuesta }),
                });
                const data = await res.json();

                if (!res.ok) throw new Error(data.message || data.error || 'No se pudo iniciar la ronda.');

                this.updateBalance(data.saldo);
                this.roundId = data.round_id;
                this.ratePerSecond = Number(data.rate_per_second);
                this.multiplier = Number(data.multiplier);
                this.ganancia = 0;
                this.graphPoints = '0,58';
                this.fase = 'subiendo';
                this.autoCashoutTriggered = false;

                this.animateCrash(this.multiplier);
            } catch (e) {
                this.error = e.message;
            } finally {
                this.actionInFlight = false;
            }
        },

        animateCrash(startAt = 1) {
            clearInterval(this.interval);
            let current = Number(startAt);
            let pointIndex = 0;
            let statusTicks = 0;
            const increment = this.ratePerSecond / 20;

            this.interval = setInterval(() => {
                if (this.fase !== 'subiendo') return;
                current += increment;
                current = Math.round(current * 100) / 100;
                this.multiplier = current;

                pointIndex++;
                const x = Math.min(95, pointIndex * 1.5);
                const y = Math.max(5, 58 - (current - 1) * 8);
                this.graphPoints += ` ${x},${y}`;

                if (!this.autoCashoutTriggered && current >= this.autoCashout) {
                    this.autoCashoutTriggered = true;
                    clearInterval(this.interval);
                    this.cashout();
                }

                statusTicks++;
                if (statusTicks >= 5) {
                    statusTicks = 0;
                    this.refreshRound();
                }
            }, 50);
        },

        async cashout() {
            if (this.fase !== 'subiendo' || this.actionInFlight || !this.roundId) return;
            clearInterval(this.interval);
            this.actionInFlight = true;
            try {
                const res = await fetch('{{ route("games.crash.cashout") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({ round_id: this.roundId }),
                });
                const data = await res.json();

                if (!res.ok) throw new Error(data.message || data.error || 'No se pudo cobrar.');
                this.applyRound(data);
            } catch (e) {
                this.error = e.message;
                if (this.fase === 'subiendo') this.animateCrash(this.multiplier);
            } finally {
                this.actionInFlight = false;
            }
        },

        async refreshRound() {
            if (this.statusInFlight || this.fase !== 'subiendo' || !this.roundId) return;
            this.statusInFlight = true;
            try {
                const res = await fetch('{{ route("games.crash.status") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({ round_id: this.roundId }),
                });
                const data = await res.json();
                if (res.ok) this.applyRound(data, false);
            } catch (e) {
                // El reloj visual puede continuar durante un fallo de red temporal.
            } finally { this.statusInFlight = false; }
        },

        applyRound(data, syncActive = true) {
            this.updateBalance(data.saldo);
            if (data.estado === 'activa') {
                if (syncActive) this.multiplier = Math.max(this.multiplier, Number(data.multiplier));
                return;
            }

            clearInterval(this.interval);
            this.ganancia = Number(data.ganancia);
            this.multiplier = Number(data.multiplier);
            this.crashAt = Number(data.crash_point);
            this.cashoutAt = data.estado === 'cobrado' ? Number(data.multiplier) : 0;
            this.fase = data.estado;
            this.historial.unshift(Number(data.crash_point));
            if (this.historial.length > 15) this.historial.pop();
        },

        updateBalance(value) {
            this.saldo = Number(value);
            Alpine.store('wallet').saldo = this.saldo;
            window.dispatchEvent(new CustomEvent('saldo-updated', { detail: { saldo: this.saldo } }));
        },
    };
}
</script>
@endpush
@endsection
