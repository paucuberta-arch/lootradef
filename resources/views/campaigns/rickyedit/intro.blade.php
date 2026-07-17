@extends('layouts.app')
@section('title', 'Preparar el reto RickyEdit')
@section('contenido')
<div class="mx-auto max-w-3xl px-4 py-12 sm:px-6">
    <div class="rounded-3xl border border-fuchsia-400/20 bg-gradient-to-br from-fuchsia-500/10 to-cyan-500/5 p-6 sm:p-10">
        <p class="text-xs font-black uppercase tracking-[.22em] text-cyan-300">Antes de empezar</p>
        <h1 class="mt-3 text-4xl font-black">Tu único intento</h1>
        <p class="mt-4 text-lg text-slate-300">Recibirás {{ number_format($campaign['initial_balance']) }} créditos exclusivos del reto. El contador de {{ $campaign['duration_minutes'] }} minutos empieza al confirmar.</p>
        <ul class="mt-6 space-y-3 text-sm text-slate-300"><li>✓ El tiempo y los resultados se calculan en el servidor.</li><li>✓ El saldo está separado de tu cartera normal.</li><li>✓ No podrás reiniciar ni transferir créditos.</li><li>✓ Las cajas y apuestas deportivas no consumen saldo del reto.</li></ul>
        @if($challenge)
            <div class="mt-7 rounded-xl border border-white/10 bg-black/20 p-4"><p class="font-bold">Estado: {{ ucfirst($challenge->status) }}</p>@if($challenge->status === 'active')<a href="{{ route('games.index') }}" class="mt-3 inline-flex rounded-xl bg-white px-5 py-3 font-black text-slate-950">Continuar reto</a>@else<a href="{{ route('rickyedit.ranking') }}" class="mt-3 inline-flex rounded-xl bg-white px-5 py-3 font-black text-slate-950">Ver resultado</a>@endif</div>
        @else
            <form action="{{ route('rickyedit.start') }}" method="POST" class="mt-8">@csrf<button class="min-h-12 w-full rounded-xl bg-gradient-to-r from-fuchsia-400 to-cyan-300 px-6 py-3.5 font-black text-slate-950">Iniciar reto ahora</button></form>
        @endif
        <p class="mt-5 text-center text-xs text-amber-200">+18 · Créditos demo · Sin dinero real · Sin retiradas ni premios.</p>
    </div>
</div>
@endsection
