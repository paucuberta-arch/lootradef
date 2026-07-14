@extends('layouts.app')

@section('title', $pageTitle . ' — Lootra Casino')

@section('contenido')

<div class="max-w-[800px] mx-auto px-4 sm:px-6 lg:px-8 py-6 sm:py-10">

    <nav class="flex items-center gap-2 text-sm text-slate-500 mb-6">
        <a href="{{ url('/') }}" class="hover:text-white transition">Inicio</a>
        <span>/</span>
        <span class="text-slate-300">{{ $pageTitle }}</span>
    </nav>

    <div class="rounded-2xl bg-white/[0.03] border border-white/5 p-6 sm:p-10">
        <h1 class="text-2xl sm:text-3xl font-extrabold text-white mb-6">{{ $pageTitle }}</h1>
        <div class="prose prose-invert max-w-none text-slate-400 leading-relaxed space-y-4">
            {!! $content !!}
        </div>
    </div>

</div>

@endsection
