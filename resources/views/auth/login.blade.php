@extends('layouts.auth')

@section('title', 'Iniciar sesion — Lootra Casino')

@section('contenido')

    <div class="relative min-h-[80vh] flex items-center justify-center px-4 py-16">
        <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[500px] h-[500px] bg-brand-500/5 rounded-full blur-[120px] pointer-events-none"></div>

        <div class="relative w-full max-w-md">

            <div class="text-center mb-8">
                <a href="{{ route('inicio') }}" class="inline-flex items-center gap-2 mb-6">
                    <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-brand-400 to-brand-600 flex items-center justify-center text-black text-xl font-black shadow-lg shadow-brand-500/20">L</div>
                </a>
                <h1 class="text-2xl sm:text-3xl font-bold text-white">Bienvenido de nuevo</h1>
                <p class="text-slate-500 mt-2">Inicia sesion para jugar</p>
            </div>

            <div class="rounded-2xl bg-white/[0.03] border border-white/5 p-6 sm:p-8 backdrop-blur-sm">

                @if($errors->any())
                    <x-ui.alert type="error" class="mb-6">
                        @foreach($errors->all() as $error)
                            <p>{{ $error }}</p>
                        @endforeach
                    </x-ui.alert>
                @endif

                <form action="{{ route('login.store') }}" method="POST" class="space-y-5" x-data="{ submitting: false, showPassword: false }" @submit="submitting = true">
                    @csrf

                    <div>
                        <label for="email" class="block text-sm font-medium text-slate-300 mb-2">Correo electrónico</label>
                        <input id="email" type="email" name="email" value="{{ old('email') }}" required autocomplete="email" placeholder="tu@email.com"
                            @class(['w-full rounded-xl bg-white/5 border px-4 py-3 text-white placeholder-slate-600 outline-none focus:ring-1 transition', 'border-red-400/50 focus:border-red-400 focus:ring-red-400/20' => $errors->has('email'), 'border-white/10 focus:border-brand-500 focus:ring-brand-500/30' => !$errors->has('email')]) aria-invalid="{{ $errors->has('email') ? 'true' : 'false' }}" @error('email') aria-describedby="email-error" @enderror>
                        @error('email')<p id="email-error" class="mt-2 text-sm text-red-300">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label for="password" class="block text-sm font-medium text-slate-300 mb-2">Contraseña</label>
                        <div class="relative"><input id="password" :type="showPassword ? 'text' : 'password'" name="password" required autocomplete="current-password" placeholder="••••••••" class="w-full rounded-xl border border-white/10 bg-white/5 px-4 py-3 pr-20 text-white outline-none focus:border-brand-500 focus:ring-1 focus:ring-brand-500/30"><button type="button" @click="showPassword=!showPassword" class="absolute inset-y-0 right-0 min-w-16 px-3 text-xs font-bold text-slate-400 hover:text-white" :aria-label="showPassword ? 'Ocultar contraseña' : 'Mostrar contraseña'" x-text="showPassword ? 'Ocultar' : 'Mostrar'"></button></div>
                    </div>

                    <label class="flex items-center gap-3 text-sm text-slate-400 cursor-pointer">
                        <input type="checkbox" name="remember" value="1" class="w-4 h-4 rounded border-white/20 bg-white/5 text-brand-500 focus:ring-brand-500/30">
                        Recordarme
                    </label>

                    <x-ui.button type="submit" class="w-full" x-bind:disabled="submitting"><span x-text="submitting ? 'Entrando…' : 'Entrar'">Entrar</span></x-ui.button>
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
