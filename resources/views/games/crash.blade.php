@extends('layouts.app')
@section('title', 'Crash — Lootra Casino')

@section('styles')
<style>
    .crash-line { transition: all 0.1s linear; }
    @keyframes pulse-glow { 0%, 100% { box-shadow: 0 0 20px rgba(245,158,11,0.3); } 50% { box-shadow: 0 0 40px rgba(245,158,11,0.6); } }
    .pulse-glow { animation: pulse-glow 1.5s ease-in-out infinite; }
    @keyframes crash-shake { 0%,100% { transform: translateX(0); } 25% { transform: translateX(-5px); } 75% { transform: translateX(5px); } }
    .crash-shake { animation: crash-shake 0.3s ease-in-out 3; }
</style>
@endsection

@section('contenido')
<div class="max-w-[1400px] mx-auto px-4 sm:px-6 lg:px-8 py-6 sm:py-10"
     x-data="crashGame()" x-init="init()">

    <div class="flex flex-col lg:flex-row gap-6">

        <div class="flex-1 min-w-0">

            <div class="flex items-center justify-between mb-6">
                <div>
                    <h1 class="text-2xl font-extrabold text-white">Crash Rocket</h1>
                    <p class="text-sm text-slate-500 mt-1">Multiplicador creciente — cobra antes de que explote</p>
                </div>
                <div class="px-4 py-2 rounded-xl bg-white/5 border border-white/10">
                    <span class="text-xs text-slate-500">Saldo</span>
                    <span class="ml-2 text-sm font-bold text-brand-400" x-text="'€' + saldo.toFixed(2)"></span>
                </div>
            </div>

            <div class="rounded-2xl bg-white/[0.03] border border-white/5 overflow-hidden mb-6">
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
                        <div class="absolute z-10 text-2xl"
                             :style="'left:' + Math.min(90, (multiplier - 1) * 5 + 5) + '%; bottom:' + Math.min(85, (multiplier - 1) * 8 + 10) + '%'">
                            🚀
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
    return {
        saldo: {{ Auth::user()->cartera->saldo ?? 1000 }},
        apuesta: 1,
        autoCashout: 2,
        fase: 'esperando',
        multiplier: 1.00,
        crashAt: 0,
        cashoutAt: 0,
        ganancia: 0,
        error: '',
        graphPoints: '0,58',
        historial: @js($partidas->pluck('detalles.crash_point')->filter()->take(15)->values()->all()),
        interval: null,
        autoCashoutTriggered: false,
        serverCrashPoint: 0,

        init() {},

        async startRound() {
            this.error = '';
            if (this.apuesta <= 0 || this.apuesta > this.saldo) {
                this.error = 'Apuesta no valida.';
                return;
            }

            try {
                const res = await fetch('{{ route("crash.play") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({ apuesta: this.apuesta }),
                });
                const data = await res.json();

                if (data.error) {
                    this.error = data.error;
                    return;
                }

                this.saldo = data.saldo;
                this.serverCrashPoint = data.crash_point;
                this.multiplier = 1.00;
                this.ganancia = 0;
                this.graphPoints = '0,58';
                this.fase = 'subiendo';
                this.autoCashoutTriggered = false;

                this.animateCrash();
            } catch (e) {
                this.error = 'Error de conexion.';
            }
        },

        animateCrash() {
            let current = 1.00;
            let pointIndex = 0;

            this.interval = setInterval(() => {
                const increment = Math.min(
                    (this.serverCrashPoint - current) * 0.08,
                    (Math.random() * 0.08) + 0.02
                );
                current += Math.max(0.01, increment);
                current = Math.round(current * 100) / 100;

                if (current >= this.serverCrashPoint) {
                    clearInterval(this.interval);
                    this.doCrash();
                    return;
                }

                this.multiplier = current;

                pointIndex++;
                const x = Math.min(95, pointIndex * 1.5);
                const y = Math.max(5, 58 - (current - 1) * 8);
                this.graphPoints += ` ${x},${y}`;

                if (!this.autoCashoutTriggered && current >= this.autoCashout) {
                    this.autoCashoutTriggered = true;
                    this.doCashout(current);
                }
            }, 50);
        },

        async cashout() {
            if (this.fase !== 'subiendo') return;
            clearInterval(this.interval);
            await this.doCashout(this.multiplier);
        },

        async doCrash() {
            try {
                const res = await fetch('{{ route("crash.crash") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                    },
                });
                const data = await res.json();

                this.crashAt = data.crash_point;
                this.ganancia = 0;
                this.saldo = data.saldo;
                this.fase = 'crashed';

                this.graphPoints = '0,58';
                let c = 1.00;
                let pi = 0;
                const crashAnim = setInterval(() => {
                    c += (Math.random() * 0.15) + 0.05;
                    c = Math.round(c * 100) / 100;
                    if (c >= data.crash_point) {
                        clearInterval(crashAnim);
                        this.multiplier = data.crash_point;
                    } else {
                        this.multiplier = c;
                        pi++;
                        const x = Math.min(95, pi * 1.5);
                        const y = Math.max(5, 58 - (c - 1) * 8);
                        this.graphPoints += ` ${x},${y}`;
                    }
                }, 30);

                this.historial.unshift(data.crash_point);
                if (this.historial.length > 15) this.historial.pop();
            } catch (e) {
                this.error = 'Error de conexion.';
                this.fase = 'esperando';
            }
        },

        async doCashout(mult) {
            try {
                const res = await fetch('{{ route("crash.cashout") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({ multiplier: mult }),
                });
                const data = await res.json();

                if (data.error) {
                    this.error = data.error;
                    this.fase = 'esperando';
                    return;
                }

                this.crashAt = data.crash_point;
                this.cashoutAt = mult;
                this.ganancia = data.ganancia;
                this.saldo = data.saldo;

                if (data.resultado === 'crash') {
                    this.fase = 'crashed';
                    this.graphPoints = '0,58';
                    let c = 1.00;
                    let pi = 0;
                    const crashAnim = setInterval(() => {
                        c += (Math.random() * 0.15) + 0.05;
                        c = Math.round(c * 100) / 100;
                        if (c >= data.crash_point) {
                            clearInterval(crashAnim);
                            this.fase = 'crashed';
                        } else {
                            this.multiplier = c;
                            pi++;
                            const x = Math.min(95, pi * 1.5);
                            const y = Math.max(5, 58 - (c - 1) * 8);
                            this.graphPoints += ` ${x},${y}`;
                        }
                    }, 30);
                } else {
                    this.fase = 'cobrado';
                }

                this.historial.unshift(data.crash_point);
                if (this.historial.length > 15) this.historial.pop();
            } catch (e) {
                this.error = 'Error de conexion.';
                this.fase = 'esperando';
            }
        },
    };
}
</script>
@endpush
@endsection
