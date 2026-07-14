@extends('layouts.app')

@section('title', 'Apuestas Deportivas — Lootra Casino')

@section('styles')
<style>
    .live-pulse { animation: livePulse 2s ease-in-out infinite; }
    @keyframes livePulse { 0%, 100% { opacity: 1; } 50% { opacity: 0.5; } }
    .odd-btn { transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1); }
    .odd-btn:hover { transform: translateY(-2px); }
    .odd-btn.selected { background: rgba(16, 185, 129, 0.2); border-color: rgba(16, 185, 129, 0.5); }
    .match-card { transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1); }
    .match-card:hover { transform: translateY(-3px); box-shadow: 0 12px 40px -8px rgba(0,0,0,0.4); }
    .score-flash { animation: scoreFlash 0.5s ease; }
    @keyframes scoreFlash { 0%, 100% { color: white; } 50% { color: #10b981; text-shadow: 0 0 20px rgba(16,185,129,0.5); } }
    .hero-sports { background: linear-gradient(135deg, #064e3b 0%, #0f172a 50%, #0d0d18 100%); }
</style>
@endsection

@section('contenido')

<div class="max-w-[1400px] mx-auto px-4 sm:px-6 lg:px-8 py-6 sm:py-10"
     x-data="{
         sport: 'futbol',
         betSlip: [],
         get betCount() { return this.betSlip.length; },
         get totalOdds() { return this.betSlip.length ? this.betSlip.reduce((a, b) => a * b.odd, 1).toFixed(2) : '0.00'; },
         toggleBet(match, type, odd) {
             const idx = this.betSlip.findIndex(b => b.match === match && b.type === type);
             if (idx > -1) this.betSlip.splice(idx, 1);
             else this.betSlip.push({ match, type, odd: parseFloat(odd) });
         },
         isSelected(match, type) { return this.betSlip.some(b => b.match === match && b.type === type); }
     }">

    {{-- HERO --}}
    <div class="hero-sports rounded-3xl overflow-hidden mb-10 relative">
        <img src="https://images.unsplash.com/photo-1431324155629-1a6deb1dec8d?w=1400&h=500&fit=crop" alt="Sports" class="absolute inset-0 w-full h-full object-cover opacity-30" loading="lazy">
        <div class="absolute inset-0 bg-gradient-to-r from-emerald-900/80 via-slate-900/60 to-transparent"></div>
        <div class="relative z-10 px-6 sm:px-10 py-12 sm:py-16">
            <div class="flex items-center gap-2 mb-4">
                <span class="w-2.5 h-2.5 rounded-full bg-red-500 live-pulse"></span>
                <span class="text-red-400 text-sm font-bold uppercase tracking-wider">En vivo</span>
            </div>
            <h1 class="text-3xl sm:text-5xl font-extrabold text-white mb-3">Apuestas Deportivas</h1>
            <p class="text-slate-400 text-lg max-w-xl mb-6">Apuesta en los mejores eventos deportivos del mundo. Odds competitivas, resultados en tiempo real.</p>
            <div class="flex flex-wrap gap-3">
                <div class="flex items-center gap-2 px-4 py-2 rounded-xl bg-white/10 backdrop-blur-sm border border-white/10">
                    <span class="text-2xl font-extrabold text-white">1,247</span>
                    <span class="text-xs text-slate-400">Eventos<br>disponibles</span>
                </div>
                <div class="flex items-center gap-2 px-4 py-2 rounded-xl bg-white/10 backdrop-blur-sm border border-white/10">
                    <span class="text-2xl font-extrabold text-emerald-400">156</span>
                    <span class="text-xs text-slate-400">En vivo<br>ahora</span>
                </div>
                <div class="flex items-center gap-2 px-4 py-2 rounded-xl bg-white/10 backdrop-blur-sm border border-white/10">
                    <span class="text-2xl font-extrabold text-amber-400">x9.85</span>
                    <span class="text-xs text-slate-400">Boost<br>del dia</span>
                </div>
            </div>
        </div>
    </div>

    <div class="flex flex-col lg:flex-row gap-8">

        {{-- MAIN CONTENT --}}
        <div class="flex-1 min-w-0">

            {{-- Sport Tabs --}}
            <div class="flex gap-2 mb-8 overflow-x-auto pb-2">
                @foreach([
                    'futbol' => ['icon' => '&#x26BD;', 'label' => 'Futbol', 'events' => 487],
                    'baloncesto' => ['icon' => '&#x1F3C0;', 'label' => 'Baloncesto', 'events' => 234],
                    'tenis' => ['icon' => '&#x1F3BE;', 'label' => 'Tenis', 'events' => 189],
                    'mma' => ['icon' => '&#x1F94A;', 'label' => 'MMA', 'events' => 42],
                    'esports' => ['icon' => '&#x1F3AE;', 'label' => 'eSports', 'events' => 156],
                    'formula1' => ['icon' => '&#x1F3CE;', 'label' => 'F1', 'events' => 28],
                ] as $id => $s)
                    <button @click="sport = '{{ $id }}'"
                        :class="sport === '{{ $id }}' ? 'bg-emerald-500/15 border-emerald-500/40 text-emerald-400 shadow-lg shadow-emerald-500/10' : 'bg-white/5 border-white/5 text-slate-400 hover:text-white hover:bg-white/8'"
                        class="flex items-center gap-2 px-4 py-2.5 rounded-xl text-sm font-semibold border transition-all whitespace-nowrap">
                        <span class="text-base">{!! $s['icon'] !!}</span>
                        {{ $s['label'] }}
                        <span class="text-xs opacity-50">{{ $s['events'] }}</span>
                    </button>
                @endforeach
            </div>

            {{-- DESTACADO --}}
            <div class="mb-8 rounded-2xl overflow-hidden match-card border border-emerald-500/20 relative">
                <img src="https://images.unsplash.com/photo-1522778119026-d647f0596c20?w=1200&h=500&fit=crop" alt="El Clasico" class="absolute inset-0 w-full h-full object-cover" loading="lazy">
                <div class="absolute inset-0 bg-gradient-to-r from-black/90 via-black/70 to-black/40"></div>
                <div class="relative z-10 p-6 sm:p-8">
                    <div class="flex items-center gap-2 mb-4">
                        <span class="px-3 py-1 rounded-lg bg-emerald-500/20 border border-emerald-500/30 text-emerald-400 text-xs font-bold uppercase tracking-wider">En vivo</span>
                        <span class="px-3 py-1 rounded-lg bg-red-500/20 border border-red-500/30 text-red-400 text-xs font-bold">78'</span>
                        <span class="px-3 py-1 rounded-lg bg-amber-500/20 border border-amber-500/30 text-amber-400 text-xs font-bold">&#x26A1; Maxima prioridad</span>
                    </div>
                    <div class="flex items-center justify-between mb-6">
                        <div class="flex items-center gap-3">
                            <div class="w-12 h-12 sm:w-14 sm:h-14 rounded-full bg-white/10 flex items-center justify-center text-2xl sm:text-3xl font-extrabold text-white border border-white/10">RM</div>
                            <div>
                                <p class="text-lg sm:text-xl font-bold text-white">Real Madrid</p>
                                <p class="text-sm text-slate-400">La Liga</p>
                            </div>
                        </div>
                        <div class="text-center">
                            <span class="text-3xl sm:text-4xl font-extrabold text-white score-flash">2 - 1</span>
                        </div>
                        <div class="flex items-center gap-3">
                            <div>
                                <p class="text-lg sm:text-xl font-bold text-white text-right">FC Barcelona</p>
                                <p class="text-sm text-slate-400 text-right">La Liga</p>
                            </div>
                            <div class="w-12 h-12 sm:w-14 sm:h-14 rounded-full bg-white/10 flex items-center justify-center text-2xl sm:text-3xl font-extrabold text-white border border-white/10">FCB</div>
                        </div>
                    </div>
                    <div class="grid grid-cols-3 gap-3">
                        <button @click="toggleBet('RM vs FCB', '1', '2.10')"
                            :class="isSelected('RM vs FCB', '1') ? 'selected' : ''"
                            class="odd-btn py-3 rounded-xl bg-white/5 border border-white/10 text-center">
                            <p class="text-xs text-slate-500 mb-0.5">1</p>
                            <p class="text-lg font-extrabold text-white">2.10</p>
                        </button>
                        <button @click="toggleBet('RM vs FCB', 'X', '3.40')"
                            :class="isSelected('RM vs FCB', 'X') ? 'selected' : ''"
                            class="odd-btn py-3 rounded-xl bg-white/5 border border-white/10 text-center">
                            <p class="text-xs text-slate-500 mb-0.5">X</p>
                            <p class="text-lg font-extrabold text-white">3.40</p>
                        </button>
                        <button @click="toggleBet('RM vs FCB', '2', '3.80')"
                            :class="isSelected('RM vs FCB', '2') ? 'selected' : ''"
                            class="odd-btn py-3 rounded-xl bg-white/5 border border-white/10 text-center">
                            <p class="text-xs text-slate-500 mb-0.5">2</p>
                            <p class="text-lg font-extrabold text-white">3.80</p>
                        </button>
                    </div>
                </div>
            </div>

            {{-- EN VIVO --}}
            <div class="mb-10">
                <div class="flex items-center gap-2 mb-5">
                    <span class="w-2.5 h-2.5 rounded-full bg-red-500 live-pulse"></span>
                    <h2 class="text-xl font-bold text-white">En vivo ahora</h2>
                    <span class="text-sm text-slate-500 font-medium">156 eventos</span>
                </div>
                <div class="grid sm:grid-cols-2 gap-4">
                    @php
                        $live = [
                            ['home' => 'Man City', 'away' => 'Liverpool', 'league' => 'Premier League', 'minute' => "34'", 'score' => '1 - 1', 'odds' => ['1.85', '3.60', '4.20'], 'img' => 'https://images.unsplash.com/photo-1522778119026-d647f0596c20?w=400&h=250&fit=crop', 'homeShort' => 'MC', 'awayShort' => 'LIV'],
                            ['home' => 'Bayern Munich', 'away' => 'Dortmund', 'league' => 'Bundesliga', 'minute' => "78'", 'score' => '3 - 0', 'odds' => ['1.50', '4.50', '5.80'], 'img' => 'https://images.unsplash.com/photo-1508098682722-e99c43a406b2?w=400&h=250&fit=crop', 'homeShort' => 'BAY', 'awayShort' => 'BVB'],
                            ['home' => 'PSG', 'away' => 'Marseille', 'league' => 'Ligue 1', 'minute' => "52'", 'score' => '2 - 0', 'odds' => ['1.30', '5.50', '8.00'], 'img' => 'https://images.unsplash.com/photo-1459865264687-595d652de67e?w=400&h=250&fit=crop', 'homeShort' => 'PSG', 'awayShort' => 'OM'],
                            ['home' => 'NBA: Lakers', 'away' => 'Celtics', 'league' => 'NBA', 'minute' => 'Q3 8:42', 'score' => '87 - 91', 'odds' => ['2.20', '-', '1.70'], 'img' => 'https://images.unsplash.com/photo-1546519638-68e109498ffc?w=400&h=250&fit=crop', 'homeShort' => 'LAL', 'awayShort' => 'BOS'],
                            ['home' => 'Djokovic', 'away' => 'Alcaraz', 'league' => 'Roland Garros', 'minute' => 'Set 2', 'score' => '6-4, 3-5', 'odds' => ['1.80', '-', '2.00'], 'img' => 'https://images.unsplash.com/photo-1554068865-24cecd4e34b8?w=400&h=250&fit=crop', 'homeShort' => 'DJO', 'awayShort' => 'ALC'],
                            ['home' => 'Fnatic', 'away' => 'G2', 'league' => 'LEC', 'minute' => "28'", 'score' => '14 - 8', 'odds' => ['2.50', '-', '1.55'], 'img' => 'https://images.unsplash.com/photo-1542751371-adc38448a05e?w=400&h=250&fit=crop', 'homeShort' => 'FNC', 'awayShort' => 'G2'],
                        ];
                    @endphp
                    @foreach($live as $m)
                        <div class="match-card rounded-2xl overflow-hidden bg-white/[0.03] border border-white/5">
                            <div class="relative h-32 sm:h-36">
                                <img src="{{ $m['img'] }}" alt="" class="w-full h-full object-cover" loading="lazy">
                                <div class="absolute inset-0 bg-gradient-to-t from-black/90 via-black/40 to-transparent"></div>
                                <div class="absolute top-3 left-3 flex items-center gap-2">
                                    <span class="flex items-center gap-1.5 px-2 py-0.5 rounded-md bg-red-500/90 text-white text-xs font-bold">
                                        <span class="w-1.5 h-1.5 rounded-full bg-white live-pulse"></span>
                                        {{ $m['minute'] }}
                                    </span>
                                    <span class="px-2 py-0.5 rounded-md bg-black/50 backdrop-blur-sm text-xs text-slate-300 font-medium">{{ $m['league'] }}</span>
                                </div>
                                <div class="absolute bottom-3 left-3 right-3 flex items-center justify-between">
                                    <div class="flex items-center gap-2">
                                        <div class="w-8 h-8 rounded-full bg-white/20 flex items-center justify-center text-xs font-bold text-white">{{ $m['homeShort'] }}</div>
                                        <span class="text-sm font-bold text-white">{{ $m['home'] }}</span>
                                    </div>
                                    <span class="text-lg font-extrabold text-white px-3 py-1 rounded-lg bg-black/40 backdrop-blur-sm">{{ $m['score'] }}</span>
                                    <div class="flex items-center gap-2">
                                        <span class="text-sm font-bold text-white">{{ $m['away'] }}</span>
                                        <div class="w-8 h-8 rounded-full bg-white/20 flex items-center justify-center text-xs font-bold text-white">{{ $m['awayShort'] }}</div>
                                    </div>
                                </div>
                            </div>
                            <div class="p-3 grid grid-cols-3 gap-2">
                                @foreach($m['odds'] as $i => $odd)
                                    <button @click="toggleBet('{{ $m["home"] }} vs {{ $m["away"] }}', '{{ ["1","X","2"][$i] }}', '{{ $odd }}')"
                                        :class="isSelected('{{ $m["home"] }} vs {{ $m["away"] }}', '{{ ["1","X","2"][$i] }}') ? 'selected' : ''"
                                        class="odd-btn py-2.5 rounded-lg bg-white/5 border border-white/5 hover:border-emerald-500/30 hover:bg-emerald-500/5 text-center transition">
                                        <p class="text-[10px] text-slate-500 mb-0.5">{{ ['1', 'X', '2'][$i] }}</p>
                                        <p class="text-sm font-bold text-white">{{ $odd }}</p>
                                    </button>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- PROXIMOS EVENTOS --}}
            <div class="mb-10">
                <div class="flex items-center justify-between mb-5">
                    <h2 class="text-xl font-bold text-white">Proximos eventos</h2>
                    <a href="#" class="text-sm text-emerald-400 hover:text-emerald-300 font-medium transition">Ver todos &rarr;</a>
                </div>
                <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-4">
                    @php
                        $proximos = [
                            ['home' => 'Atletico Madrid', 'away' => 'Sevilla', 'league' => 'La Liga', 'date' => '15 Jul, 21:00', 'odds' => ['1.70', '3.50', '5.00'], 'homeShort' => 'ATM', 'awayShort' => 'SEV', 'img' => 'https://images.unsplash.com/photo-1489944440615-453fc2b6a9a9?w=400&h=250&fit=crop'],
                            ['home' => 'Arsenal', 'away' => 'Chelsea', 'league' => 'Premier League', 'date' => '16 Jul, 17:30', 'odds' => ['2.20', '3.30', '3.10'], 'homeShort' => 'ARS', 'awayShort' => 'CHE', 'img' => 'https://images.unsplash.com/photo-1522778119026-d647f0596c20?w=400&h=250&fit=crop'],
                            ['home' => 'Juventus', 'away' => 'AC Milan', 'league' => 'Serie A', 'date' => '17 Jul, 20:45', 'odds' => ['2.50', '3.20', '2.80'], 'homeShort' => 'JUV', 'awayShort' => 'MIL', 'img' => 'https://images.unsplash.com/photo-1508098682722-e99c43a406b2?w=400&h=250&fit=crop'],
                            ['home' => 'Barcelona', 'away' => 'Atletico Madrid', 'league' => 'La Liga', 'date' => '20 Jul, 21:00', 'odds' => ['1.90', '3.40', '4.00'], 'homeShort' => 'BAR', 'awayShort' => 'ATM', 'img' => 'https://images.unsplash.com/photo-1459865264687-595d652de67e?w=400&h=250&fit=crop'],
                            ['home' => 'Nadal', 'away' => 'Sinner', 'league' => 'Wimbledon', 'date' => '18 Jul, 14:00', 'odds' => ['2.80', '-', '1.45'], 'homeShort' => 'NAD', 'awayShort' => 'SIN', 'img' => 'https://images.unsplash.com/photo-1554068865-24cecd4e34b8?w=400&h=250&fit=crop'],
                            ['home' => 'T1', 'away' => 'JDG', 'league' => 'Worlds 2026', 'date' => '19 Jul, 10:00', 'odds' => ['1.65', '-', '2.25'], 'homeShort' => 'T1', 'awayShort' => 'JDG', 'img' => 'https://images.unsplash.com/photo-1542751371-adc38448a05e?w=400&h=250&fit=crop'],
                        ];
                    @endphp
                    @foreach($proximos as $ev)
                        <div class="match-card rounded-2xl overflow-hidden bg-white/[0.03] border border-white/5">
                            <div class="relative h-28">
                                <img src="{{ $ev['img'] }}" alt="" class="w-full h-full object-cover" loading="lazy">
                                <div class="absolute inset-0 bg-gradient-to-t from-black/90 via-black/50 to-transparent"></div>
                                <div class="absolute top-3 left-3 flex items-center gap-2">
                                    <span class="px-2 py-0.5 rounded-md bg-white/10 backdrop-blur-sm text-xs text-slate-300 font-medium">{{ $ev['league'] }}</span>
                                </div>
                                <div class="absolute bottom-3 left-3 right-3 flex items-center justify-between">
                                    <div class="flex items-center gap-2">
                                        <div class="w-7 h-7 rounded-full bg-white/20 flex items-center justify-center text-[10px] font-bold text-white">{{ $ev['homeShort'] }}</div>
                                        <span class="text-sm font-bold text-white">{{ $ev['home'] }}</span>
                                    </div>
                                    <span class="text-xs text-slate-400 font-medium">VS</span>
                                    <div class="flex items-center gap-2">
                                        <span class="text-sm font-bold text-white">{{ $ev['away'] }}</span>
                                        <div class="w-7 h-7 rounded-full bg-white/20 flex items-center justify-center text-[10px] font-bold text-white">{{ $ev['awayShort'] }}</div>
                                    </div>
                                </div>
                            </div>
                            <div class="px-3 py-2 flex items-center justify-between">
                                <span class="text-xs text-slate-500">{{ $ev['date'] }}</span>
                                <div class="flex gap-1.5">
                                    @foreach($ev['odds'] as $i => $odd)
                                        <button @click="toggleBet('{{ $ev["home"] }} vs {{ $ev["away"] }}', '{{ ["1","X","2"][$i] }}', '{{ $odd }}')"
                                            :class="isSelected('{{ $ev["home"] }} vs {{ $ev["away"] }}', '{{ ["1","X","2"][$i] }}') ? 'selected' : ''"
                                            class="odd-btn px-3 py-1.5 rounded-lg bg-white/5 border border-white/5 hover:border-emerald-500/30 text-center transition">
                                            <p class="text-[9px] text-slate-500">{{ ["1","X","2"][$i] }}</p>
                                            <p class="text-xs font-bold text-white">{{ $odd }}</p>
                                        </button>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- RESULTADOS RECIENTES --}}
            <div>
                <h2 class="text-xl font-bold text-white mb-5">Resultados recientes</h2>
                <div class="rounded-2xl bg-white/[0.03] border border-white/5 overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="w-full min-w-[600px]">
                            <thead>
                                <tr class="border-b border-white/5">
                                    <th class="px-5 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Partido</th>
                                    <th class="px-5 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Liga</th>
                                    <th class="px-5 py-3 text-center text-xs font-semibold text-slate-500 uppercase tracking-wider">Resultado</th>
                                    <th class="px-5 py-3 text-center text-xs font-semibold text-slate-500 uppercase tracking-wider">1</th>
                                    <th class="px-5 py-3 text-center text-xs font-semibold text-slate-500 uppercase tracking-wider">X</th>
                                    <th class="px-5 py-3 text-center text-xs font-semibold text-slate-500 uppercase tracking-wider">2</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-white/5">
                                @php
                                    $results = [
                                        ['home' => 'Real Sociedad', 'away' => 'Athletic', 'league' => 'La Liga', 'score' => '2 - 0', 'odds' => ['2.10', '3.20', '3.50']],
                                        ['home' => 'Tottenham', 'away' => 'Man Utd', 'league' => 'Premier League', 'score' => '1 - 3', 'odds' => ['2.80', '3.40', '2.40']],
                                        ['home' => 'Inter', 'away' => 'Napoli', 'league' => 'Serie A', 'score' => '0 - 0', 'odds' => ['2.20', '3.00', '3.30']],
                                        ['home' => 'Djokovic', 'away' => 'Rublev', 'league' => 'ATP Masters', 'score' => '2 - 1', 'odds' => ['1.25', '-', '4.00']],
                                    ];
                                @endphp
                                @foreach($results as $r)
                                    <tr class="hover:bg-white/[0.02] transition">
                                        <td class="px-5 py-3.5">
                                            <p class="text-sm font-semibold text-white">{{ $r['home'] }} vs {{ $r['away'] }}</p>
                                        </td>
                                        <td class="px-5 py-3.5 text-sm text-slate-400">{{ $r['league'] }}</td>
                                        <td class="px-5 py-3.5 text-center">
                                            <span class="px-3 py-1 rounded-lg bg-white/5 text-sm font-bold text-white">{{ $r['score'] }}</span>
                                        </td>
                                        @foreach($r['odds'] as $odd)
                                            <td class="px-5 py-3.5 text-center">
                                                <span class="text-sm font-semibold text-slate-500">{{ $odd }}</span>
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

        {{-- SIDEBAR: BET SLIP --}}
        <aside class="w-full lg:w-80 shrink-0">
            <div class="lg:sticky lg:top-20 space-y-6">

                {{-- Bet Slip --}}
                <div class="rounded-2xl bg-white/[0.03] border border-white/5 overflow-hidden">
                    <div class="flex items-center justify-between px-5 py-4 border-b border-white/5">
                        <div class="flex items-center gap-2">
                            <span class="text-sm font-bold text-white">Mi apuesta</span>
                            <template x-if="betCount > 0">
                                <span class="w-5 h-5 rounded-full bg-emerald-500 text-black text-xs font-bold flex items-center justify-center" x-text="betCount"></span>
                            </template>
                        </div>
                        <button @click="betSlip = []" x-show="betCount > 0" class="text-xs text-slate-500 hover:text-red-400 transition">Limpiar</button>
                    </div>

                    <div class="p-5">
                        <template x-if="betCount === 0">
                            <div class="text-center py-6">
                                <div class="text-3xl mb-2">&#x1F4B0;</div>
                                <p class="text-sm text-slate-500">Selecciona una apuesta para anadirla aqui.</p>
                            </div>
                        </template>
                        <template x-if="betCount > 0">
                            <div>
                                <div class="space-y-2 mb-4">
                                    <template x-for="(bet, i) in betSlip" :key="i">
                                        <div class="flex items-center justify-between p-3 rounded-xl bg-white/[0.03] border border-white/5">
                                            <div>
                                                <p class="text-xs text-slate-400" x-text="bet.match"></p>
                                                <p class="text-sm font-bold text-white" x-text="bet.type + ' @ ' + bet.odd"></p>
                                            </div>
                                            <button @click="betSlip.splice(i, 1)" class="text-slate-500 hover:text-red-400 transition text-xs">X</button>
                                        </div>
                                    </template>
                                </div>
                                <div class="flex justify-between items-center mb-4 pt-3 border-t border-white/5">
                                    <span class="text-sm text-slate-400">Cuota total</span>
                                    <span class="text-lg font-extrabold text-emerald-400" x-text="totalOdds"></span>
                                </div>
                                <div class="space-y-2">
                                    <input type="number" placeholder="Importe (EUR)" min="1" class="w-full px-4 py-3 rounded-xl bg-white/5 border border-white/10 text-white placeholder-slate-500 text-sm outline-none focus:border-emerald-500 transition">
                                    <button class="w-full py-3 rounded-xl bg-emerald-500 hover:bg-emerald-400 text-black font-bold text-sm transition shadow-lg shadow-emerald-500/20">
                                        Colocar apuesta
                                    </button>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>

                {{-- Boost del dia --}}
                <div class="rounded-2xl bg-gradient-to-br from-amber-500/10 to-orange-500/5 border border-amber-500/20 p-5">
                    <div class="flex items-center gap-2 mb-3">
                        <span class="text-xl">&#x26A1;</span>
                        <h3 class="text-sm font-bold text-white">Boost del dia</h3>
                    </div>
                    <div class="p-3 rounded-xl bg-black/20 border border-amber-500/10 mb-3">
                        <p class="text-xs text-slate-400 mb-1">Real Madrid vs Barcelona + Lakers vs Celtics</p>
                        <div class="flex items-center gap-2">
                            <span class="text-xs text-slate-500 line-through">5.46</span>
                            <span class="text-xl font-extrabold text-amber-400">9.85</span>
                        </div>
                    </div>
                    <button class="w-full py-2.5 rounded-xl bg-amber-500/20 hover:bg-amber-500/30 border border-amber-500/30 text-amber-400 text-sm font-bold transition">
                        Apostar con boost
                    </button>
                </div>

                {{-- Stats rapidas --}}
                <div class="rounded-2xl bg-white/[0.03] border border-white/5 p-5">
                    <h3 class="text-sm font-bold text-white uppercase tracking-wider mb-4">Estadisticas</h3>
                    <div class="space-y-3">
                        <div class="flex justify-between items-center">
                            <span class="text-sm text-slate-500">Eventos hoy</span>
                            <span class="text-sm font-bold text-white">342</span>
                        </div>
                        <div class="h-px bg-white/5"></div>
                        <div class="flex justify-between items-center">
                            <span class="text-sm text-slate-500">En vivo</span>
                            <span class="text-sm font-bold text-emerald-400">156</span>
                        </div>
                        <div class="h-px bg-white/5"></div>
                        <div class="flex justify-between items-center">
                            <span class="text-sm text-slate-500">Mejor cuota</span>
                            <span class="text-sm font-bold text-amber-400">x51.00</span>
                        </div>
                        <div class="h-px bg-white/5"></div>
                        <div class="flex justify-between items-center">
                            <span class="text-sm text-slate-500">Depositos totales</span>
                            <span class="text-sm font-bold text-white">€124,890</span>
                        </div>
                    </div>
                </div>

            </div>
        </aside>

    </div>
</div>

@endsection
