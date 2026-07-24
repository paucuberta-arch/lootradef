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
                @foreach([
                    ['Saldo demo', number_format($usuario->saldo, 2, ',', '.').' EUR Demo', 3],
                    ['Partidas', $resumen['partidas'], 8],
                    ['Inventario', $resumen['inventario'], 6],
                    ['Apuestas', $resumen['apuestas'], 2],
                ] as [$label, $value, $badge])
                    <div class="world-panel group rounded-2xl p-3 min-[420px]:p-4">
                        <img src="{{ asset(sprintf('images/lootra_visual_pack/07_badges/badge_%02d_512.png', $badge)) }}" alt="" class="pointer-events-none absolute -bottom-5 -right-4 h-24 w-24 object-contain opacity-20 transition duration-500 group-hover:scale-110 group-hover:opacity-35" loading="lazy" aria-hidden="true">
                        <p class="relative text-[10px] font-bold uppercase tracking-wider text-slate-500 min-[420px]:text-xs">{{ $label }}</p>
                        <p class="relative mt-2 break-words font-display text-lg font-bold text-white min-[420px]:text-xl">{{ $value }}</p>
                    </div>
                @endforeach
            </div>

            <section class="mb-6 rounded-2xl border border-cyan-400/15 bg-cyan-400/[.04] p-4 sm:p-5">
                <div class="flex flex-wrap items-center justify-between gap-3"><div><p class="text-[10px] font-black uppercase tracking-[.2em] text-cyan-300">Resumen económico demo</p><h2 class="mt-1 text-lg font-bold text-white">Tu situación en EUR Demo</h2></div><span class="rounded-lg border border-amber-400/20 bg-amber-400/10 px-3 py-1 text-[10px] font-black uppercase text-amber-300">Sin valor monetario</span></div>
                <div class="mt-4 grid grid-cols-2 gap-3 sm:grid-cols-4">
                    @foreach([
                        ['Saldo disponible', number_format($financialSummary['available'], 2, ',', '.').' EUR Demo'],
                        ['Saldo reservado', number_format($financialSummary['reserved'], 2, ',', '.').' EUR Demo'],
                        ['Total apostado', number_format($financialSummary['wagered'], 2, ',', '.').' EUR Demo'],
                        ['Total premiado', number_format($financialSummary['won'], 2, ',', '.').' EUR Demo'],
                        ['Depositado demo', number_format($financialSummary['deposited'], 2, ',', '.').' EUR Demo'],
                        ['Retirado demo', number_format($financialSummary['withdrawn'], 2, ',', '.').' EUR Demo'],
                        ['Ganancia/pérdida neta', number_format($financialSummary['net'], 2, ',', '.').' EUR Demo'],
                    ] as [$label, $value])
                        <div class="rounded-xl border border-white/5 bg-black/20 p-3"><p class="text-[10px] uppercase tracking-wider text-slate-500">{{ $label }}</p><p class="mt-1 break-words text-sm font-black {{ str_contains($label, 'neta') ? ($financialSummary['net'] >= 0 ? 'text-emerald-300' : 'text-red-300') : 'text-white' }}">{{ $value }}</p></div>
                    @endforeach
                </div>
            </section>

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

            <section class="world-panel relative overflow-hidden rounded-3xl">
                <img src="{{ asset('images/lootra_visual_pack/05_top_panels/panel_perfil_1920x360.webp') }}" alt="" class="absolute inset-x-0 top-0 h-64 w-full object-cover opacity-70" aria-hidden="true" decoding="async">
                <div class="absolute inset-0 bg-gradient-to-b from-[#070816]/10 via-[#070816]/75 to-[#070816]"></div>
                <div class="relative grid gap-8 p-5 sm:p-8 lg:grid-cols-[280px_minmax(0,1fr)] lg:p-10">
                    <div class="flex flex-col items-center justify-center text-center lg:items-start lg:text-left">
                        <div class="visual-float relative mb-5 h-32 w-32 sm:h-40 sm:w-40">
                            <div class="absolute inset-3 rounded-[2rem] bg-gradient-to-br from-cyan-300 via-fuchsia-500 to-amber-300 blur-xl opacity-40"></div>
                            <x-ui.user-avatar :user="$usuario" size="custom" shape="custom" class="relative h-full w-full rounded-[2rem] ring-white/20 shadow-2xl shadow-fuchsia-950/60" alt="Foto de perfil de {{ $usuario->name }}" loading="eager" />
                        </div>
                        <span class="world-kicker">Perfil de jugador</span>
                        <h1 class="mt-3 break-words font-display text-3xl font-black text-white sm:text-4xl">{{ $usuario->name }}</h1>
                        <p class="mt-2 text-sm text-cyan-200/70">Miembro desde {{ $usuario->created_at->format('Y') }}</p>
                    </div>

                    <div class="self-end rounded-2xl border border-white/10 bg-[#070816]/65 p-4 backdrop-blur-xl sm:p-6">
                        <div class="mb-5 flex flex-wrap items-center justify-between gap-3"><div><p class="text-xs font-black uppercase tracking-[.18em] text-fuchsia-300">Identidad Lootra</p><h2 class="mt-1 text-xl font-bold">Tu centro personal</h2></div><span class="rounded-full border border-cyan-300/20 bg-cyan-300/10 px-3 py-1 text-xs font-bold text-cyan-200">Cuenta activa</span></div>
                        <div class="space-y-3 mb-6">
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
                            <p class="text-white font-semibold mt-0.5"><span x-text="$store.wallet.saldo.toFixed(2)">{{ number_format($usuario->saldo, 2) }}</span> EUR Demo</p>
                        </div>
                        @if(config('features.demo_deposits.enabled') && $usuario->is_demo)
                            @if(!$campaignChallenge || $campaignChallenge->status !== 'active')<button @click="depositOpen=true" class="px-3 py-1.5 rounded-lg bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 hover:bg-emerald-500/20 transition text-sm font-bold">Añadir saldo demo</button>@else<span class="text-xs text-cyan-300">La cartera normal permanece separada durante el reto</span>@endif
                        @endif
                        @if(config('features.economy.enabled') && $usuario->is_demo && (!$campaignChallenge || $campaignChallenge->status !== 'active'))<button @click="withdrawalOpen=true" class="px-3 py-1.5 rounded-lg bg-amber-500/10 border border-amber-500/20 text-amber-300 hover:bg-amber-500/20 transition text-sm font-bold">Solicitar retirada demo</button>@endif
                    </div>
                        </div>

                        <div class="grid gap-3 sm:grid-cols-2">
                    <a href="{{ route('games.index') }}" class="block w-full text-center rounded-xl bg-gradient-to-r from-cyan-400 to-emerald-400 text-slate-950 font-bold py-3 transition hover:brightness-110">
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
                </div>
            </section>

            <div class="mt-6 grid gap-6 lg:grid-cols-2">
                <x-ui.card>
                    <div class="mb-4 flex items-center justify-between"><div><h2 class="text-lg font-bold">Inventario disponible</h2><p class="text-xs text-slate-500">Recompensas virtuales pendientes de activar.</p></div><a href="{{ route('cases.index') }}#inventario" class="text-xs font-bold text-cyan-300">Ver todo</a></div>
                    <div class="grid grid-cols-2 gap-3">
                        @forelse($inventario as $item)
                            <div class="rounded-xl border border-white/5 bg-black/15 p-3"><img src="{{ $item->imagen ?: asset('images/game-fallback.svg') }}" alt="" class="mb-2 h-16 w-full rounded-lg object-cover" loading="lazy" decoding="async"><p class="truncate text-sm font-bold">{{ $item->nombre }}</p><p class="text-xs text-brand-300">{{ (int) ($item->valor_virtual ?: round($item->valor_canje * 100)) }} puntos virtuales</p></div>
                        @empty
                            <p class="col-span-2 rounded-xl border border-dashed border-white/10 py-8 text-center text-sm text-slate-500">Abre una caja para conseguir tu primer artículo.</p>
                        @endforelse
                    </div>
                </x-ui.card>
                <x-ui.card>
                    <div class="mb-4"><h2 class="text-lg font-bold">Últimas partidas</h2><p class="text-xs text-slate-500">Actividad reciente en el casino.</p></div>
                    <div class="space-y-2">
                        @forelse($ultimasPartidas as $partida)
                            <div class="flex items-center justify-between gap-3 rounded-xl border border-white/5 bg-black/15 p-3"><div class="min-w-0"><p class="truncate text-sm font-bold">{{ str($partida->juego)->replace('_', ' ')->title() }}</p><p class="text-xs text-slate-500">Apuesta {{ number_format($partida->apuesta, 2, ',', '.') }} EUR Demo</p></div><b class="text-sm {{ $partida->ganancia > 0 ? 'text-emerald-300' : 'text-red-300' }}">{{ number_format($partida->ganancia, 2, ',', '.') }} EUR Demo</b></div>
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
                            <div class="min-w-0 flex-1"><p class="truncate text-sm font-semibold">{{ $movimiento->etiqueta }}</p><p class="break-words text-xs text-slate-500">{{ $movimiento->created_at->format('d/m/Y H:i') }} · Saldo {{ number_format($movimiento->saldo_posterior, 2, ',', '.') }} EUR Demo</p></div>
                            <b class="shrink-0 text-sm {{ $movimiento->direccion === 'credito' ? 'text-emerald-300' : 'text-red-300' }}">{{ $movimiento->direccion === 'credito' ? '+' : '−' }}{{ number_format($movimiento->importe, 2, ',', '.') }} EUR Demo</b>
                        </div>
                    @empty
                        <p class="rounded-xl border border-dashed border-white/10 py-8 text-center text-sm text-slate-500">Todavía no hay movimientos registrados.</p>
                    @endforelse
                </div>
            </section>

        </div>

        @if(config('features.demo_deposits.enabled') && $usuario->is_demo)
        <x-ui.modal name="depositOpen" title="Añadir saldo demo" max-width="max-w-md">
                <p class="text-xs font-black uppercase tracking-wider text-amber-300">Entorno de demostración</p>
                <p class="mt-2 text-sm text-slate-400">Este movimiento no representa un pago real y quedará registrado en tu cartera.</p>
                <x-ui.money-input x-model.number="amount" min="1" max="1000" class="mt-5" label="Importe demo" />
                <div class="mt-3 grid grid-cols-4 gap-2"><template x-for="value in [10,25,50,100]"><button @click="amount=value" class="rounded-lg bg-white/5 py-2 text-xs hover:bg-white/10" x-text="value+' EUR Demo'"></button></template></div>
                <p x-show="error" class="mt-3 text-sm text-red-300" x-text="error"></p>
                <p x-show="success" class="mt-3 text-sm text-emerald-300" x-text="success"></p>
                <div class="mt-5 flex gap-2"><button @click="depositOpen=false" :disabled="busy" class="flex-1 rounded-xl border border-white/10 py-3 text-sm text-slate-400">Cancelar</button><button @click="deposit" :disabled="busy||amount<1" class="flex-1 rounded-xl bg-emerald-500 py-3 font-bold text-slate-950 disabled:opacity-50" x-text="busy?'Procesando…':'Confirmar demo'"></button></div>
        </x-ui.modal>
        @endif
        @if(config('features.economy.enabled') && $usuario->is_demo)
        <x-ui.modal name="withdrawalOpen" title="Solicitar retirada demo" max-width="max-w-md">
            <p class="text-xs font-black uppercase tracking-wider text-amber-300">Solo simulación</p>
            <p class="mt-2 text-sm text-slate-400">El importe se reserva en la tesorería demo. No se envía a ningún banco ni tiene valor monetario.</p>
            <x-ui.money-input x-model.number="withdrawalAmount" min="1" max="5000" class="mt-5" label="Importe EUR Demo" />
            <label class="mt-4 block text-xs font-bold text-slate-400">Método ficticio<select x-model="withdrawalMethod" class="mt-1 min-h-11 w-full rounded-xl border border-white/10 bg-black/25 px-3 text-white"><option value="demo_wallet">Monedero demo</option><option value="demo_card">Tarjeta demo</option><option value="demo_transfer">Transferencia demo</option></select></label>
            <p x-show="withdrawalError" class="mt-3 text-sm text-red-300" x-text="withdrawalError"></p>
            <p x-show="withdrawalSuccess" class="mt-3 text-sm text-emerald-300" x-text="withdrawalSuccess"></p>
            <div class="mt-5 flex gap-2"><button @click="withdrawalOpen=false" :disabled="withdrawalBusy" class="flex-1 rounded-xl border border-white/10 py-3 text-sm text-slate-400">Cancelar</button><button @click="requestWithdrawal" :disabled="withdrawalBusy||withdrawalAmount<1" class="flex-1 rounded-xl bg-amber-300 py-3 font-bold text-slate-950 disabled:opacity-50" x-text="withdrawalBusy?'Reservando…':'Solicitar demo'"></button></div>
        </x-ui.modal>
        @endif
    </div>

@push('scripts')
<script>
function profileWallet(){return{depositOpen:false,withdrawalOpen:false,amount:50,withdrawalAmount:10,withdrawalMethod:'demo_wallet',busy:false,withdrawalBusy:false,error:'',success:'',withdrawalError:'',withdrawalSuccess:'',lastMovement:null,requestToken:window.lootraRequestToken(),withdrawalToken:window.lootraRequestToken(),money(value){return `${Number(value||0).toFixed(2)} EUR Demo`},async deposit(){if(this.busy)return;this.busy=true;this.error='';this.success='';try{const response=await fetch(@js(route('wallet.demo-deposit')),{method:'POST',headers:{'Content-Type':'application/json','Accept':'application/json','X-CSRF-TOKEN':document.querySelector('meta[name="csrf-token"]').content},body:JSON.stringify({amount:this.amount,request_token:this.requestToken})});const data=await response.json();if(!response.ok)throw new Error(data.message||data.error||'No se pudo completar el depósito demo.');this.requestToken=window.lootraRequestToken();this.$store.wallet.saldo=Number(data.saldo);this.lastMovement=data.movement;this.success='Saldo demo añadido correctamente.';window.dispatchEvent(new CustomEvent('saldo-updated',{detail:{saldo:Number(data.saldo)}}));setTimeout(()=>{this.depositOpen=false;this.success=''},900)}catch(error){this.error=error.message}finally{this.busy=false}},async requestWithdrawal(){if(this.withdrawalBusy)return;this.withdrawalBusy=true;this.withdrawalError='';this.withdrawalSuccess='';try{const response=await fetch(@js(route('wallet.demo-withdrawals.store')),{method:'POST',headers:{'Content-Type':'application/json','Accept':'application/json','X-CSRF-TOKEN':document.querySelector('meta[name="csrf-token"]').content},body:JSON.stringify({amount:this.withdrawalAmount,method_key:this.withdrawalMethod,request_token:this.withdrawalToken})});const data=await response.json();if(!response.ok)throw new Error(data.message||'No se pudo solicitar la retirada demo.');this.withdrawalToken=window.lootraRequestToken();this.$store.wallet.saldo=Number(this.$store.wallet.saldo)-Number(this.withdrawalAmount);this.withdrawalSuccess='Retirada demo reservada para revisión.';setTimeout(()=>{this.withdrawalOpen=false;this.withdrawalSuccess=''},1200)}catch(error){this.withdrawalError=error.message}finally{this.withdrawalBusy=false}}}}
</script>
@endpush
@endsection
