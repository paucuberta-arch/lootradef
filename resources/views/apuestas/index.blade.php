@extends('layouts.app')

@section('title', 'Apuestas Deportivas — Lootra Casino')

@section('contenido')

<div class="max-w-[1400px] mx-auto px-4 sm:px-6 lg:px-8 py-6 sm:py-10">

    {{-- Header --}}
    <div class="mb-8">
        <div class="flex items-center gap-3 mb-3">
            <div class="w-10 h-10 rounded-xl bg-emerald-500/10 flex items-center justify-center text-xl">&#x26BD;</div>
            <h1 class="text-2xl sm:text-3xl font-extrabold text-white">Apuestas Deportivas</h1>
        </div>
        <p class="text-slate-500">Apuesta en vivo en los mejores eventos deportivos del mundo.</p>
    </div>

    {{-- Deportes tabs --}}
    <div class="flex gap-2 mb-8 overflow-x-auto pb-2" x-data="{ sport: 'futbol' }">
        @foreach(['futbol' => '&#x26BD; Futbol', 'baloncesto' => '&#x1F3C0; Baloncesto', 'tenis' => '&#x1F3BE; Tenis', 'mma' => '&#x1F94A; MMA', 'eSports' => '&#x1F3AE; eSports'] as $id => $label)
            <button @click="sport = '{{ $id }}'"
                :class="sport === '{{ $id }}' ? 'bg-emerald-500/10 border-emerald-500/30 text-emerald-400' : 'bg-white/5 border-white/5 text-slate-400 hover:text-white'"
                class="px-4 py-2 rounded-xl text-sm font-semibold border transition-all whitespace-nowrap">
                {!! $label !!}
            </button>
        @endforeach
    </div>

    {{-- Partidos en vivo --}}
    <div class="mb-10">
        <div class="flex items-center gap-2 mb-5">
            <span class="w-2 h-2 rounded-full bg-red-500 animate-pulse"></span>
            <h2 class="text-lg font-bold text-white">En vivo ahora</h2>
        </div>
        <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-4">
            @php
                $live = [
                    ['home' => 'Real Madrid', 'away' => 'Barcelona', 'league' => 'La Liga', 'minute' => "67'", 'odds' => ['2.10', '3.40', '3.80']],
                    ['home' => 'Man City', 'away' => 'Liverpool', 'league' => 'Premier League', 'minute' => "34'", 'odds' => ['1.85', '3.60', '4.20']],
                    ['home' => 'Bayern', 'away' => 'Dortmund', 'league' => 'Bundesliga', 'minute' => "78'", 'odds' => ['1.50', '4.50', '5.80']],
                ];
            @endphp
            @foreach($live as $match)
                <div class="rounded-2xl bg-white/[0.03] border border-white/5 p-5 hover:bg-white/[0.05] transition">
                    <div class="flex items-center justify-between mb-4">
                        <span class="text-xs font-medium text-slate-500">{{ $match['league'] }}</span>
                        <span class="flex items-center gap-1.5 px-2 py-0.5 rounded-md bg-red-500/10 text-red-400 text-xs font-bold">
                            <span class="w-1.5 h-1.5 rounded-full bg-red-500 animate-pulse"></span>
                            {{ $match['minute'] }}
                        </span>
                    </div>
                    <div class="space-y-2 mb-4">
                        <div class="flex items-center justify-between">
                            <span class="text-sm font-semibold text-white">{{ $match['home'] }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-sm font-semibold text-white">{{ $match['away'] }}</span>
                        </div>
                    </div>
                    <div class="grid grid-cols-3 gap-2">
                        @foreach($match['odds'] as $i => $odd)
                            <button class="py-2 rounded-lg bg-white/5 hover:bg-emerald-500/10 border border-white/5 hover:border-emerald-500/30 text-center transition">
                                <p class="text-xs text-slate-500 mb-0.5">{{ ['1', 'X', '2'][$i] }}</p>
                                <p class="text-sm font-bold text-white">{{ $odd }}</p>
                            </button>
                        @endforeach
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    {{-- Proximos partidos --}}
    <div>
        <h2 class="text-lg font-bold text-white mb-5">Proximos eventos</h2>
        <div class="rounded-2xl bg-white/[0.03] border border-white/5 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full min-w-[600px]">
                    <thead>
                        <tr class="border-b border-white/5">
                            <th class="px-5 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Evento</th>
                            <th class="px-5 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Liga</th>
                            <th class="px-5 py-3 text-center text-xs font-semibold text-slate-500 uppercase tracking-wider">Fecha</th>
                            <th class="px-5 py-3 text-center text-xs font-semibold text-slate-500 uppercase tracking-wider">1</th>
                            <th class="px-5 py-3 text-center text-xs font-semibold text-slate-500 uppercase tracking-wider">X</th>
                            <th class="px-5 py-3 text-center text-xs font-semibold text-slate-500 uppercase tracking-wider">2</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-white/5">
                        @php
                            $proximos = [
                                ['home' => 'Atletico Madrid', 'away' => 'Sevilla', 'league' => 'La Liga', 'date' => '15 Jul', 'odds' => ['1.70', '3.50', '5.00']],
                                ['home' => 'Arsenal', 'away' => 'Chelsea', 'league' => 'Premier League', 'date' => '16 Jul', 'odds' => ['2.20', '3.30', '3.10']],
                                ['home' => 'Juventus', 'away' => 'AC Milan', 'league' => 'Serie A', 'date' => '17 Jul', 'odds' => ['2.50', '3.20', '2.80']],
                                ['home' => 'PSG', 'away' => 'Marseille', 'league' => 'Ligue 1', 'date' => '17 Jul', 'odds' => ['1.40', '4.80', '7.00']],
                                ['home' => 'Barcelona', 'away' => 'Atletico Madrid', 'league' => 'La Liga', 'date' => '20 Jul', 'odds' => ['1.90', '3.40', '4.00']],
                            ];
                        @endphp
                        @foreach($proximos as $ev)
                            <tr class="hover:bg-white/[0.02] transition">
                                <td class="px-5 py-4">
                                    <p class="text-sm font-semibold text-white">{{ $ev['home'] }} vs {{ $ev['away'] }}</p>
                                </td>
                                <td class="px-5 py-4 text-sm text-slate-400">{{ $ev['league'] }}</td>
                                <td class="px-5 py-4 text-sm text-slate-400 text-center">{{ $ev['date'] }}</td>
                                @foreach($ev['odds'] as $odd)
                                    <td class="px-5 py-4 text-center">
                                        <button class="px-3 py-1.5 rounded-lg bg-white/5 hover:bg-emerald-500/10 border border-white/5 hover:border-emerald-500/30 text-sm font-bold text-white transition">{{ $odd }}</button>
                                    </td>
                                @endforeach
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>

@endsection
