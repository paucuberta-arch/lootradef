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
    .case-glow-purple { box-shadow: 0 0 40px -8px rgba(168, 85, 247, 0.3); }
    .case-glow-gold { box-shadow: 0 0 40px -8px rgba(245, 158, 11, 0.3); }
    .case-glow-blue { box-shadow: 0 0 40px -8px rgba(59, 130, 246, 0.3); }
    .case-glow-red { box-shadow: 0 0 40px -8px rgba(239, 68, 68, 0.3); }
    .case-glow-green { box-shadow: 0 0 40px -8px rgba(34, 197, 94, 0.3); }
</style>
@endsection

@section('contenido')

<div class="max-w-[1400px] mx-auto px-4 sm:px-6 lg:px-8 py-6 sm:py-10">

    {{-- Header --}}
    <div class="text-center mb-10">
        <div class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full bg-purple-500/10 border border-purple-500/20 text-purple-300 text-sm font-medium mb-5">
            <span class="text-base">&#x1F4E6;</span> Productos reales
        </div>
        <h1 class="text-3xl sm:text-4xl font-extrabold text-white mb-3">Cajas de Azar</h1>
        <p class="text-slate-500 text-lg max-w-2xl mx-auto">Abre cajas y gana productos reales. Canjealos por dinero o pedir que te los enviamos a casa.</p>
    </div>

    {{-- Leyenda --}}
    <div class="flex flex-wrap justify-center gap-4 mb-10">
        @foreach([
            ['color' => 'bg-slate-400', 'label' => 'Comun', 'pct' => '45%'],
            ['color' => 'bg-blue-500', 'label' => 'Poco comun', 'pct' => '30%'],
            ['color' => 'bg-purple-500', 'label' => 'Raro', 'pct' => '15%'],
            ['color' => 'bg-amber-500', 'label' => 'Epico', 'pct' => '8%'],
            ['color' => 'bg-red-500', 'label' => 'Legendario', 'pct' => '2%'],
        ] as $rarity)
            <div class="flex items-center gap-2 text-sm">
                <span class="w-3 h-3 rounded-full {{ $rarity['color'] }}"></span>
                <span class="text-slate-400">{{ $rarity['label'] }}</span>
                <span class="text-slate-600">({{ $rarity['pct'] }})</span>
            </div>
        @endforeach
    </div>

    {{-- Cajas --}}
    <div class="grid sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-5" x-data="{ opened: null }">

        @php
            $cajas = [
                ['id' => 1, 'name' => 'Caja Basica', 'price' => '€2.99', 'glow' => 'case-glow-blue', 'border' => 'border-blue-500/20', 'accent' => 'text-blue-400', 'bg' => 'from-blue-500/10 to-blue-600/5', 'items' => ['Auriculares Bluetooth', 'Funda de movil', 'Estuche de auriculares', 'Tarjeta regalo €5', 'Funda portatil']],
                ['id' => 2, 'name' => 'Caja Premium', 'price' => '€9.99', 'glow' => 'case-glow-purple', 'border' => 'border-purple-500/20', 'accent' => 'text-purple-400', 'bg' => 'from-purple-500/10 to-purple-600/5', 'items' => ['Smartwatch deportivo', 'Altavoz bluetooth', 'Cargador inalambrico', 'Tarjeta regalo €15', 'Funda premium']],
                ['id' => 3, 'name' => 'Caja Gold', 'price' => '€24.99', 'glow' => 'case-glow-gold', 'border' => 'border-amber-500/20', 'accent' => 'text-amber-400', 'bg' => 'from-amber-500/10 to-amber-600/5', 'items' => ['Auriculares ANC', 'Tablet basica', 'Mando gaming', 'Tarjeta regalo €50', 'Smartwatch']],
                ['id' => 4, 'name' => 'Caja Diamond', 'price' => '€49.99', 'glow' => 'case-glow-green', 'border' => 'border-emerald-500/20', 'accent' => 'text-emerald-400', 'bg' => 'from-emerald-500/10 to-emerald-600/5', 'items' => ['iPhone / Samsung', 'MacBook Air', 'PS5 / Xbox', 'Tarjeta regalo €200', 'iPad']],
                ['id' => 5, 'name' => 'Caja Ruby', 'price' => '€14.99', 'glow' => 'case-glow-red', 'border' => 'border-red-500/20', 'accent' => 'text-red-400', 'bg' => 'from-red-500/10 to-red-600/5', 'items' => ['Zapatillas deportivas', 'Reloj clasico', 'Cartera de piel', 'Tarjeta regalo €25', 'Gafas de sol']],
                ['id' => 6, 'name' => 'Caja Starter', 'price' => '€0.99', 'glow' => '', 'border' => 'border-slate-500/20', 'accent' => 'text-slate-400', 'bg' => 'from-slate-500/10 to-slate-600/5', 'items' => ['Vinilo adhesivo', 'Llavero metalico', 'Sticker pack', 'Tarjeta regalo €1', 'Camiseta basica']],
                ['id' => 7, 'name' => 'Caja Platinum', 'price' => '€99.99', 'glow' => 'case-glow-purple', 'border' => 'border-purple-500/20', 'accent' => 'text-purple-400', 'bg' => 'from-purple-500/10 via-amber-500/5 to-purple-600/5', 'items' => ['MacBook Pro', 'iPhone Pro Max', 'Tesla Model 3 ( entrada )', 'Tarjeta regalo €500', 'Viaje premium']],
                ['id' => 8, 'name' => 'Caja Especial', 'price' => '€4.99', 'glow' => 'case-glow-gold', 'border' => 'border-amber-500/20', 'accent' => 'text-amber-400', 'bg' => 'from-amber-500/10 to-orange-600/5', 'items' => ['Auriculares gaming', 'Teclado mecanico', 'Raton gaming', 'Tarjeta regalo €10', 'Alfombrilla XL']],
            ];
        @endphp

        @foreach($cajas as $caja)
            <div class="group relative rounded-2xl bg-white/[0.03] border {{ $caja['border'] }} p-6 {{ $caja['glow'] }} hover:scale-[1.02] transition-all duration-300 cursor-pointer"
                 @click="opened = opened === {{ $caja['id'] }} ? null : {{ $caja['id'] }}">

                {{-- Shine effect --}}
                <div class="absolute inset-0 rounded-2xl case-shine pointer-events-none"></div>

                <div class="relative z-10">
                    {{-- Icono caja --}}
                    <div class="w-16 h-16 rounded-2xl bg-gradient-to-br {{ $caja['bg'] }} flex items-center justify-center text-3xl mb-4 mx-auto group-hover:scale-110 transition-transform">
                        &#x1F4E6;
                    </div>

                    <h3 class="text-lg font-bold text-white text-center mb-1">{{ $caja['name'] }}</h3>
                    <p class="text-center {{ $caja['accent'] }} font-extrabold text-xl mb-4">{{ $caja['price'] }}</p>

                    {{-- Contenido posible --}}
                    <div x-show="opened === {{ $caja['id'] }}" x-collapse class="mt-4 pt-4 border-t border-white/5">
                        <p class="text-xs font-bold text-slate-500 uppercase tracking-wider mb-3">Posibles premios</p>
                        <div class="space-y-2">
                            @foreach($caja['items'] as $item)
                                <div class="flex items-center gap-2 text-sm text-slate-400">
                                    <span class="w-1.5 h-1.5 rounded-full {{ $caja['accent'] }} bg-current opacity-50"></span>
                                    {{ $item }}
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <button class="w-full mt-4 py-2.5 rounded-xl bg-white/5 hover:bg-white/10 border border-white/5 text-sm font-semibold text-white transition">
                        <span x-text="opened === {{ $caja['id'] }} ? 'Cerrar' : 'Ver contenido'"></span>
                    </button>
                </div>
            </div>
        @endforeach

    </div>

    {{-- Info --}}
    <div class="mt-12 rounded-2xl bg-white/[0.03] border border-white/5 p-6 sm:p-8">
        <h2 class="text-xl font-bold text-white mb-4">Como funciona</h2>
        <div class="grid sm:grid-cols-3 gap-6">
            <div class="text-center">
                <div class="w-12 h-12 rounded-xl bg-brand-500/10 flex items-center justify-center text-2xl mx-auto mb-3">&#x1F4B3;</div>
                <h3 class="text-sm font-bold text-white mb-1">1. Compra una caja</h3>
                <p class="text-xs text-slate-500">Elige la caja que quieras y paga con tu saldo.</p>
            </div>
            <div class="text-center">
                <div class="w-12 h-12 rounded-xl bg-purple-500/10 flex items-center justify-center text-2xl mx-auto mb-3">&#x1F3B2;</div>
                <h3 class="text-sm font-bold text-white mb-1">2. Abre y gana</h3>
                <p class="text-xs text-slate-500">Abre la caja y descubre que premio te ha tocado.</p>
            </div>
            <div class="text-center">
                <div class="w-12 h-12 rounded-xl bg-emerald-500/10 flex items-center justify-center text-2xl mx-auto mb-3">&#x1F4E6;</div>
                <h3 class="text-sm font-bold text-white mb-1">3. Canjea</h3>
                <p class="text-xs text-slate-500">Cambia tu premio por dinero o pide que te lo enviemos.</p>
            </div>
        </div>
    </div>

</div>

@endsection
