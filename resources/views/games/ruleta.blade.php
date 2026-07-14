@extends('layouts.app')
@section('title', 'Ruleta — Lootra Casino')

@section('styles')
<style>
    @keyframes spin-wheel { 0% { transform: rotate(0deg); } 100% { transform: rotate(3600deg); } }
    .wheel-spin { animation: spin-wheel 3s cubic-bezier(0.17, 0.67, 0.12, 0.99) forwards; }
    @keyframes number-pop { 0% { transform: scale(0.5); opacity:0; } 50% { transform: scale(1.2); } 100% { transform: scale(1); opacity:1; } }
    .number-pop { animation: number-pop 0.4s ease-out forwards; }
</style>
@endsection

@section('contenido')
<div class="max-w-[1400px] mx-auto px-4 sm:px-6 lg:px-8 py-6 sm:py-10"
     x-data="ruletaGame()">

    <div class="flex flex-col lg:flex-row gap-6">

        <div class="flex-1 min-w-0">

            {{-- Header --}}
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h1 class="text-2xl font-extrabold text-white">🎲 Ruleta Europea</h1>
                    <p class="text-sm text-slate-500 mt-1">Apuesta al numero, color o grupo</p>
                </div>
                <div class="px-4 py-2 rounded-xl bg-white/5 border border-white/10">
                    <span class="text-xs text-slate-500">Saldo</span>
                    <span class="ml-2 text-sm font-bold text-brand-400" x-text="'€' + saldo.toFixed(2)"></span>
                </div>
            </div>

            {{-- Ruleta visual --}}
            <div class="rounded-2xl bg-white/[0.03] border border-white/5 p-6 sm:p-8 mb-6">
                <div class="max-w-lg mx-auto text-center">

                    {{-- Numero resultado --}}
                    <div class="mb-6 h-20 flex items-center justify-center">
                        <template x-if="lastNumero !== null">
                            <div class="number-pop inline-flex items-center justify-center w-20 h-20 rounded-2xl text-3xl font-black"
                                 :class="lastColor === 'rojo' ? 'bg-red-500 text-white' : lastColor === 'negro' ? 'bg-slate-800 text-white border border-white/20' : 'bg-emerald-600 text-white'"
                                 x-text="lastNumero"></div>
                        </template>
                        <template x-if="lastNumero === null">
                            <div class="text-slate-600 text-lg">Elige tu apuesta y gira</div>
                        </template>
                    </div>

                    {{-- Tablero rapido --}}
                    <div class="grid grid-cols-3 gap-3 mb-6">
                        <button @click="tipo = 'rojo'; valor = null"
                                :class="tipo === 'rojo' ? 'ring-2 ring-white/50 bg-red-600' : 'bg-red-600/80 hover:bg-red-600'"
                                class="py-4 rounded-xl text-white font-bold text-sm transition">
                            🔴 Rojo
                        </button>
                        <button @click="tipo = 'negro'; valor = null"
                                :class="tipo === 'negro' ? 'ring-2 ring-white/50 bg-slate-700' : 'bg-slate-800 hover:bg-slate-700'"
                                class="py-4 rounded-xl text-white font-bold text-sm transition">
                            ⚫ Negro
                        </button>
                        <button @click="tipo = 'numero'; valor = 0"
                                :class="tipo === 'numero' && valor === 0 ? 'ring-2 ring-white/50 bg-emerald-600' : 'bg-emerald-700 hover:bg-emerald-600'"
                                class="py-4 rounded-xl text-white font-bold text-sm transition">
                            0 🟢
                        </button>
                    </div>

                    {{-- Grupos --}}
                    <div class="grid grid-cols-2 gap-3 mb-4">
                        <button @click="tipo = 'par'; valor = null"
                                :class="tipo === 'par' ? 'ring-2 ring-brand-500 bg-brand-500/20 text-brand-400' : 'bg-white/5 text-slate-400 hover:text-white hover:bg-white/10'"
                                class="py-3 rounded-xl font-bold text-sm transition">
                            Par
                        </button>
                        <button @click="tipo = 'impar'; valor = null"
                                :class="tipo === 'impar' ? 'ring-2 ring-brand-500 bg-brand-500/20 text-brand-400' : 'bg-white/5 text-slate-400 hover:text-white hover:bg-white/10'"
                                class="py-3 rounded-xl font-bold text-sm transition">
                            Impar
                        </button>
                    </div>
                    <div class="grid grid-cols-3 gap-3 mb-6">
                        <button @click="tipo = 'docena1'; valor = null"
                                :class="tipo === 'docena1' ? 'ring-2 ring-brand-500 bg-brand-500/20 text-brand-400' : 'bg-white/5 text-slate-400 hover:text-white hover:bg-white/10'"
                                class="py-3 rounded-xl font-bold text-sm transition">
                            1-12
                        </button>
                        <button @click="tipo = 'docena2'; valor = null"
                                :class="tipo === 'docena2' ? 'ring-2 ring-brand-500 bg-brand-500/20 text-brand-400' : 'bg-white/5 text-slate-400 hover:text-white hover:bg-white/10'"
                                class="py-3 rounded-xl font-bold text-sm transition">
                            13-24
                        </button>
                        <button @click="tipo = 'docena3'; valor = null"
                                :class="tipo === 'docena3' ? 'ring-2 ring-brand-500 bg-brand-500/20 text-brand-400' : 'bg-white/5 text-slate-400 hover:text-white hover:bg-white/10'"
                                class="py-3 rounded-xl font-bold text-sm transition">
                            25-36
                        </button>
                    </div>

                    {{-- Numero especifico --}}
                    <div x-show="tipo === 'numero'" class="mb-6 p-4 rounded-xl bg-white/[0.03] border border-white/5">
                        <label class="text-xs text-slate-500 mb-2 block">Numero especifico (paga x35)</label>
                        <input type="number" x-model.number="valor" min="0" max="36"
                               class="w-full px-4 py-3 rounded-xl bg-white/5 border border-white/10 text-white text-center text-lg font-bold outline-none focus:border-brand-500 transition">
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
                            <button @click="play()" :disabled="spinning || apuesta > saldo"
                                    class="px-8 py-3 rounded-xl bg-brand-500 hover:bg-brand-400 text-black font-bold transition shadow-lg shadow-brand-500/20 disabled:opacity-50">
                                <span x-text="spinning ? 'Girando...' : 'Girar'"></span>
                            </button>
                        </div>
                    </div>

                    {{-- Resultado --}}
                    <div x-show="ganancia > 0" class="mt-4 text-emerald-400 font-bold text-lg number-pop">
                        ¡Ganaste <span x-text="'€' + ganancia.toFixed(2)"></span>!
                    </div>
                </div>
            </div>
        </div>

        {{-- Sidebar --}}
        <aside class="w-full lg:w-72 shrink-0 space-y-5">
            <div class="rounded-2xl bg-white/[0.03] border border-white/5 p-5">
                <h3 class="text-sm font-bold text-white uppercase tracking-wider mb-3">Pagos</h3>
                <div class="space-y-2 text-sm">
                    <div class="flex justify-between"><span class="text-slate-500">Numero exacto</span><span class="text-brand-400 font-bold">x35</span></div>
                    <div class="flex justify-between"><span class="text-slate-500">Rojo / Negro</span><span class="text-white font-semibold">x2</span></div>
                    <div class="flex justify-between"><span class="text-slate-500">Par / Impar</span><span class="text-white font-semibold">x2</span></div>
                    <div class="flex justify-between"><span class="text-slate-500">Docena (12 nums)</span><span class="text-white font-semibold">x3</span></div>
                </div>
            </div>

            <div class="rounded-2xl bg-gradient-to-br from-brand-500/20 to-brand-600/10 border border-brand-500/20 p-5">
                <h3 class="text-sm font-bold text-white mb-2">¿Cómo funciona?</h3>
                <p class="text-xs text-slate-400 leading-relaxed">Elige tu tipo de apuesta, introduce la cantidad y gira. Puedes apostar a color, par/impar, docena o un numero exacto.</p>
            </div>

            {{-- Historial --}}
            <div class="rounded-2xl bg-white/[0.03] border border-white/5 p-5">
                <h3 class="text-sm font-bold text-white uppercase tracking-wider mb-3">Historial</h3>
                <div class="space-y-2">
                    <template x-for="(h, i) in historial.slice(0, 8)" :key="i">
                        <div class="flex items-center gap-2 text-xs">
                            <span class="w-6 h-6 rounded-full flex items-center justify-center text-white font-bold text-[10px]"
                                  :class="h.color === 'rojo' ? 'bg-red-500' : h.color === 'negro' ? 'bg-slate-700' : 'bg-emerald-600'"
                                  x-text="h.numero"></span>
                            <span class="text-slate-400" x-text="h.tipo"></span>
                            <span class="ml-auto" :class="h.ganancia > 0 ? 'text-emerald-400' : 'text-red-400'"
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
function ruletaGame() {
    return {
        saldo: {{ Auth::user()->cartera->saldo ?? 1000 }},
        apuesta: 1,
        tipo: 'rojo',
        valor: null,
        spinning: false,
        ganancia: 0,
        lastNumero: null,
        lastColor: null,
        historial: @js($partidas->map(fn($p) => ['numero' => $p->detalles['numero'] ?? 0, 'color' => $p->detalles['color'] ?? 'verde', 'tipo' => $p->detalles['tipo_apuesta'] ?? '', 'ganancia' => $p->ganancia, 'apuesta' => $p->apuesta])->take(10)->all()),

        async play() {
            if (this.spinning || this.apuesta > this.saldo) return;
            this.spinning = true;
            this.ganancia = 0;

            try {
                const res = await fetch('{{ route("ruleta.play") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({ apuesta: this.apuesta, tipo: this.tipo, valor: this.valor }),
                });
                const data = await res.json();

                if (data.errors) return;

                await new Promise(r => setTimeout(r, 1500));

                this.lastNumero = data.numero;
                this.lastColor = data.color;
                this.ganancia = data.ganancia;
                this.saldo = data.saldo;
                this.historial.unshift({ numero: data.numero, color: data.color, tipo: this.tipo, ganancia: data.ganancia, apuesta: this.apuesta });
            } catch (e) {}

            this.spinning = false;
        },
    };
}
</script>
@endpush
@endsection
