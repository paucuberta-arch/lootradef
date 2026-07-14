@extends('layouts.app')

@section('title', 'Detalle de operación — Lootra')

@section('menu')
    @include('partials.menu')
@endsection

@section('contenido')

    <div class="max-w-xl mx-auto px-4 sm:px-6 py-10 sm:py-16">

        <div class="glass-card rounded-2xl p-6 sm:p-8">

            <div class="text-center mb-8">
                <p class="text-xs font-medium text-slate-500 uppercase tracking-wider mb-2">Operación #{{ $operacion->id }}</p>
                <h1 class="text-2xl sm:text-3xl font-bold text-white">Detalle</h1>
            </div>

            {{-- Operación --}}
            <div class="text-center mb-6 p-6 rounded-xl bg-white/[0.02] border border-white/5">
                <p class="text-sm text-slate-500 mb-2">Expresión</p>
                <p class="text-2xl sm:text-3xl font-bold text-white font-mono">
                    {{ $operacion->operacion }}
                </p>
            </div>

            {{-- Resultado --}}
            <div class="text-center p-6 rounded-xl bg-brand-600/10 border border-brand-500/20 mb-6">
                <p class="text-sm text-brand-300 mb-2">Resultado</p>
                <p class="text-4xl sm:text-5xl font-black text-white">
                    {{ $operacion->resultado }}
                </p>
            </div>

            {{-- Fecha --}}
            <p class="text-center text-sm text-slate-600 mb-8">
                Creado el {{ $operacion->created_at->format('d/m/Y a las H:i') }}
            </p>

            {{-- Volver --}}
            <a href="{{ route('resultados.index') }}"
               class="block w-full text-center rounded-xl bg-white/5 hover:bg-white/10 text-white font-semibold py-3 border border-white/10 hover:border-white/20 transition-all">
                &larr; Volver a resultados
            </a>

        </div>

    </div>

@endsection
