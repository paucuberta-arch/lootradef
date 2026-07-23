@extends('layouts.auth')

@section('title', 'Verifica tu correo — Lootra Casino')

@section('contenido')
<main class="flex min-h-screen items-center justify-center px-4 py-12">
    <section class="w-full max-w-lg rounded-3xl border border-white/10 bg-[#080a16]/90 p-8 text-center shadow-2xl">
        <h1 class="font-display text-3xl font-black text-white">Verifica tu correo</h1>
        <p class="mt-4 text-slate-300">Hemos enviado un enlace firmado a tu dirección. El bono y el reto se activarán después de verificarla.</p>

        @if(session('success'))
            <p class="mt-5 rounded-xl border border-emerald-400/20 bg-emerald-400/10 p-3 text-sm text-emerald-200">{{ session('success') }}</p>
        @endif

        <form method="POST" action="{{ route('verification.send') }}" class="mt-6">
            @csrf
            <x-ui.button type="submit" class="w-full">Reenviar enlace</x-ui.button>
        </form>

        <form method="POST" action="{{ route('logout') }}" class="mt-3">
            @csrf
            <button type="submit" class="text-sm font-bold text-slate-400 hover:text-white">Cerrar sesión</button>
        </form>
    </section>
</main>
@endsection
