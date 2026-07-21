@extends('layouts.app')

@section('title', 'Preferencias de correo — Lootra')

@section('contenido')
<section class="mx-auto grid min-h-[70vh] max-w-2xl place-items-center px-4 py-16 text-center">
    <div class="w-full rounded-3xl border border-white/10 bg-white/[.04] p-7 shadow-2xl sm:p-12">
        <div class="mx-auto grid h-14 w-14 place-items-center rounded-2xl bg-emerald-400/15 text-2xl text-emerald-300">✓</div>
        <p class="mt-6 text-xs font-bold uppercase tracking-[.22em] text-emerald-300">Preferencia guardada</p>
        <h1 class="mt-3 text-3xl font-bold text-white sm:text-4xl">No recibirás más newsletters promocionales.</h1>
        <p class="mx-auto mt-4 max-w-lg leading-relaxed text-slate-400">Seguiremos enviando únicamente los mensajes necesarios relacionados con la seguridad y la actividad de tu cuenta.</p>
        <a href="{{ route('inicio') }}" class="mt-8 inline-flex rounded-xl bg-white px-6 py-3 font-extrabold text-slate-950">Volver a Lootra</a>
    </div>
</section>
@endsection
