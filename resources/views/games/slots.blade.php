@extends('layouts.app')
@section('title', 'Slots — Lootra Casino')

@section('styles')
<style>
    @keyframes spin-reel { 0% { transform: translateY(0); } 100% { transform: translateY(-300%); } }
    .reel-spin { animation: spin-reel 0.6s cubic-bezier(0.25, 0.1, 0.25, 1); }
    @keyframes win-flash { 0%,100% { opacity:1; } 50% { opacity:0.5; } }
    .win-flash { animation: win-flash 0.4s ease-in-out 4; }
    @keyframes coin-fall { 0% { transform: translateY(-20px) rotate(0deg); opacity:1; } 100% { transform: translateY(100px) rotate(360deg); opacity:0; } }
    .coin { animation: coin-fall 1s ease-in forwards; }
</style>
@endsection

@section('contenido')
<div class="max-w-[1400px] mx-auto px-4 sm:px-6 lg:px-8 py-6 sm:py-10"
     x-data="slotsGame()">

    <div class="flex flex-col lg:flex-row gap-6">

        <div class="flex-1 min-w-0">

            {{-- Header --}}
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h1 class="text-2xl font-extrabold text-white">🎰 Slots</h1>
                    <p class="text-sm text-slate-500 mt-1">Gira los carretes y gana premios</p>
                </div>
                <div class="px-4 py-2 rounded-xl bg-white/5 border border-white/10">
                    <span class="text-xs text-slate-500">Saldo</span>
                    <span class="ml-2 text-sm font-bold text-brand-400" x-text="'€' + saldo.toFixed(2)"></span>
                </div>
            </div>

            {{-- Maquina tragaperras --}}
            <div class="rounded-2xl bg-white/[0.03] border border-white/5 p-6 sm:p-8 mb-6">
                <div class="max-w-md mx-auto">
                    {{-- Carretes --}}
                    <div class="bg-[#0d0d18] rounded-2xl border-2 border-white/10 p-4 mb-6 relative overflow-hidden">
                        <div class="grid grid-cols-3 gap-3">
                            <template x-for="(reel, i) in reels" :key="i">
                                <div class="aspect-square rounded-xl bg-white/[0.03] border border-white/5 flex items-center justify-center text-4xl sm:text-5xl"
                                     :class="spinning ? 'opacity-30' : (ganancia > 0 ? 'win-flash border-brand-500/50' : '')"
                                     x-text="spinning ? '❓' : reel">
                                </div>
                            </template>
                        </div>

                        {{-- Linea central --}}
                        <div class="absolute left-0 right-0 top-1/2 -translate-y-1/2 h-0.5 bg-brand-500/30 pointer-events-none"></div>
                    </div>

                    {{-- Resultado --}}
                    <div x-show="lastResult" class="text-center mb-4">
                        <template x-if="ganancia > 0">
                            <div class="text-emerald-400 font-bold text-lg">
                                ¡Ganaste <span x-text="'€' + ganancia.toFixed(2)"></span>!
                            </div>
                        </template>
                        <template x-if="ganancia === 0 && lastResult">
                            <div class="text-red-400 font-bold text-lg">Sin suerte esta vez</div>
                        </template>
                    </div>

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
                                    class="px-8 py-3 rounded-xl bg-brand-500 hover:bg-brand-400 text-black font-bold transition shadow-lg shadow-brand-500/20 disabled:opacity-50 disabled:cursor-not-allowed">
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
                    <div class="p-3 rounded-xl bg-white/[0.02] border border-white/5 text-center">
                        <div class="text-2xl mb-1">7️⃣7️⃣7️⃣</div>
                        <div class="text-brand-400 font-bold">x50</div>
                    </div>
                    <div class="p-3 rounded-xl bg-white/[0.02] border border-white/5 text-center">
                        <div class="text-2xl mb-1">💎💎💎</div>
                        <div class="text-brand-400 font-bold">x25</div>
                    </div>
                    <div class="p-3 rounded-xl bg-white/[0.02] border border-white/5 text-center">
                        <div class="text-2xl mb-1">🔔🔔🔔</div>
                        <div class="text-brand-400 font-bold">x20</div>
                    </div>
                    <div class="p-3 rounded-xl bg-white/[0.02] border border-white/5 text-center">
                        <div class="text-2xl mb-1">⭐⭐⭐</div>
                        <div class="text-brand-400 font-bold">x15</div>
                    </div>
                    <div class="p-3 rounded-xl bg-white/[0.02] border border-white/5 text-center">
                        <div class="text-2xl mb-1">🍒🍒🍒</div>
                        <div class="text-brand-400 font-bold">x10</div>
                    </div>
                    <div class="p-3 rounded-xl bg-white/[0.02] border border-white/5 text-center">
                        <div class="text-2xl mb-1">X X X</div>
                        <div class="text-emerald-400 font-bold">x2 (pareja)</div>
                    </div>
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
        saldo: {{ Auth::user()->cartera->saldo ?? 1000 }},
        apuesta: 1,
        reels: ['🍒', '🍋', '🍊'],
        spinning: false,
        ganancia: 0,
        lastResult: false,
        historial: @js($partidas->map(fn($p) => ['reels' => $p->detalles['reels'] ?? ['?','?','?'], 'ganancia' => $p->ganancia, 'apuesta' => $p->apuesta])->take(10)->all()),

        async spin() {
            if (this.spinning || this.apuesta > this.saldo) return;
            this.spinning = true;
            this.ganancia = 0;
            this.lastResult = false;

            try {
                const res = await fetch('{{ route("slots.play") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({ apuesta: this.apuesta }),
                });
                const data = await res.json();

                if (data.errors) return;

                await new Promise(r => setTimeout(r, 600));

                this.reels = data.reels;
                this.ganancia = data.ganancia;
                this.lastResult = true;
                this.saldo = data.saldo;
                this.historial.unshift({ reels: data.reels, ganancia: data.ganancia, apuesta: this.apuesta });
            } catch (e) {}

            this.spinning = false;
        },
    };
}
</script>
@endpush
@endsection
