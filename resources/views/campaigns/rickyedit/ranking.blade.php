@extends('layouts.app')
@section('title', 'Ranking — Reto RickyEdit')
@section('contenido')
<div class="mx-auto max-w-5xl px-4 py-10 sm:px-6">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between"><div><p class="text-xs font-black uppercase tracking-[.2em] text-fuchsia-300">RickyEdit x Lootra</p><h1 class="mt-2 text-4xl font-black">Ranking del reto</h1><p class="mt-2 text-slate-400">RickyEdit: <b class="text-white">{{ number_format($creatorScore) }} puntos</b></p></div>@if($challenge && $userPosition)<div class="rounded-xl border border-cyan-400/20 bg-cyan-400/10 p-4 text-sm">Tu posición: <b class="text-cyan-300">#{{ $userPosition }}</b></div>@endif</div>
    <div class="mt-8 overflow-hidden rounded-2xl border border-white/10 bg-white/[.03]">
        <div class="overflow-x-auto"><table class="w-full min-w-[680px] text-sm"><thead class="bg-white/5 text-xs uppercase tracking-wider text-slate-500"><tr><th class="px-4 py-3 text-left">Posición</th><th class="px-4 py-3 text-left">Alias</th><th class="px-4 py-3 text-right">Puntuación</th><th class="px-4 py-3 text-right">Saldo final</th><th class="px-4 py-3 text-right">Partidas</th></tr></thead><tbody>
        @forelse($leaders as $entry)<tr class="border-t border-white/5"><td class="px-4 py-3 font-black text-slate-500">#{{ $leaders->firstItem()+$loop->index }}</td><td class="px-4 py-3"><div class="flex items-center gap-3"><span class="grid h-9 w-9 place-items-center rounded-lg bg-gradient-to-br from-fuchsia-400 to-cyan-300 font-black text-slate-950">{{ mb_strtoupper(mb_substr($entry->public_alias,0,1)) }}</span><b>{{ $entry->public_alias }}</b></div></td><td class="px-4 py-3 text-right font-black text-fuchsia-300">{{ number_format($entry->score) }}</td><td class="px-4 py-3 text-right">{{ number_format($entry->final_balance,2,',','.') }}</td><td class="px-4 py-3 text-right text-slate-400">{{ $entry->games_played }}</td></tr>@empty<tr><td colspan="5" class="p-10 text-center text-slate-500">Todavía no hay resultados reales.</td></tr>@endforelse
        </tbody></table></div>
    </div>
    <div class="mt-5">{{ $leaders->links() }}</div>
</div>
@endsection
