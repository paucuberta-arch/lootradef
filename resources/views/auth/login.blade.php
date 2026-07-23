@extends('layouts.auth')

@section('title', 'Iniciar sesion — Lootra Casino')

@section('contenido')

    <div class="relative min-h-screen flex items-center justify-center px-4 py-8 sm:py-12">
        <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 h-[min(500px,90vw)] w-[min(500px,90vw)] bg-brand-500/5 rounded-full blur-[120px] pointer-events-none"></div>

        <div class="relative grid w-full max-w-5xl overflow-hidden rounded-3xl border border-white/10 bg-[#080a16]/85 shadow-2xl shadow-black/50 backdrop-blur-xl lg:grid-cols-[1.05fr_.95fr]">
            <aside class="relative hidden min-h-[650px] overflow-hidden lg:block">
                <img src="{{ asset('images/lootra_visual_pack/01_heroes/hero_home_lootra_960x450.webp') }}" srcset="{{ asset('images/lootra_visual_pack/01_heroes/hero_home_lootra_960x450.webp') }} 960w, {{ asset('images/lootra_visual_pack/01_heroes/hero_home_lootra_1920x900.webp') }} 1920w" sizes="(min-width: 1024px) 520px, 1px" width="1920" height="900" alt="El universo de Lootra" class="absolute inset-0 h-full w-full object-cover" loading="lazy" decoding="async">
                <div class="absolute inset-0 bg-gradient-to-t from-[#070816] via-[#070816]/35 to-cyan-950/15"></div>
                <div class="absolute inset-x-0 bottom-0 p-10">
                    <span class="world-kicker">Tu partida continúa</span>
                    <h2 class="mt-4 font-display text-4xl font-black leading-tight">Vuelve a tu universo de juego.</h2>
                    <p class="mt-4 max-w-md text-sm leading-relaxed text-slate-300">Casino, originales, drops y retos viven dentro del mismo perfil.</p>
                </div>
            </aside>

            <div class="p-5 sm:p-10 lg:p-12">

            <div class="text-center mb-8">
                <a href="{{ route('inicio') }}" class="inline-flex items-center gap-2 mb-6">
                    <div class="brand-orbit w-12 h-12 rounded-xl bg-gradient-to-br from-brand-400 to-cyan-300 flex items-center justify-center text-black text-xl font-black shadow-lg shadow-brand-500/20">L</div>
                </a>
                <h1 class="text-2xl sm:text-3xl font-bold text-white">Bienvenido de nuevo</h1>
                <p class="text-slate-500 mt-2">Inicia sesion para jugar</p>
            </div>

            <div class="rounded-2xl bg-white/[0.035] border border-white/10 p-5 sm:p-7 backdrop-blur-sm">

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

                    <div class="text-right">
                        <a href="{{ route('password.request') }}" class="text-sm text-brand-400 hover:text-brand-300">¿Has olvidado tu contraseña?</a>
                    </div>

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
    </div>

@endsection
