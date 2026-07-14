@extends('layouts.app')

@section('title', 'Iniciar sesión — Lootra')

@section('menu')
    @include('partials.menu')
@endsection

@section('contenido')

    <div class="relative min-h-[80vh] flex items-center justify-center px-4 py-16">
        {{-- Background glow --}}
        <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[500px] h-[500px] bg-brand-600/10 rounded-full blur-[120px] pointer-events-none"></div>

        <div class="relative w-full max-w-md">

            {{-- Header --}}
            <div class="text-center mb-8">
                <div class="w-14 h-14 rounded-2xl bg-brand-600 flex items-center justify-center text-white text-2xl font-black mx-auto mb-5 shadow-lg shadow-brand-600/30">
                    L
                </div>
                <h1 class="text-2xl sm:text-3xl font-bold text-white">Bienvenido de nuevo</h1>
                <p class="text-slate-400 mt-2">Inicia sesión en tu cuenta</p>
            </div>

            {{-- Card --}}
            <div class="glass-card rounded-2xl p-6 sm:p-8">

                @if($errors->any())
                    <div class="mb-6 rounded-xl bg-red-500/10 border border-red-500/20 p-4">
                        @foreach($errors->all() as $error)
                            <p class="text-red-400 text-sm">{{ $error }}</p>
                        @endforeach
                    </div>
                @endif

                <form action="{{ route('login.store') }}" method="POST" class="space-y-5">
                    @csrf

                    <div>
                        <label for="email" class="block text-sm font-medium text-slate-300 mb-2">
                            Correo electrónico
                        </label>
                        <input
                            id="email"
                            type="email"
                            name="email"
                            value="{{ old('email') }}"
                            required
                            autocomplete="email"
                            placeholder="tu@email.com"
                            class="w-full rounded-xl bg-white/5 border border-white/10 px-4 py-3 text-white placeholder-slate-500 outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 transition"
                        >
                    </div>

                    <div>
                        <label for="password" class="block text-sm font-medium text-slate-300 mb-2">
                            Contraseña
                        </label>
                        <input
                            id="password"
                            type="password"
                            name="password"
                            required
                            autocomplete="current-password"
                            placeholder="••••••••"
                            class="w-full rounded-xl bg-white/5 border border-white/10 px-4 py-3 text-white placeholder-slate-500 outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 transition"
                        >
                    </div>

                    <label class="flex items-center gap-3 text-sm text-slate-400 cursor-pointer">
                        <input type="checkbox" name="remember" value="1"
                               class="w-4 h-4 rounded border-white/20 bg-white/5 text-brand-500 focus:ring-brand-500/20">
                        Recordarme
                    </label>

                    <button
                        type="submit"
                        class="w-full rounded-xl bg-brand-600 hover:bg-brand-500 text-white font-bold py-3.5 transition-all shadow-lg shadow-brand-600/25 hover:shadow-brand-500/40 hover:-translate-y-0.5"
                    >
                        Entrar
                    </button>
                </form>

                <div class="mt-6 pt-6 border-t border-white/5 text-center">
                    <p class="text-sm text-slate-500">
                        ¿No tienes cuenta?
                        <a href="{{ route('registro') }}" class="font-semibold text-brand-400 hover:text-brand-300 transition">
                            Regístrate
                        </a>
                    </p>
                </div>

            </div>

        </div>
    </div>

@endsection
