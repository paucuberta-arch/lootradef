@extends('layouts.app')

@section('title', 'Mi perfil — Lootra Casino')

@section('contenido')

    <div class="relative min-h-[80vh] flex items-center justify-center px-4 py-8 sm:py-16" x-data="profileWallet()">
        <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 h-[min(500px,90vw)] w-[min(500px,90vw)] bg-brand-500/5 rounded-full blur-[120px] pointer-events-none"></div>

        <div class="relative w-full max-w-6xl">

            @if(session('success'))
                <div class="mb-6 rounded-xl bg-emerald-500/10 border border-emerald-500/20 p-4 text-emerald-400 text-sm text-center">
                    {{ session('success') }}
                </div>
            @endif

            <div class="mb-6 grid grid-cols-2 gap-3 sm:grid-cols-4">
                @foreach([['Saldo', number_format($usuario->saldo, 2, ',', '.').' €'], ['Partidas', $resumen['partidas']], ['Inventario', $resumen['inventario']], ['Apuestas', $resumen['apuestas']]] as [$label, $value])
                    <x-ui.card padding="p-3 min-[420px]:p-4"><p class="text-[10px] min-[420px]:text-xs font-bold uppercase tracking-wider text-slate-500">{{ $label }}</p><p class="mt-2 break-words font-display text-lg min-[420px]:text-xl font-bold text-white">{{ $value }}</p></x-ui.card>
                @endforeach
            </div>

            @if($campaignChallenge)
            <section class="mb-6 rounded-2xl border border-fuchsia-400/20 bg-gradient-to-br from-fuchsia-500/10 to-cyan-500/5 p-5 sm:p-7">
                <div class="flex flex-col gap-5 sm:flex-row sm:items-start sm:justify-between"><div><p class="text-xs font-black uppercase tracking-[.2em] text-fuchsia-300">Mi reto contra RickyEdit</p><h2 class="mt-2 text-2xl font-black">{{ $campaignChallenge->public_alias }}</h2></div><span class="self-start rounded-full border border-white/10 bg-black/20 px-3 py-1 text-xs font-bold uppercase text-cyan-200">{{ $campaignChallenge->status }}</span></div>
                @if($campaignChallenge->status === 'active')<x-campaign.rickyedit.progress class="mt-5" />@endif
                @php($playedUntil = $campaignChallenge->completed_at ?? now())
                <div class="mt-5 grid grid-cols-2 gap-3 sm:grid-cols-5">@foreach([
                    ['Puntuación', number_format($campaignChallenge->score ?? floor($campaignChallenge->current_balance))],
                    ['Saldo final', number_format($campaignChallenge->final_balance ?? $campaignChallenge->current_balance,2,',','.')],
                    ['Partidas', $campaignChallenge->games_played],
                    ['Tiempo jugado', $campaignChallenge->started_at ? gmdate('i:s', min(($rickyeditCampaign['duration_minutes'] ?? 15) * 60, $campaignChallenge->started_at->diffInSeconds($playedUntil))) : '00:00'],
                    ['Posición', $campaignPosition ? '#'.$campaignPosition : '—'],
                ] as [$label,$value])<div class="rounded-xl bg-black/20 p-3"><p class="text-[10px] uppercase tracking-wider text-slate-500">{{ $label }}</p><b class="mt-1 block">{{ $value }}</b></div>@endforeach</div>
                <div class="mt-5 flex flex-col gap-2 sm:flex-row"><a href="{{ route('rickyedit.ranking') }}" class="rounded-xl bg-white px-4 py-3 text-center text-sm font-black text-slate-950">Ver ranking</a><button type="button" data-campaign-share onclick="navigator.share?.({title:'Mi reto contra RickyEdit',url:'{{ route('rickyedit.ranking') }}'})" class="rounded-xl border border-white/10 px-4 py-3 text-sm font-bold">Compartir resultado</button></div>
            </section>
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
                            <p class="break-all text-white font-semibold mt-0.5">{{ $usuario->email }}</p>
                        </div>
                    </div>
                    <div class="flex items-center justify-between p-4 rounded-xl bg-white/[0.02] border border-white/5">
                        <div>
                            <p class="text-xs font-medium text-slate-500 uppercase tracking-wider">Miembro desde</p>
                            <p class="text-white font-semibold mt-0.5">{{ $usuario->created_at->format('d/m/Y') }}</p>
                        </div>
                    </div>
                    <div class="flex flex-col items-start gap-3 p-4 rounded-xl bg-brand-500/5 border border-brand-500/20 min-[480px]:flex-row min-[480px]:items-center min-[480px]:justify-between">
                        <div>
                            <p class="text-xs font-medium text-brand-400 uppercase tracking-wider">Saldo</p>
                            <p class="text-white font-semibold mt-0.5">€<span x-text="$store.wallet.saldo.toFixed(2)">{{ number_format($usuario->saldo, 2) }}</span></p>
                        </div>
                        @if(!$campaignChallenge || $campaignChallenge->status !== 'active')<button @click="depositOpen=true" class="px-3 py-1.5 rounded-lg bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 hover:bg-emerald-500/20 transition text-sm font-bold">Añadir saldo demo</button>@else<span class="text-xs text-cyan-300">La cartera normal permanece separada durante el reto</span>@endif
                    </div>
                </div>

                <div class="space-y-3">
                    <a href="{{ route('games.index') }}" class="block w-full text-center rounded-xl bg-white/5 hover:bg-white/10 text-white font-semibold py-3 border border-white/10 hover:border-white/20 transition-all">
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

            <div class="mt-6 grid gap-6 lg:grid-cols-2">
                <x-ui.card>
                    <div class="mb-4 flex items-center justify-between"><div><h2 class="text-lg font-bold">Inventario disponible</h2><p class="text-xs text-slate-500">Premios pendientes de canje.</p></div><a href="{{ route('cases.index') }}#inventario" class="text-xs font-bold text-cyan-300">Ver todo</a></div>
                    <div class="grid grid-cols-2 gap-3">
                        @forelse($inventario as $item)
                            <div class="rounded-xl border border-white/5 bg-black/15 p-3"><img src="{{ $item->imagen ?: asset('images/game-fallback.svg') }}" alt="" class="mb-2 h-16 w-full rounded-lg object-cover" loading="lazy" decoding="async"><p class="truncate text-sm font-bold">{{ $item->nombre }}</p><p class="text-xs text-brand-300">{{ number_format($item->valor_canje, 2, ',', '.') }} €</p></div>
                        @empty
                            <p class="col-span-2 rounded-xl border border-dashed border-white/10 py-8 text-center text-sm text-slate-500">Abre una caja para conseguir tu primer artículo.</p>
                        @endforelse
                    </div>
                </x-ui.card>
                <x-ui.card>
                    <div class="mb-4"><h2 class="text-lg font-bold">Últimas partidas</h2><p class="text-xs text-slate-500">Actividad reciente en el casino.</p></div>
                    <div class="space-y-2">
                        @forelse($ultimasPartidas as $partida)
                            <div class="flex items-center justify-between gap-3 rounded-xl border border-white/5 bg-black/15 p-3"><div class="min-w-0"><p class="truncate text-sm font-bold">{{ str($partida->juego)->replace('_', ' ')->title() }}</p><p class="text-xs text-slate-500">Apuesta {{ number_format($partida->apuesta, 2, ',', '.') }} €</p></div><b class="text-sm {{ $partida->ganancia > 0 ? 'text-emerald-300' : 'text-red-300' }}">{{ number_format($partida->ganancia, 2, ',', '.') }} €</b></div>
                        @empty
                            <p class="rounded-xl border border-dashed border-white/10 py-8 text-center text-sm text-slate-500">Aún no has jugado ninguna partida.</p>
                        @endforelse
                    </div>
                </x-ui.card>
            </div>

            <section class="mt-6 rounded-2xl bg-white/[0.03] border border-white/5 p-6 sm:p-8">
                <div class="mb-5 flex flex-col items-start justify-between gap-3 min-[480px]:flex-row min-[480px]:items-center">
                    <div><h2 class="text-lg font-bold">Movimientos de cartera</h2><p class="mt-1 text-xs text-slate-500">Registro confirmado de débitos y créditos.</p></div>
                    <span class="rounded-lg border border-amber-400/20 bg-amber-400/10 px-3 py-1 text-xs font-bold text-amber-300">Saldo demo</span>
                </div>
                <div class="space-y-2">
                    <template x-if="lastMovement"><div class="flex items-center gap-3 rounded-xl border border-emerald-400/20 bg-emerald-500/10 p-3"><span class="grid h-9 w-9 shrink-0 place-items-center rounded-lg bg-emerald-500/15 text-emerald-300">+</span><div class="min-w-0 flex-1"><p class="text-sm font-semibold" x-text="lastMovement.label"></p><p class="break-words text-xs text-slate-500" x-text="lastMovement.created_at+' · Saldo '+money(lastMovement.balance)"></p></div><b class="shrink-0 text-sm text-emerald-300" x-text="'+'+money(lastMovement.amount)"></b></div></template>
                    @forelse($movimientos as $movimiento)
                        <div class="flex items-center gap-3 rounded-xl border border-white/5 bg-black/15 p-3">
                            <span class="grid h-9 w-9 shrink-0 place-items-center rounded-lg {{ $movimiento->direccion === 'credito' ? 'bg-emerald-500/10 text-emerald-300' : 'bg-red-500/10 text-red-300' }}">{{ $movimiento->direccion === 'credito' ? '+' : '−' }}</span>
                            <div class="min-w-0 flex-1"><p class="truncate text-sm font-semibold">{{ $movimiento->etiqueta }}</p><p class="break-words text-xs text-slate-500">{{ $movimiento->created_at->format('d/m/Y H:i') }} · Saldo {{ number_format($movimiento->saldo_posterior, 2, ',', '.') }} €</p></div>
                            <b class="shrink-0 text-sm {{ $movimiento->direccion === 'credito' ? 'text-emerald-300' : 'text-red-300' }}">{{ $movimiento->direccion === 'credito' ? '+' : '−' }}{{ number_format($movimiento->importe, 2, ',', '.') }} €</b>
                        </div>
                    @empty
                        <p class="rounded-xl border border-dashed border-white/10 py-8 text-center text-sm text-slate-500">Todavía no hay movimientos registrados.</p>
                    @endforelse
                </div>
            </section>

        </div>

        <x-ui.modal name="depositOpen" title="Añadir saldo demo" max-width="max-w-md">
                <p class="text-xs font-black uppercase tracking-wider text-amber-300">Entorno de demostración</p>
                <p class="mt-2 text-sm text-slate-400">Este movimiento no representa un pago real y quedará registrado en tu cartera.</p>
                <x-ui.money-input x-model.number="amount" min="1" max="50000" class="mt-5" label="Importe demo" />
                <div class="mt-3 grid grid-cols-4 gap-2"><template x-for="value in [10,25,50,100]"><button @click="amount=value" class="rounded-lg bg-white/5 py-2 text-xs hover:bg-white/10" x-text="value+'€'"></button></template></div>
                <p x-show="error" class="mt-3 text-sm text-red-300" x-text="error"></p>
                <p x-show="success" class="mt-3 text-sm text-emerald-300" x-text="success"></p>
                <div class="mt-5 flex gap-2"><button @click="depositOpen=false" :disabled="busy" class="flex-1 rounded-xl border border-white/10 py-3 text-sm text-slate-400">Cancelar</button><button @click="deposit" :disabled="busy||amount<1" class="flex-1 rounded-xl bg-emerald-500 py-3 font-bold text-slate-950 disabled:opacity-50" x-text="busy?'Procesando…':'Confirmar demo'"></button></div>
        </x-ui.modal>
    </div>

@push('scripts')
<script>
function profileWallet(){return{depositOpen:false,amount:50,busy:false,error:'',success:'',lastMovement:null,money(value){return new Intl.NumberFormat('es-ES',{style:'currency',currency:'EUR'}).format(Number(value)||0)},async deposit(){if(this.busy)return;this.busy=true;this.error='';this.success='';try{const response=await fetch(@js(route('wallet.demo-deposit')),{method:'POST',headers:{'Content-Type':'application/json','Accept':'application/json','X-CSRF-TOKEN':document.querySelector('meta[name="csrf-token"]').content},body:JSON.stringify({amount:this.amount})});const data=await response.json();if(!response.ok)throw new Error(data.message||data.error||'No se pudo completar el depósito demo.');this.$store.wallet.saldo=Number(data.saldo);this.lastMovement=data.movement;this.success='Saldo demo añadido correctamente.';window.dispatchEvent(new CustomEvent('saldo-updated',{detail:{saldo:Number(data.saldo)}}));setTimeout(()=>{this.depositOpen=false;this.success=''},900)}catch(error){this.error=error.message}finally{this.busy=false}}}}
</script>
@endpush
@endsection
