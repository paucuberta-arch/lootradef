@extends('layouts.app')

@section('title', 'Casino Lootra — Próximamente')

@section('contenido')

    <section class="relative min-h-[85vh] flex items-center justify-center overflow-hidden">
        {{-- Background --}}
        <div class="absolute inset-0 bg-gradient-to-br from-slate-950 via-gray-950 to-amber-950/20"></div>

        {{-- Glow effects --}}
        <div class="absolute top-1/4 left-1/4 w-[500px] h-[500px] bg-amber-500/5 rounded-full blur-[120px] pointer-events-none animate-casino"></div>
        <div class="absolute bottom-1/4 right-1/4 w-[400px] h-[400px] bg-brand-500/5 rounded-full blur-[100px] pointer-events-none animate-casino-delay"></div>

        {{-- Floating emojis (desktop only) --}}
        <div class="hidden lg:block absolute top-20 left-16 text-[100px] opacity-[0.04] blur-[1px] animate-casino">&#x1F3B0;</div>
        <div class="hidden lg:block absolute top-32 right-20 text-[80px] opacity-[0.04] blur-[1px] animate-casino-delay">&#x1F3B2;</div>
        <div class="hidden lg:block absolute bottom-32 left-20 text-[90px] opacity-[0.04] blur-[1px] animate-casino-slow">&#x1F0CF;</div>
        <div class="hidden lg:block absolute bottom-20 right-16 text-[110px] opacity-[0.04] blur-[1px] animate-casino">&#x1F4B0;</div>

        {{-- Content --}}
        <div class="relative z-10 max-w-3xl mx-auto px-4 sm:px-6 text-center">

            <div class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full bg-amber-500/10 border border-amber-500/20 text-amber-300 text-sm font-medium mb-8">
                <span class="w-2 h-2 rounded-full bg-amber-400 animate-pulse"></span>
                Próximamente
            </div>

            <h1 class="text-4xl sm:text-5xl lg:text-7xl font-black tracking-tight mb-6">
                <span class="text-white">Casino</span>
                <span class="bg-gradient-to-r from-amber-400 to-amber-200 bg-clip-text text-transparent"> Lootra</span>
            </h1>

            <p class="text-lg sm:text-xl text-slate-400 leading-relaxed mb-10 max-w-2xl mx-auto">
                Estamos preparando una experiencia de casino completamente nueva.
                Juegos, ruletas, estadísticas y muchas sorpresas te esperan.
            </p>

            <div class="flex flex-wrap justify-center gap-3 mb-12">
                <span class="px-4 py-2 rounded-xl bg-amber-500/10 border border-amber-500/20 text-amber-300 text-sm font-medium">&#x1F3B2; Juegos</span>
                <span class="px-4 py-2 rounded-xl bg-amber-500/10 border border-amber-500/20 text-amber-300 text-sm font-medium">&#x1F3B0; Ruletas</span>
                <span class="px-4 py-2 rounded-xl bg-amber-500/10 border border-amber-500/20 text-amber-300 text-sm font-medium">&#x1F0CF; Cartas</span>
                <span class="px-4 py-2 rounded-xl bg-amber-500/10 border border-amber-500/20 text-amber-300 text-sm font-medium">&#x1F3C6; Torneos</span>
            </div>

            <a href="{{ url('/') }}"
               class="inline-flex items-center px-8 py-4 rounded-xl bg-white/5 hover:bg-white/10 text-white font-semibold text-lg border border-white/10 hover:border-white/20 transition-all">
                &larr; Volver al inicio
            </a>

        </div>
    </section>

@endsection
