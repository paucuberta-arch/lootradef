@extends('layouts.app')

@section('title', 'Mi perfil — Lootra Casino')

@section('contenido')

    <div class="relative min-h-[80vh] flex items-center justify-center px-4 py-16" x-data="profileWallet()">
        <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[500px] h-[500px] bg-brand-500/5 rounded-full blur-[120px] pointer-events-none"></div>

        <div class="relative w-full max-w-4xl">

            @if(session('success'))
                <div class="mb-6 rounded-xl bg-emerald-500/10 border border-emerald-500/20 p-4 text-emerald-400 text-sm text-center">
                    {{ session('success') }}
                </div>
            @endif

            <div class="rounded-2xl bg-white/[0.03] border border-white/5 p-6 sm:p-8 backdrop-blur-sm">

                <div class="text-center mb-8">
                    <div class="w-20 h-20 rounded-2xl bg-gradient-to-br from-brand-400 to-brand-600 flex items-center justify-center text-black text-3xl font-black mx-auto mb-4 shadow-lg shadow-brand-500/20">
                        {{ strtoupper(substr($usuario->name, 0, 1)) }}
                    </div>
                    <h1 class="text-2xl font-bold text-white">{{ $usuario->name }}</h1>
                    <p class="text-slate-500 text-sm mt-1">Mi perfil</p>
                </div>

                <div class="space-y-3 mb-8">
                    <div class="flex items-center justify-between p-4 rounded-xl bg-white/[0.02] border border-white/5">
                        <div>
                            <p class="text-xs font-medium text-slate-500 uppercase tracking-wider">Nombre</p>
                            <p class="text-white font-semibold mt-0.5">{{ $usuario->name }}</p>
                        </div>
                    </div>
                    <div class="flex items-center justify-between p-4 rounded-xl bg-white/[0.02] border border-white/5">
                        <div>
                            <p class="text-xs font-medium text-slate-500 uppercase tracking-wider">Correo electronico</p>
                            <p class="text-white font-semibold mt-0.5">{{ $usuario->email }}</p>
                        </div>
                    </div>
                    <div class="flex items-center justify-between p-4 rounded-xl bg-white/[0.02] border border-white/5">
                        <div>
                            <p class="text-xs font-medium text-slate-500 uppercase tracking-wider">Miembro desde</p>
                            <p class="text-white font-semibold mt-0.5">{{ $usuario->created_at->format('d/m/Y') }}</p>
                        </div>
                    </div>
                    <div class="flex items-center justify-between p-4 rounded-xl bg-brand-500/5 border border-brand-500/20">
                        <div>
                            <p class="text-xs font-medium text-brand-400 uppercase tracking-wider">Saldo</p>
                            <p class="text-white font-semibold mt-0.5">€<span x-text="$store.wallet.saldo.toFixed(2)">{{ number_format($usuario->saldo, 2) }}</span></p>
                        </div>
                        <button @click="depositOpen=true" class="px-3 py-1.5 rounded-lg bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 hover:bg-emerald-500/20 transition text-sm font-bold">Añadir saldo demo</button>
                    </div>
                </div>

                <div class="space-y-3">
                    <a href="{{ url('/') }}" class="block w-full text-center rounded-xl bg-white/5 hover:bg-white/10 text-white font-semibold py-3 border border-white/10 hover:border-white/20 transition-all">
                        Volver al casino
                    </a>
                    <form action="{{ route('logout') }}" method="POST">
                        @csrf
                        <button type="submit" class="w-full rounded-xl bg-red-500/10 hover:bg-red-500/20 text-red-400 font-semibold py-3 border border-red-500/20 hover:border-red-500/30 transition-all">
                            Cerrar sesion
                        </button>
                    </form>
                </div>

            </div>

            <section class="mt-6 rounded-2xl bg-white/[0.03] border border-white/5 p-6 sm:p-8">
                <div class="mb-5 flex items-center justify-between gap-3">
                    <div><h2 class="text-lg font-bold">Movimientos de cartera</h2><p class="mt-1 text-xs text-slate-500">Registro confirmado de débitos y créditos.</p></div>
                    <span class="rounded-lg border border-amber-400/20 bg-amber-400/10 px-3 py-1 text-xs font-bold text-amber-300">Saldo demo</span>
                </div>
                <div class="space-y-2">
                    @forelse($movimientos as $movimiento)
                        <div class="flex items-center gap-3 rounded-xl border border-white/5 bg-black/15 p-3">
                            <span class="grid h-9 w-9 shrink-0 place-items-center rounded-lg {{ $movimiento->direccion === 'credito' ? 'bg-emerald-500/10 text-emerald-300' : 'bg-red-500/10 text-red-300' }}">{{ $movimiento->direccion === 'credito' ? '+' : '−' }}</span>
                            <div class="min-w-0 flex-1"><p class="truncate text-sm font-semibold">{{ $movimiento->etiqueta }}</p><p class="text-xs text-slate-500">{{ $movimiento->created_at->format('d/m/Y H:i') }} · Saldo {{ number_format($movimiento->saldo_posterior, 2, ',', '.') }} €</p></div>
                            <b class="text-sm {{ $movimiento->direccion === 'credito' ? 'text-emerald-300' : 'text-red-300' }}">{{ $movimiento->direccion === 'credito' ? '+' : '−' }}{{ number_format($movimiento->importe, 2, ',', '.') }} €</b>
                        </div>
                    @empty
                        <p class="rounded-xl border border-dashed border-white/10 py-8 text-center text-sm text-slate-500">Todavía no hay movimientos registrados.</p>
                    @endforelse
                </div>
            </section>

        </div>

        <div x-show="depositOpen" x-cloak @keydown.escape.window="depositOpen=false" class="fixed inset-0 z-[120] grid place-items-center bg-black/75 p-4 backdrop-blur-sm">
            <button class="absolute inset-0 cursor-default" @click="depositOpen=false" aria-label="Cerrar depósito"></button>
            <section role="dialog" aria-modal="true" aria-labelledby="deposit-title" class="relative w-full max-w-md rounded-2xl border border-white/10 bg-[#11111f] p-6 shadow-2xl">
                <p class="text-xs font-black uppercase tracking-wider text-amber-300">Entorno de demostración</p>
                <h2 id="deposit-title" class="mt-1 text-xl font-bold">Añadir saldo demo</h2>
                <p class="mt-2 text-sm text-slate-400">Este movimiento no representa un pago real y quedará registrado en tu cartera.</p>
                <label class="mt-5 block text-xs text-slate-400">Importe</label>
                <div class="mt-2 flex items-center rounded-xl border border-white/10 bg-black/20 px-3"><span>€</span><input x-model.number="amount" type="number" min="1" max="50000" class="w-full bg-transparent px-3 py-3 font-bold outline-none"></div>
                <div class="mt-3 grid grid-cols-4 gap-2"><template x-for="value in [10,25,50,100]"><button @click="amount=value" class="rounded-lg bg-white/5 py-2 text-xs hover:bg-white/10" x-text="value+'€'"></button></template></div>
                <p x-show="error" class="mt-3 text-sm text-red-300" x-text="error"></p>
                <div class="mt-5 flex gap-2"><button @click="depositOpen=false" :disabled="busy" class="flex-1 rounded-xl border border-white/10 py-3 text-sm text-slate-400">Cancelar</button><button @click="deposit" :disabled="busy||amount<1" class="flex-1 rounded-xl bg-emerald-500 py-3 font-bold text-slate-950 disabled:opacity-50" x-text="busy?'Procesando…':'Confirmar demo'"></button></div>
            </section>
        </div>
    </div>

@push('scripts')
<script>
function profileWallet(){return{depositOpen:false,amount:50,busy:false,error:'',async deposit(){if(this.busy)return;this.busy=true;this.error='';try{const response=await fetch(@js(route('perfil.deposit')),{method:'POST',headers:{'Content-Type':'application/json','Accept':'application/json','X-CSRF-TOKEN':document.querySelector('meta[name="csrf-token"]').content},body:JSON.stringify({amount:this.amount})});const data=await response.json();if(!response.ok)throw new Error(data.message||data.error||'No se pudo completar el depósito demo.');this.$store.wallet.saldo=Number(data.saldo);window.dispatchEvent(new CustomEvent('saldo-updated',{detail:{saldo:Number(data.saldo)}}));window.location.reload();}catch(error){this.error=error.message;this.busy=false;}}}}
</script>
@endpush
@endsection
