@extends('layouts.app')

@section('title', 'Iniciar sesion — Lootra Casino')

@section('contenido')

    <div class="relative min-h-[80vh] flex items-center justify-center px-4 py-16">
        <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[500px] h-[500px] bg-brand-500/5 rounded-full blur-[120px] pointer-events-none"></div>

        <div class="relative w-full max-w-md">

            <div class="text-center mb-8">
                <a href="{{ url('/') }}" class="inline-flex items-center gap-2 mb-6">
                    <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-brand-400 to-brand-600 flex items-center justify-center text-black text-xl font-black shadow-lg shadow-brand-500/20">L</div>
                </a>
                <h1 class="text-2xl sm:text-3xl font-bold text-white">Bienvenido de nuevo</h1>
                <p class="text-slate-500 mt-2">Inicia sesion para jugar</p>
            </div>

            <div class="rounded-2xl bg-white/[0.03] border border-white/5 p-6 sm:p-8 backdrop-blur-sm">

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
                        <label for="email" class="block text-sm font-medium text-slate-300 mb-2">Correo electronico</label>
                        <input id="email" type="email" name="email" value="{{ old('email') }}" required autocomplete="email" placeholder="tu@email.com"
                            class="w-full rounded-xl bg-white/5 border border-white/10 px-4 py-3 text-white placeholder-slate-600 outline-none focus:border-brand-500 focus:ring-1 focus:ring-brand-500/30 transition">
                    </div>

                    <div>
                        <label for="password" class="block text-sm font-medium text-slate-300 mb-2">Contrasena</label>
                        <input id="password" type="password" name="password" required autocomplete="current-password" placeholder="••••••••"
                            class="w-full rounded-xl bg-white/5 border border-white/10 px-4 py-3 text-white placeholder-slate-600 outline-none focus:border-brand-500 focus:ring-1 focus:ring-brand-500/30 transition">
                    </div>

                    <label class="flex items-center gap-3 text-sm text-slate-400 cursor-pointer">
                        <input type="checkbox" name="remember" value="1" class="w-4 h-4 rounded border-white/20 bg-white/5 text-brand-500 focus:ring-brand-500/30">
                        Recordarme
                    </label>

                    <button type="submit" class="w-full rounded-xl bg-brand-500 hover:bg-brand-400 text-black font-bold py-3.5 transition-all shadow-lg shadow-brand-500/20 hover:shadow-brand-400/30 hover:-translate-y-0.5">
                        Entrar
                    </button>
                </form>

                <div class="mt-6 pt-6 border-t border-white/5 text-center">
                    <p class="text-sm text-slate-500">
                        No tienes cuenta?
                        <a href="{{ route('registro') }}" class="font-semibold text-brand-400 hover:text-brand-300 transition">Registrate</a>
                    </p>
                </div>
            </div>

        </div>
    </div>

@endsection
