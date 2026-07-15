@extends('layouts.app')
@section('title', 'Blackjack — Lootra Casino')

@section('styles')
<style>
    @keyframes deal-card { 0% { transform: translateY(-40px) rotate(-10deg) scale(0.5); opacity:0; } 100% { transform: translateY(0) rotate(0) scale(1); opacity:1; } }
    .deal-card { animation: deal-card 0.3s ease-out forwards; }
    .deal-card:nth-child(2) { animation-delay: 0.15s; }
    .deal-card:nth-child(3) { animation-delay: 0.3s; }
</style>
@endsection

@section('contenido')
<div class="max-w-[1400px] mx-auto px-4 sm:px-6 lg:px-8 py-6 sm:py-10"
     x-data="blackjackGame()">

    <div class="flex flex-col lg:flex-row gap-6">

        <div class="flex-1 min-w-0">

            {{-- Header --}}
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h1 class="text-2xl font-extrabold text-white">🃏 Blackjack VIP</h1>
                    <p class="text-sm text-slate-500 mt-1">21 puntos — vence al dealer</p>
                </div>
                <div class="px-4 py-2 rounded-xl bg-white/5 border border-white/10">
                    <span class="text-xs text-slate-500">Saldo</span>
                    <span class="ml-2 text-sm font-bold text-brand-400" x-text="'€' + saldo.toFixed(2)"></span>
                </div>
            </div>

            {{-- Mesa --}}
            <div class="rounded-2xl bg-[#0a2e1a] border border-emerald-900/50 p-6 sm:p-8 mb-6 relative overflow-hidden">
                {{-- Felt texture --}}
                <div class="absolute inset-0 opacity-10" style="background-image: radial-gradient(circle, #fff 1px, transparent 1px); background-size: 20px 20px;"></div>

                <div class="relative z-10">

                    {{-- Dealer --}}
                    <div class="mb-8">
                        <div class="flex items-center gap-2 mb-3">
                            <span class="text-sm font-bold text-emerald-300">Dealer</span>
                            <span x-show="manoDealer.length > 0" class="text-sm text-emerald-400/70"
                                  x-text="'— ' + puntosDealer + ' pts'"></span>
                        </div>
                        <div class="flex gap-2 flex-wrap">
                            <template x-for="(carta, i) in manoDealer" :key="i">
                                <div class="deal-card w-16 h-22 sm:w-20 sm:h-28 rounded-xl flex flex-col items-center justify-center text-sm font-bold shadow-lg"
                                     :class="(oculto && i === 1) ? 'bg-slate-700 border-2 border-slate-600' : 'bg-white border-2 border-slate-200'"
                                     :style="'animation-delay:' + (i*0.15) + 's'">
                                    <template x-if="!oculto || i === 0">
                                        <div>
                                            <span :class="carta.palo === '♥' || carta.palo === '♦' ? 'text-red-500' : 'text-slate-800'"
                                                  class="text-lg" x-text="carta.valor"></span>
                                            <span :class="carta.palo === '♥' || carta.palo === '♦' ? 'text-red-500' : 'text-slate-800'"
                                                  class="text-xs" x-text="carta.palo"></span>
                                        </div>
                                    </template>
                                    <template x-if="oculto && i === 1">
                                        <span class="text-2xl">🂠</span>
                                    </template>
                                </div>
                            </template>
                            <div x-show="manoDealer.length === 0" class="text-emerald-700 text-sm">Esperando apuesta...</div>
                        </div>
                    </div>

                    {{-- Separador --}}
                    <div class="border-t border-emerald-800/50 my-4"></div>

                    {{-- Jugador --}}
                    <div>
                        <div class="flex items-center gap-2 mb-3">
                            <span class="text-sm font-bold text-emerald-300">Tu mano</span>
                            <span x-show="manoJugador.length > 0" class="text-sm text-emerald-400/70"
                                  x-text="'— ' + puntosJugador + ' pts'"
                                  :class="puntosJugador > 21 ? 'text-red-400' : ''"></span>
                        </div>
                        <div class="flex gap-2 flex-wrap">
                            <template x-for="(carta, i) in manoJugador" :key="i">
                                <div class="deal-card w-16 h-22 sm:w-20 sm:h-28 rounded-xl bg-white border-2 border-slate-200 flex flex-col items-center justify-center text-sm font-bold shadow-lg"
                                     :style="'animation-delay:' + (i*0.15) + 's'">
                                    <span :class="carta.palo === '♥' || carta.palo === '♦' ? 'text-red-500' : 'text-slate-800'"
                                          class="text-lg" x-text="carta.valor"></span>
                                    <span :class="carta.palo === '♥' || carta.palo === '♦' ? 'text-red-500' : 'text-slate-800'"
                                          class="text-xs" x-text="carta.palo"></span>
                                </div>
                            </template>
                        </div>
                    </div>

                    {{-- Resultado --}}
                    <div x-show="estado !== 'jugando' && estado !== ''" class="mt-6 text-center">
                        <div class="inline-block px-6 py-3 rounded-xl font-bold text-lg"
                             :class="{
                                'bg-emerald-500/20 text-emerald-400 border border-emerald-500/30': estado === 'win' || estado === 'blackjack',
                                'bg-slate-500/20 text-slate-300 border border-slate-500/30': estado === 'push',
                                'bg-red-500/20 text-red-400 border border-red-500/30': estado === 'lose' || estado === 'bust',
                             }">
                            <span x-show="estado === 'blackjack'">¡BLACKJACK! </span>
                            <span x-show="estado === 'win'">¡Ganaste! </span>
                            <span x-show="estado === 'push'">Empate</span>
                            <span x-show="estado === 'bust'">¡Te pasaste!</span>
                            <span x-show="estado === 'lose'">Dealer gana</span>
                            <span x-show="ganancia > 0" x-text="' +€' + ganancia.toFixed(2)"></span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Controles --}}
            <div class="rounded-2xl bg-white/[0.03] border border-white/5 p-5">
                <template x-if="estado === 'jugando' || estado === ''">
                    <div class="flex items-center gap-4">
                        <div class="flex-1">
                            <label class="text-xs text-slate-500 mb-1 block">Apuesta (€)</label>
                            <input type="number" x-model.number="apuesta" min="1" max="5000" step="1"
                                   class="w-full px-4 py-3 rounded-xl bg-white/5 border border-white/10 text-white text-sm outline-none focus:border-brand-500 transition">
                        </div>
                        <div class="mt-5">
                            <button @click="deal()" :disabled="apuesta > saldo"
                                    class="px-8 py-3 rounded-xl bg-brand-500 hover:bg-brand-400 text-black font-bold transition shadow-lg shadow-brand-500/20 disabled:opacity-50">
                                Repartir
                            </button>
                        </div>
                    </div>
                </template>
                <template x-if="fase === 'jugando' && puntosJugador <= 21">
                    <div class="flex items-center gap-3">
                        <button @click="hit()"
                                class="flex-1 py-3 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white font-bold transition">
                            Pedir carta
                        </button>
                        <button @click="stand()"
                                class="flex-1 py-3 rounded-xl bg-red-600 hover:bg-red-500 text-white font-bold transition">
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
                    <div class="flex justify-between"><span class="text-slate-500">Min apuesta</span><span class="text-white font-semibold">€1</span></div>
                    <div class="flex justify-between"><span class="text-slate-500">Max apuesta</span><span class="text-white font-semibold">€5,000</span></div>
                    <div class="flex justify-between"><span class="text-slate-500">Blackjack</span><span class="text-brand-400 font-bold">x2.5</span></div>
                    <div class="flex justify-between"><span class="text-slate-500">RTP</span><span class="text-emerald-400 font-semibold">99.28%</span></div>
                </div>
            </div>
            <div class="rounded-2xl bg-gradient-to-br from-brand-500/20 to-brand-600/10 border border-brand-500/20 p-5">
                <h3 class="text-sm font-bold text-white mb-2">¿Cómo funciona?</h3>
                <p class="text-xs text-slate-400 leading-relaxed">Acércate a 21 puntos sin pasarte. Pide carta o plantate. Blackjack natural (A + 10) paga x2.5. Dealer se planta en 17.</p>
            </div>
            <div class="rounded-2xl bg-white/[0.03] border border-white/5 p-5">
                <h3 class="text-sm font-bold text-white uppercase tracking-wider mb-3">Historial</h3>
                <div class="space-y-2">
                    <template x-for="(h, i) in historial.slice(0, 8)" :key="i">
                        <div class="flex items-center justify-between text-xs">
                            <span class="text-slate-400" x-text="h.puntos + ' pts'"></span>
                            <span class="text-slate-500" x-text="h.dealer_puntos ? 'vs ' + h.dealer_puntos : ''"></span>
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
function blackjackGame() {
    return {
        saldo: {{ Auth::user()?->cartera?->saldo ?? 1000 }},
        apuesta: 10,
        manoJugador: [],
        manoDealer: [],
        puntosJugador: 0,
        puntosDealer: 0,
        oculto: true,
        estado: 'jugando',
        fase: '',
        ganancia: 0,
        error: '',
        baraja: [],
        historial: @js($partidas->take(10)->map(fn($p) => ['puntos' => $p->detalles['puntos_jugador'] ?? 0, 'dealer_puntos' => $p->detalles['puntos_dealer'] ?? 0, 'ganancia' => $p->ganancia, 'apuesta' => $p->apuesta])->all()),

        async deal() {
            if (this.apuesta > this.saldo) return;
            this.fase = 'jugando';
            this.estado = 'jugando';
            this.ganancia = 0;
            this.oculto = true;
            this.error = '';

            try {
                const res = await fetch('{{ route("blackjack.deal") }}', {
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

                this.manoJugador = data.mano_jugador;
                this.manoDealer = data.mano_dealer;
                this.puntosJugador = data.puntos_jugador;
                this.puntosDealer = data.puntos_dealer;
                this.baraja = data.baraja || [];
                this.saldo = data.saldo;
                window.dispatchEvent(new CustomEvent('saldo-updated', { detail: { saldo: data.saldo } }));
                this.estado = data.estado;

                if (data.estado === 'jugando') {
                    this.oculto = true;
                } else {
                    this.oculto = false;
                    this.ganancia = data.ganancia;
                    this.fase = 'terminado';
                    this.historial.unshift({ puntos: data.puntos_jugador, dealer_puntos: data.puntos_dealer, ganancia: data.ganancia, apuesta: this.apuesta });
                }
            } catch (e) {
                this.error = 'Error de conexion.';
            }
        },

        async hit() {
            try {
                const res = await fetch('{{ route("blackjack.hit") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({ baraja: this.baraja, mano_jugador: this.manoJugador, mano_dealer: this.manoDealer, apuesta: this.apuesta }),
                });
                const data = await res.json();

                if (data.error) {
                    this.error = data.error;
                    return;
                }

                this.manoJugador = data.mano_jugador;
                this.puntosJugador = data.puntos_jugador;
                this.baraja = data.baraja;

                if (data.mano_dealer) this.manoDealer = data.mano_dealer;
                if (data.puntos_dealer) this.puntosDealer = data.puntos_dealer;

                if (data.estado === 'bust') {
                    this.estado = 'bust';
                    this.oculto = false;
                    this.fase = 'terminado';
                    this.historial.unshift({ puntos: data.puntos_jugador, dealer_puntos: data.puntos_dealer || 0, ganancia: 0, apuesta: this.apuesta });
                } else if (['win', 'lose', 'push', 'blackjack'].includes(data.estado)) {
                    this.manoDealer = data.mano_dealer || this.manoDealer;
                    this.puntosDealer = data.puntos_dealer || this.puntosDealer;
                    this.estado = data.estado;
                    this.ganancia = data.ganancia;
                    this.saldo = data.saldo;
                    window.dispatchEvent(new CustomEvent('saldo-updated', { detail: { saldo: data.saldo } }));
                    this.oculto = false;
                    this.fase = 'terminado';
                    this.historial.unshift({ puntos: data.puntos_jugador, dealer_puntos: data.puntos_dealer, ganancia: data.ganancia, apuesta: this.apuesta });
                }
            } catch (e) {
                this.error = 'Error de conexion.';
            }
        },

        async stand() {
            this.oculto = false;

            try {
                const res = await fetch('{{ route("blackjack.stand") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({ mano_jugador: this.manoJugador, mano_dealer: this.manoDealer, baraja: this.baraja, apuesta: this.apuesta }),
                });
                const data = await res.json();

                if (data.error) {
                    this.error = data.error;
                    return;
                }

                this.manoDealer = data.mano_dealer;
                this.puntosDealer = data.puntos_dealer;
                this.estado = data.estado;
                this.ganancia = data.ganancia;
                this.saldo = data.saldo;
                window.dispatchEvent(new CustomEvent('saldo-updated', { detail: { saldo: data.saldo } }));
                this.fase = 'terminado';
                this.historial.unshift({ puntos: data.puntos_jugador, dealer_puntos: data.puntos_dealer, ganancia: data.ganancia, apuesta: this.apuesta });
            } catch (e) {
                this.error = 'Error de conexion.';
            }
        },

        reset() {
            this.manoJugador = [];
            this.manoDealer = [];
            this.puntosJugador = 0;
            this.puntosDealer = 0;
            this.oculto = true;
            this.estado = 'jugando';
            this.fase = '';
            this.ganancia = 0;
            this.baraja = [];
        },
    };
}
</script>
@endpush
@endsection
