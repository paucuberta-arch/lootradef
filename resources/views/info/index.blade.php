@extends('layouts.app')

@section('title', $pageTitle . ' — Lootra Casino')

@section('contenido')

<div class="max-w-[960px] mx-auto px-4 sm:px-6 lg:px-8 py-6 sm:py-12">

    <nav class="flex items-center gap-2 text-sm text-slate-500 mb-6">
        <a href="{{ url('/') }}" class="hover:text-white transition">Inicio</a>
        <span>/</span>
        <span class="text-slate-300">{{ $pageTitle }}</span>
    </nav>

    <header class="relative mb-5 min-h-52 overflow-hidden rounded-3xl border border-white/10 sm:min-h-60">
        <img src="{{ asset('images/lootra_visual_pack/05_top_panels/panel_promociones_1920x360.webp') }}" alt="" class="absolute inset-0 h-full w-full object-cover opacity-75" aria-hidden="true" decoding="async">
        <div class="absolute inset-0 bg-gradient-to-r from-[#070816] via-[#070816]/80 to-transparent"></div>
        <div class="relative z-10 flex min-h-52 max-w-2xl flex-col justify-end p-6 sm:min-h-60 sm:p-10">
            <span class="world-kicker">Universo Lootra</span>
            <h1 class="mt-3 font-display text-3xl font-black text-white sm:text-5xl">{{ $pageTitle }}</h1>
        </div>
    </header>

    <div class="world-panel rounded-3xl p-6 sm:p-10">
        <div class="prose prose-invert max-w-none space-y-4 leading-relaxed text-slate-400 prose-headings:font-display prose-headings:text-white prose-a:text-cyan-300">
            {!! $content !!}
        </div>
    </div>

</div>

@endsection
