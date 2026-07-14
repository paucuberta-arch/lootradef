@extends('layouts.app')

@section('title', 'Crear cuenta — Lootra')

@section('menu')
    @include('partials.menu')
@endsection

@section('contenido')

    <div class="relative min-h-[80vh] flex items-center justify-center px-4 py-16">
        <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[500px] h-[500px] bg-brand-600/10 rounded-full blur-[120px] pointer-events-none"></div>

        <div class="relative w-full max-w-md">

            <div class="text-center mb-8">
                <div class="w-14 h-14 rounded-2xl bg-brand-600 flex items-center justify-center text-white text-2xl font-black mx-auto mb-5 shadow-lg shadow-brand-600/30">
                    L
                </div>
                <h1 class="text-2xl sm:text-3xl font-bold text-white">Crear una cuenta</h1>
                <p class="text-slate-400 mt-2">Empieza a usar Lootra en segundos</p>
            </div>

            <div class="glass-card rounded-2xl p-6 sm:p-8">

                @if($errors->any())
                    <div class="mb-6 rounded-xl bg-red-500/10 border border-red-500/20 p-4">
                        <ul class="list-disc list-inside space-y-1">
                            @foreach($errors->all() as $error)
                                <li class="text-red-400 text-sm">{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form action="{{ route('registro.store') }}" method="POST" class="space-y-5">
                    @csrf

                    <div>
                        <label for="name" class="block text-sm font-medium text-slate-300 mb-2">
                            Nombre
                        </label>
                        <input
                            id="name"
                            type="text"
                            name="name"
                            value="{{ old('name') }}"
                            required
                            autocomplete="name"
                            placeholder="Tu nombre"
                            class="w-full rounded-xl bg-white/5 border border-white/10 px-4 py-3 text-white placeholder-slate-500 outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 transition"
                        >
                    </div>

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
                            autocomplete="new-password"
                            placeholder="Mínimo 8 caracteres"
                            class="w-full rounded-xl bg-white/5 border border-white/10 px-4 py-3 text-white placeholder-slate-500 outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 transition"
                        >
                    </div>

                    <div>
                        <label for="password_confirmation" class="block text-sm font-medium text-slate-300 mb-2">
                            Repite la contraseña
                        </label>
                        <input
                            id="password_confirmation"
                            type="password"
                            name="password_confirmation"
                            required
                            autocomplete="new-password"
                            placeholder="Confirma tu contraseña"
                            class="w-full rounded-xl bg-white/5 border border-white/10 px-4 py-3 text-white placeholder-slate-500 outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 transition"
                        >
                    </div>

                    <button
                        type="submit"
                        class="w-full rounded-xl bg-brand-600 hover:bg-brand-500 text-white font-bold py-3.5 transition-all shadow-lg shadow-brand-600/25 hover:shadow-brand-500/40 hover:-translate-y-0.5"
                    >
                        Crear cuenta
                    </button>
                </form>

                <div class="mt-6 pt-6 border-t border-white/5 text-center">
                    <p class="text-sm text-slate-500">
                        ¿Ya tienes una cuenta?
                        <a href="{{ route('login') }}" class="font-semibold text-brand-400 hover:text-brand-300 transition">
                            Inicia sesión
                        </a>
                    </p>
                </div>

            </div>

        </div>
    </div>

@endsection
