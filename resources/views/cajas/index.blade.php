@extends('layouts.app')

@section('title', 'Cajas de Azar — Lootra Casino')

@section('styles')
<style>
    .case-shine {
        background: linear-gradient(105deg, transparent 40%, rgba(255,255,255,0.05) 45%, rgba(255,255,255,0.1) 50%, rgba(255,255,255,0.05) 55%, transparent 60%);
        background-size: 200% 100%;
        animation: shine 3s ease-in-out infinite;
    }
    @keyframes shine { 0% { background-position: 200% 0; } 100% { background-position: -200% 0; } }
    .case-card { transition: all 0.4s cubic-bezier(0.16, 1, 0.3, 1); }
    .case-card:hover { transform: translateY(-8px) scale(1.02); }
    .rarity-common { border-color: rgba(148, 163, 184, 0.3); }
    .rarity-uncommon { border-color: rgba(59, 130, 246, 0.3); }
    .rarity-rare { border-color: rgba(168, 85, 247, 0.3); }
    .rarity-epic { border-color: rgba(245, 158, 11, 0.3); }
    .rarity-legendary { border-color: rgba(239, 68, 68, 0.3); }
    .glow-common { box-shadow: 0 0 30px -8px rgba(148, 163, 184, 0.15); }
    .glow-uncommon { box-shadow: 0 0 40px -8px rgba(59, 130, 246, 0.25); }
    .glow-rare { box-shadow: 0 0 50px -8px rgba(168, 85, 247, 0.3); }
    .glow-epic { box-shadow: 0 0 60px -8px rgba(245, 158, 11, 0.35); }
    .glow-legendary { box-shadow: 0 0 70px -8px rgba(239, 68, 68, 0.4); }
    .box-opening { animation: boxOpen 0.8s cubic-bezier(0.34, 1.56, 0.64, 1); }
    @keyframes boxOpen { 0% { transform: scale(1); } 40% { transform: scale(1.15) rotate(-3deg); } 70% { transform: scale(0.95) rotate(2deg); } 100% { transform: scale(1) rotate(0deg); } }
    .prize-reveal { animation: prizeReveal 0.6s cubic-bezier(0.16, 1, 0.3, 1) forwards; }
    @keyframes prizeReveal { 0% { opacity: 0; transform: translateY(20px) scale(0.9); } 100% { opacity: 1; transform: translateY(0) scale(1); } }
    .hero-cases { background: linear-gradient(135deg, #2e1065 0%, #1e1b4b 40%, #0f172a 70%, #0d0d18 100%); }
</style>
@endsection

@section('contenido')

<div class="max-w-[1400px] mx-auto px-4 sm:px-6 lg:px-8 py-6 sm:py-10"
     x-data="{
         selectedBox: null,
         opening: false,
         prize: null,
         filter: 'all',
         recentWins: [
             { user: 'Carlos M.', item: 'iPhone 16 Pro', box: 'Caja Diamond', rarity: 'legendary', time: 'hace 2 min' },
             { user: 'Ana R.', item: 'AirPods Pro', box: 'Caja Premium', rarity: 'rare', time: 'hace 5 min' },
             { user: 'Luis P.', item: 'Tarjeta regalo €10', box: 'Caja Especial', rarity: 'common', time: 'hace 8 min' },
             { user: 'Maria G.', item: 'Smartwatch', box: 'Caja Gold', rarity: 'epic', time: 'hace 12 min' },
             { user: 'Javi S.', item: 'Funda premium', box: 'Caja Starter', rarity: 'common', time: 'hace 15 min' },
             { user: 'Elena V.', item: 'MacBook Air', box: 'Caja Platinum', rarity: 'legendary', time: 'hace 18 min' },
             { user: 'Pablo D.', item: 'Mando gaming', box: 'Caja Gold', rarity: 'rare', time: 'hace 22 min' },
         ],
         openBox(caja) {
             if (this.opening) return;
             this.selectedBox = caja;
             this.opening = true;
             this.prize = null;
             setTimeout(() => {
                 const items = caja.allItems || caja.items;
                 this.prize = items[Math.floor(Math.random() * items.length)];
                 this.opening = false;
             }, 2000);
         }
     }">

    {{-- HERO --}}
    <div class="hero-cases rounded-3xl overflow-hidden mb-10 relative">
        <img src="https://images.unsplash.com/photo-1513542789411-b6a5d4f31634?w=1400&h=500&fit=crop" alt="Cajas" class="absolute inset-0 w-full h-full object-cover opacity-25" loading="lazy">
        <div class="absolute inset-0 bg-gradient-to-r from-purple-900/80 via-indigo-900/50 to-transparent"></div>
        <div class="relative z-10 px-6 sm:px-10 py-12 sm:py-16">
            <div class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full bg-purple-500/20 border border-purple-500/30 text-purple-300 text-sm font-medium mb-5">
                <span class="text-base">&#x1F4E6;</span> Productos reales garantizados
            </div>
            <h1 class="text-3xl sm:text-5xl font-extrabold text-white mb-3">Cajas de Azar</h1>
            <p class="text-slate-400 text-lg max-w-xl mb-6">Abre cajas y gana productos reales: electronica, ropa, tarjetas regalo y mucho mas. Canjea tus premios por dinero o pidelos a casa.</p>
            <div class="flex flex-wrap gap-3">
                <div class="flex items-center gap-2 px-4 py-2 rounded-xl bg-white/10 backdrop-blur-sm border border-white/10">
                    <span class="text-2xl font-extrabold text-white">8</span>
                    <span class="text-xs text-slate-400">Cajas<br>disponibles</span>
                </div>
                <div class="flex items-center gap-2 px-4 py-2 rounded-xl bg-white/10 backdrop-blur-sm border border-white/10">
                    <span class="text-2xl font-extrabold text-purple-400">24,891</span>
                    <span class="text-xs text-slate-400">Cajas<br>abiertas</span>
                </div>
                <div class="flex items-center gap-2 px-4 py-2 rounded-xl bg-white/10 backdrop-blur-sm border border-white/10">
                    <span class="text-2xl font-extrabold text-amber-400">€89,420</span>
                    <span class="text-xs text-slate-400">Premios<br>entregados</span>
                </div>
            </div>
        </div>
    </div>

    {{-- Leyenda de rareza --}}
    <div class="flex flex-wrap justify-center gap-4 sm:gap-6 mb-8">
        @foreach([
            ['color' => 'bg-slate-400', 'border' => 'border-slate-400', 'label' => 'Comun', 'pct' => '45%', 'desc' => '€1 - €5'],
            ['color' => 'bg-blue-500', 'border' => 'border-blue-500', 'label' => 'Poco comun', 'pct' => '30%', 'desc' => '€5 - €25'],
            ['color' => 'bg-purple-500', 'border' => 'border-purple-500', 'label' => 'Raro', 'pct' => '15%', 'desc' => '€25 - €100'],
            ['color' => 'bg-amber-500', 'border' => 'border-amber-500', 'label' => 'Epico', 'pct' => '8%', 'desc' => '€100 - €500'],
            ['color' => 'bg-red-500', 'border' => 'border-red-500', 'label' => 'Legendario', 'pct' => '2%', 'desc' => '€500+'],
        ] as $r)
            <div class="flex items-center gap-3 px-4 py-2 rounded-xl bg-white/[0.03] border border-white/5">
                <span class="w-4 h-4 rounded-full {{ $r['color'] }} shadow-lg" style="box-shadow: 0 0 12px {{ $r['color'] === 'bg-slate-400' ? 'rgba(148,163,184,0.3)' : $r['color'] === 'bg-blue-500' ? 'rgba(59,130,246,0.3)' : $r['color'] === 'bg-purple-500' ? 'rgba(168,85,247,0.3)' : $r['color'] === 'bg-amber-500' ? 'rgba(245,158,11,0.3)' : 'rgba(239,68,68,0.3)' }}"></span>
                <div>
                    <span class="text-sm font-semibold text-white">{{ $r['label'] }}</span>
                    <span class="text-xs text-slate-500 ml-1">({{ $r['pct'] }})</span>
                </div>
                <span class="text-xs text-slate-600">{{ $r['desc'] }}</span>
            </div>
        @endforeach
    </div>

    {{-- Filtro --}}
    <div class="flex gap-2 mb-8 overflow-x-auto pb-2">
        @foreach(['all' => '&#x1F4E6; Todas', 'low' => '&#x1F4B0; Hasta €5', 'mid' => '&#x1F4B0; €5 - €25', 'high' => '&#x1F4B0; €25+', 'special' => '&#x2B50; Especiales'] as $id => $label)
            <button @click="filter = '{{ $id }}'"
                :class="filter === '{{ $id }}' ? 'bg-purple-500/15 border-purple-500/40 text-purple-400 shadow-lg shadow-purple-500/10' : 'bg-white/5 border-white/5 text-slate-400 hover:text-white'"
                class="px-4 py-2 rounded-xl text-sm font-semibold border transition-all whitespace-nowrap">
                {!! $label !!}
            </button>
        @endforeach
    </div>

    {{-- GRID DE CAJAS --}}
    <div class="grid sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-5 mb-12">

        @php
            $cajas = [
                [
                    'id' => 1, 'name' => 'Caja Starter', 'price' => '€0.99', 'rarity' => 'common',
                    'glow' => 'glow-common', 'rarityClass' => 'rarity-common', 'accent' => 'slate-400',
                    'image' => 'https://images.unsplash.com/photo-1558618666-fcd25c85f82e?w=400&h=400&fit=crop',
                    'items' => ['Vinilo adhesivo', 'Llavero metalico', 'Sticker pack', 'Tarjeta regalo €1', 'Camiseta basica'],
                    'allItems' => ['Vinilo adhesivo', 'Llavero metalico', 'Sticker pack', 'Tarjeta regalo €1', 'Camiseta basica', 'Gorra basica', 'Taza basica'],
                    'featured' => false, 'tag' => '',
                ],
                [
                    'id' => 2, 'name' => 'Caja Basica', 'price' => '€2.99', 'rarity' => 'common',
                    'glow' => 'glow-uncommon', 'rarityClass' => 'rarity-uncommon', 'accent' => 'blue-400',
                    'image' => 'https://images.unsplash.com/photo-1505740420928-5e560c06d30e?w=400&h=400&fit=crop',
                    'items' => ['Auriculares Bluetooth', 'Funda de movil', 'Estuche de auriculares', 'Tarjeta regalo €5', 'Funda portatil'],
                    'allItems' => ['Auriculares Bluetooth', 'Funda de movil', 'Estuche de auriculares', 'Tarjeta regalo €5', 'Funda portatil', 'Cable USB premium', 'Soporte movil'],
                    'featured' => false, 'tag' => '',
                ],
                [
                    'id' => 3, 'name' => 'Caja Ruby', 'price' => '€14.99', 'rarity' => 'rare',
                    'glow' => 'glow-rare', 'rarityClass' => 'rarity-rare', 'accent' => 'purple-400',
                    'image' => 'https://images.unsplash.com/photo-1523275335684-37898b6baf30?w=400&h=400&fit=crop',
                    'items' => ['Zapatillas deportivas', 'Reloj clasico', 'Cartera de piel', 'Tarjeta regalo €25', 'Gafas de sol'],
                    'allItems' => ['Zapatillas deportivas', 'Reloj clasico', 'Cartera de piel', 'Tarjeta regalo €25', 'Gafas de sol', 'Bolso de piel', 'Cinturon premium'],
                    'featured' => false, 'tag' => '',
                ],
                [
                    'id' => 4, 'name' => 'Caja Premium', 'price' => '€9.99', 'rarity' => 'rare',
                    'glow' => 'glow-rare', 'rarityClass' => 'rarity-rare', 'accent' => 'purple-400',
                    'image' => 'https://images.unsplash.com/photo-1546868871-af0de0ae72be?w=400&h=400&fit=crop',
                    'items' => ['Smartwatch deportivo', 'Altavoz bluetooth', 'Cargador inalambrico', 'Tarjeta regalo €15', 'Funda premium'],
                    'allItems' => ['Smartwatch deportivo', 'Altavoz bluetooth', 'Cargador inalambrico', 'Tarjeta regalo €15', 'Funda premium', 'Funda iPad', 'Auriculares deportivos'],
                    'featured' => true, 'tag' => 'Popular',
                ],
                [
                    'id' => 5, 'name' => 'Caja Especial', 'price' => '€4.99', 'rarity' => 'uncommon',
                    'glow' => 'glow-uncommon', 'rarityClass' => 'rarity-uncommon', 'accent' => 'blue-400',
                    'image' => 'https://images.unsplash.com/photo-1593642702821-c8da6771f0c6?w=400&h=400&fit=crop',
                    'items' => ['Auriculares gaming', 'Teclado mecanico', 'Raton gaming', 'Tarjeta regalo €10', 'Alfombrilla XL'],
                    'allItems' => ['Auriculares gaming', 'Teclado mecanico', 'Raton gaming', 'Tarjeta regalo €10', 'Alfombrilla XL', 'Webcam HD', 'Parlador USB'],
                    'featured' => false, 'tag' => '',
                ],
                [
                    'id' => 6, 'name' => 'Caja Gold', 'price' => '€24.99', 'rarity' => 'epic',
                    'glow' => 'glow-epic', 'rarityClass' => 'rarity-epic', 'accent' => 'amber-400',
                    'image' => 'https://images.unsplash.com/photo-1511707171634-5f897ff02aa9?w=400&h=400&fit=crop',
                    'items' => ['Auriculares ANC', 'Tablet basica', 'Mando gaming', 'Tarjeta regalo €50', 'Smartwatch'],
                    'allItems' => ['Auriculares ANC', 'Tablet basica', 'Mando gaming', 'Tarjeta regalo €50', 'Smartwatch', 'Cargador inalambrico Pro', 'Altavoz JBL'],
                    'featured' => true, 'tag' => '&#x2B50; Mejor valor',
                ],
                [
                    'id' => 7, 'name' => 'Caja Diamond', 'price' => '€49.99', 'rarity' => 'legendary',
                    'glow' => 'glow-legendary', 'rarityClass' => 'rarity-legendary', 'accent' => 'red-400',
                    'image' => 'https://images.unsplash.com/photo-1517336714731-489689fd1ca8?w=400&h=400&fit=crop',
                    'items' => ['iPhone / Samsung', 'MacBook Air', 'PS5 / Xbox', 'Tarjeta regalo €200', 'iPad'],
                    'allItems' => ['iPhone / Samsung', 'MacBook Air', 'PS5 / Xbox', 'Tarjeta regalo €200', 'iPad', 'AirPods Max', 'GoPro Hero'],
                    'featured' => true, 'tag' => '&#x1F451; Premium',
                ],
                [
                    'id' => 8, 'name' => 'Caja Platinum', 'price' => '€99.99', 'rarity' => 'legendary',
                    'glow' => 'glow-legendary', 'rarityClass' => 'rarity-legendary', 'accent' => 'red-400',
                    'image' => 'https://images.unsplash.com/photo-1496181133206-80ce9b88a853?w=400&h=400&fit=crop',
                    'items' => ['MacBook Pro', 'iPhone Pro Max', 'Tesla entrada', 'Tarjeta regalo €500', 'Viaje premium'],
                    'allItems' => ['MacBook Pro', 'iPhone Pro Max', 'Tesla entrada', 'Tarjeta regalo €500', 'Viaje premium', 'BMW entrada', 'Montblanc boligrafo'],
                    'featured' => true, 'tag' => '&#x1F451; Exclusiva',
                ],
            ];
        @endphp

        @foreach($cajas as $caja)
            <div class="case-card group relative rounded-2xl bg-white/[0.03] border {{ $caja['rarityClass'] }} {{ $caja['glow'] }} overflow-hidden cursor-pointer"
                 @click="openBox({
                     id: {{ $caja['id'] }},
                     name: '{{ $caja['name'] }}',
                     items: {{ json_encode($caja['items']) }},
                     allItems: {{ json_encode($caja['allItems']) }}
                 })">

                {{-- Shine effect --}}
                <div class="absolute inset-0 case-shine pointer-events-none z-10"></div>

                {{-- Image --}}
                <div class="relative h-44 overflow-hidden">
                    <img src="{{ $caja['image'] }}" alt="{{ $caja['name'] }}" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500" loading="lazy">
                    <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-black/20 to-transparent"></div>
                    @if($caja['tag'])
                        <div class="absolute top-3 left-3 z-20">
                            <span class="px-2.5 py-1 rounded-lg bg-{{ $caja['accent'] }}/90 text-white text-xs font-bold uppercase tracking-wider shadow-lg">{!! $caja['tag'] !!}</span>
                        </div>
                    @endif
                    <div class="absolute bottom-3 left-3 right-3 z-20">
                        <p class="text-xs text-slate-400 uppercase tracking-wider font-medium mb-0.5">{{ $caja['rarity'] }}</p>
                        <p class="text-lg font-extrabold text-white">{{ $caja['name'] }}</p>
                    </div>
                </div>

                {{-- Content --}}
                <div class="p-5 relative z-20">
                    <div class="flex items-center justify-between mb-4">
                        <span class="text-2xl font-extrabold text-{{ $caja['accent'] }}">{{ $caja['price'] }}</span>
                        <span class="text-xs text-slate-500">{{ count($caja['items']) }}+ premios posibles</span>
                    </div>

                    {{-- Preview items --}}
                    <div class="space-y-1.5 mb-4">
                        @foreach(array_slice($caja['items'], 0, 3) as $item)
                            <div class="flex items-center gap-2 text-sm text-slate-400">
                                <span class="w-1.5 h-1.5 rounded-full bg-{{ $caja['accent'] }} opacity-60"></span>
                                {{ $item }}
                            </div>
                        @endforeach
                        <p class="text-xs text-slate-600 pl-3.5">+{{ count($caja['items']) - 3 }} mas...</p>
                    </div>

                    <button class="w-full py-3 rounded-xl bg-white/5 hover:bg-white/10 border border-white/5 text-sm font-bold text-white transition group-hover:border-{{ $caja['accent'] }}/30 group-hover:bg-{{ $caja['accent'] }}/10">
                        Abrir caja
                    </button>
                </div>
            </div>
        @endforeach

    </div>

    {{-- MODAL DE APERTURA --}}
    <div x-show="selectedBox" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-[100] flex items-center justify-center p-4" style="display: none;">
        <div class="absolute inset-0 bg-black/80 backdrop-blur-sm" @click="selectedBox = null; prize = null;"></div>
        <div class="relative z-10 w-full max-w-md rounded-3xl bg-[#14142a] border border-white/10 p-8 text-center"
             x-show="selectedBox"
             x-transition:enter="transition ease-out duration-300 delay-100"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100">

            <button @click="selectedBox = null; prize = null;" class="absolute top-4 right-4 text-slate-500 hover:text-white transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>

            <template x-if="!prize && !opening">
                <div>
                    <div class="w-24 h-24 rounded-3xl mx-auto mb-4 flex items-center justify-center text-5xl bg-white/5 border border-white/10"
                         :class="'glow-' + (selectedBox?.rarity || 'common')">
                        &#x1F4E6;
                    </div>
                    <h3 class="text-xl font-extrabold text-white mb-1" x-text="selectedBox?.name"></h3>
                    <p class="text-sm text-slate-500 mb-6">Haz clic en abrir para descubrir tu premio</p>
                    <button @click="opening = true; setTimeout(() => { const items = selectedBox.allItems || selectedBox.items; prize = items[Math.floor(Math.random() * items.length)]; opening = false; }, 2000);"
                        class="px-8 py-3 rounded-xl bg-purple-500 hover:bg-purple-400 text-black font-bold transition shadow-lg shadow-purple-500/20">
                        Abrir caja
                    </button>
                </div>
            </template>

            <template x-if="opening">
                <div>
                    <div class="w-24 h-24 rounded-3xl mx-auto mb-4 flex items-center justify-center text-5xl bg-white/5 border border-white/10 box-opening"
                         :class="'glow-' + (selectedBox?.rarity || 'common')">
                        &#x1F4E6;
                    </div>
                    <h3 class="text-xl font-extrabold text-white mb-2">Abriendo...</h3>
                    <div class="w-48 h-1.5 bg-white/10 rounded-full mx-auto overflow-hidden">
                        <div class="h-full bg-purple-500 rounded-full animate-pulse" style="width: 60%"></div>
                    </div>
                </div>
            </template>

            <template x-if="prize && !opening">
                <div class="prize-reveal">
                    <div class="text-6xl mb-4">&#x2728;</div>
                    <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full mb-4"
                         :class="{
                             'bg-slate-400/10 text-slate-400': selectedBox?.rarity === 'common',
                             'bg-blue-500/10 text-blue-400': selectedBox?.rarity === 'uncommon',
                             'bg-purple-500/10 text-purple-400': selectedBox?.rarity === 'rare',
                             'bg-amber-500/10 text-amber-400': selectedBox?.rarity === 'epic',
                             'bg-red-500/10 text-red-400': selectedBox?.rarity === 'legendary'
                         }">
                        <span class="w-2 h-2 rounded-full"
                              :class="{
                                  'bg-slate-400': selectedBox?.rarity === 'common',
                                  'bg-blue-500': selectedBox?.rarity === 'uncommon',
                                  'bg-purple-500': selectedBox?.rarity === 'rare',
                                  'bg-amber-500': selectedBox?.rarity === 'epic',
                                  'bg-red-500': selectedBox?.rarity === 'legendary'
                              }"></span>
                        <span class="text-xs font-bold uppercase" x-text="selectedBox?.rarity"></span>
                    </div>
                    <h3 class="text-sm text-slate-400 mb-1">Has ganado</h3>
                    <p class="text-2xl font-extrabold text-white mb-6" x-text="prize"></p>
                    <div class="flex gap-3">
                        <button @click="prize = null; selectedBox = null;" class="flex-1 py-3 rounded-xl bg-white/5 hover:bg-white/10 border border-white/5 text-sm font-bold text-white transition">
                            Cerrar
                        </button>
                        <button class="flex-1 py-3 rounded-xl bg-emerald-500 hover:bg-emerald-400 text-black font-bold text-sm transition">
                            Canjear
                        </button>
                    </div>
                </div>
            </template>
        </div>
    </div>

    {{-- GANANCIAS RECIENTES --}}
    <div class="mb-12">
        <div class="flex items-center gap-2 mb-5">
            <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
            <h2 class="text-xl font-bold text-white">Ganancias recientes</h2>
        </div>
        <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-3" id="recent-wins">
            @foreach([
                ['user' => 'Carlos M.', 'item' => 'iPhone 16 Pro', 'box' => 'Caja Diamond', 'rarity' => 'legendary', 'color' => 'red', 'time' => 'hace 2 min', 'avatar' => 'C'],
                ['user' => 'Ana R.', 'item' => 'AirPods Pro', 'box' => 'Caja Premium', 'rarity' => 'rare', 'color' => 'purple', 'time' => 'hace 5 min', 'avatar' => 'A'],
                ['user' => 'Luis P.', 'item' => 'Tarjeta regalo €10', 'box' => 'Caja Especial', 'rarity' => 'common', 'color' => 'slate', 'time' => 'hace 8 min', 'avatar' => 'L'],
                ['user' => 'Maria G.', 'item' => 'Smartwatch', 'box' => 'Caja Gold', 'rarity' => 'epic', 'color' => 'amber', 'time' => 'hace 12 min', 'avatar' => 'M'],
                ['user' => 'Javi S.', 'item' => 'Mando gaming', 'box' => 'Caja Ruby', 'rarity' => 'rare', 'color' => 'purple', 'time' => 'hace 15 min', 'avatar' => 'J'],
                ['user' => 'Elena V.', 'item' => 'MacBook Air', 'box' => 'Caja Platinum', 'rarity' => 'legendary', 'color' => 'red', 'time' => 'hace 18 min', 'avatar' => 'E'],
                ['user' => 'Pablo D.', 'item' => 'Auriculares ANC', 'box' => 'Caja Gold', 'rarity' => 'epic', 'color' => 'amber', 'time' => 'hace 22 min', 'avatar' => 'P'],
                ['user' => 'Sofia L.', 'item' => 'Tablet basica', 'box' => 'Caja Gold', 'rarity' => 'epic', 'color' => 'amber', 'time' => 'hace 25 min', 'avatar' => 'S'],
            ] as $win)
                <div class="flex items-center gap-3 p-3 rounded-xl bg-white/[0.03] border border-white/5 hover:bg-white/[0.05] transition">
                    <div class="w-10 h-10 rounded-full bg-{{ $win['color'] }}-500/20 flex items-center justify-center text-sm font-bold text-{{ $win['color'] }}-400 shrink-0">{{ $win['avatar'] }}</div>
                    <div class="min-w-0">
                        <p class="text-sm font-semibold text-white truncate">{{ $win['item'] }}</p>
                        <p class="text-xs text-slate-500">{{ $win['user'] }} &middot; {{ $win['time'] }}</p>
                    </div>
                    <span class="px-1.5 py-0.5 rounded text-[9px] font-bold uppercase bg-{{ $win['color'] }}-500/10 text-{{ $win['color'] }}-400 shrink-0">{{ $win['rarity'] }}</span>
                </div>
            @endforeach
        </div>
    </div>

    {{-- COMO FUNCIONA --}}
    <div class="rounded-2xl bg-white/[0.03] border border-white/5 p-6 sm:p-10 mb-12">
        <h2 class="text-xl font-bold text-white mb-8 text-center">Como funciona</h2>
        <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-8">
            @php
                $steps = [
                    ['icon' => '&#x1F4B3;', 'title' => '1. Elige una caja', 'desc' => 'Selecciona la caja que mas te guste segun tu presupuesto.', 'color' => 'brand'],
                    ['icon' => '&#x1F3B2;', 'title' => '2. Abre y descubre', 'desc' => 'Haz clic en abrir y descubre que premio te ha tocado.', 'color' => 'purple'],
                    ['icon' => '&#x1F4E6;', 'title' => '3. Recibe tu premio', 'desc' => 'El premio se anade a tu cuenta inmediatamente.', 'color' => 'emerald'],
                    ['icon' => '&#x1F4B5;', 'title' => '4. Canjea', 'desc' => 'Cambia tu premio por dinero o pide que te lo enviemos.', 'color' => 'amber'],
                ];
            @endphp
            @foreach($steps as $i => $step)
                <div class="text-center relative">
                    @if($i < 3)
                        <div class="hidden sm:block absolute top-6 left-[60%] w-[80%] h-px bg-white/10"></div>
                    @endif
                    <div class="w-14 h-14 rounded-2xl bg-{{ $step['color'] }}-500/10 flex items-center justify-center text-2xl mx-auto mb-4 relative z-10">{!! $step['icon'] !!}</div>
                    <h3 class="text-sm font-bold text-white mb-2">{{ $step['title'] }}</h3>
                    <p class="text-xs text-slate-500 leading-relaxed">{{ $step['desc'] }}</p>
                </div>
            @endforeach
        </div>
    </div>

    {{-- FAQ --}}
    <div class="rounded-2xl bg-white/[0.03] border border-white/5 p-6 sm:p-8"
         x-data="{ openFaq: null }">
        <h2 class="text-xl font-bold text-white mb-5">Preguntas frecuentes</h2>
        <div class="space-y-2">
            @php
                $faqs = [
                    ['q' => 'Que productos puedo ganar?', 'a' => 'Desde tarjetas regalo y auriculares hasta iPhones, MacBooks y viajes. El contenido depende de la caja que elijas.'],
                    ['q' => 'Los productos son reales?', 'a' => 'Si, todos nuestros productos son 100% reales. Puedes canjearlos por dinero o pedir que te los enviemos a casa.'],
                    ['q' => 'Como se calcula la rareza?', 'a' => 'Cada producto tiene un porcentaje de probabilidad. Cuanto mas raro, mayor valor. Puedes ver las probabilidades en cada caja.'],
                    ['q' => 'Puedo canjear varios premios a la vez?', 'a' => 'Si, puedes acumular premios en tu cuenta y canjearlos cuando quieras. No hay limite.'],
                    ['q' => 'Hay envio gratis?', 'a' => 'Si, todos los envios son gratuitos para premios fisicos. El tiempo de entrega es de 3-7 dias laborables.'],
                ];
            @endphp
            @foreach($faqs as $i => $faq)
                <div class="rounded-xl border border-white/5 overflow-hidden" :class="openFaq === {{ $i }} ? 'bg-white/[0.03]' : ''">
                    <button @click="openFaq = openFaq === {{ $i }} ? null : {{ $i }}" class="w-full flex items-center justify-between px-5 py-4 text-left">
                        <span class="text-sm font-semibold text-white">{{ $faq['q'] }}</span>
                        <svg class="w-4 h-4 text-slate-500 transition-transform" :class="openFaq === {{ $i }} && 'rotate-180'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </button>
                    <div x-show="openFaq === {{ $i }}" x-collapse>
                        <div class="px-5 pb-4 text-sm text-slate-400 leading-relaxed">{{ $faq['a'] }}</div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

</div>

@endsection
