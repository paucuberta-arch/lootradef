@extends('layouts.auth')

@section('title', 'Crear cuenta — Lootra Casino')

@section('contenido')
@if($campaignAttributed ?? false)
<div class="mx-auto mt-8 max-w-lg px-4"><div class="rounded-xl border border-fuchsia-400/25 bg-fuchsia-400/10 p-4 text-center font-bold text-fuchsia-100">Crea tu cuenta y recibe {{ number_format($rickyeditCampaign['initial_balance'] ?? 1000, 0, ',', '.') }} créditos demo para el reto.</div></div>
@endif

    <div class="relative min-h-[80vh] flex items-center justify-center px-4 py-8 sm:py-16">
        <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 h-[min(500px,90vw)] w-[min(500px,90vw)] bg-brand-500/5 rounded-full blur-[120px] pointer-events-none"></div>

        <div class="relative w-full max-w-md">

            <div class="text-center mb-8">
                <a href="{{ route('inicio') }}" class="inline-flex items-center gap-2 mb-6">
                    <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-brand-400 to-brand-600 flex items-center justify-center text-black text-xl font-black shadow-lg shadow-brand-500/20">L</div>
                </a>
                <h1 class="text-2xl sm:text-3xl font-bold text-white">Crear una cuenta</h1>
                <p class="text-slate-500 mt-2">Unete a Lootra Casino</p>
            </div>

            <div class="rounded-2xl bg-white/[0.03] border border-white/5 p-6 sm:p-8 backdrop-blur-sm">

                @if($errors->any())
                    <x-ui.alert type="error" class="mb-6">
                        <ul class="list-disc list-inside space-y-1">
                            @foreach($errors->all() as $error)
                                <li class="text-red-400 text-sm">{{ $error }}</li>
                            @endforeach
                        </ul>
                    </x-ui.alert>
                @endif

                <form action="{{ route('registro.store') }}" method="POST" class="space-y-5" x-data="{ submitting: false, showPassword: false, showConfirmation: false, password: '' }" @submit="submitting = true">
                    @csrf

                    <div>
                        <label for="name" class="block text-sm font-medium text-slate-300 mb-2">Nombre</label>
                        <input id="name" type="text" name="name" value="{{ old('name') }}" required autocomplete="name" placeholder="Tu nombre"
                            class="w-full rounded-xl bg-white/5 border border-white/10 px-4 py-3 text-white placeholder-slate-600 outline-none focus:border-brand-500 focus:ring-1 focus:ring-brand-500/30 transition" @error('name') aria-invalid="true" aria-describedby="name-error" @enderror>
                        @error('name')<p id="name-error" class="mt-2 text-sm text-red-300">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label for="email" class="block text-sm font-medium text-slate-300 mb-2">Correo electrónico</label>
                        <input id="email" type="email" name="email" value="{{ old('email') }}" required autocomplete="email" placeholder="tu@email.com"
                            class="w-full rounded-xl bg-white/5 border border-white/10 px-4 py-3 text-white placeholder-slate-600 outline-none focus:border-brand-500 focus:ring-1 focus:ring-brand-500/30 transition" @error('email') aria-invalid="true" aria-describedby="register-email-error" @enderror>
                        @error('email')<p id="register-email-error" class="mt-2 text-sm text-red-300">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label for="password" class="block text-sm font-medium text-slate-300 mb-2">Contraseña</label>
                        <div class="relative"><input id="password" x-model="password" :type="showPassword ? 'text' : 'password'" name="password" required autocomplete="new-password" placeholder="Mínimo 8 caracteres" class="w-full rounded-xl border border-white/10 bg-white/5 px-4 py-3 pr-20 text-white outline-none focus:border-brand-500 focus:ring-1 focus:ring-brand-500/30"><button type="button" @click="showPassword=!showPassword" class="absolute inset-y-0 right-0 min-w-16 px-3 text-xs font-bold text-slate-400 hover:text-white" x-text="showPassword ? 'Ocultar' : 'Mostrar'"></button></div>
                        <div class="mt-2 h-1.5 overflow-hidden rounded-full bg-white/5"><span class="block h-full rounded-full transition-all" :class="password.length >= 12 ? 'bg-emerald-400' : password.length >= 8 ? 'bg-amber-400' : 'bg-red-400'" :style="`width:${Math.min(100,password.length/12*100)}%`"></span></div>
                        @error('password')<p class="mt-2 text-sm text-red-300">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label for="password_confirmation" class="block text-sm font-medium text-slate-300 mb-2">Repite la contraseña</label>
                        <div class="relative"><input id="password_confirmation" :type="showConfirmation ? 'text' : 'password'" name="password_confirmation" required autocomplete="new-password" placeholder="Confirma tu contraseña" class="w-full rounded-xl border border-white/10 bg-white/5 px-4 py-3 pr-20 text-white outline-none focus:border-brand-500 focus:ring-1 focus:ring-brand-500/30"><button type="button" @click="showConfirmation=!showConfirmation" class="absolute inset-y-0 right-0 min-w-16 px-3 text-xs font-bold text-slate-400 hover:text-white" x-text="showConfirmation ? 'Ocultar' : 'Mostrar'"></button></div>
                    </div>

                    <x-ui.button type="submit" class="w-full" x-bind:disabled="submitting"><span x-text="submitting ? 'Creando cuenta…' : 'Crear cuenta'">Crear cuenta</span></x-ui.button>
                </form>

                <div class="mt-6 pt-6 border-t border-white/5 text-center">
                    <p class="text-sm text-slate-500">
                        Ya tienes una cuenta?
                        <a href="{{ route('login') }}" class="font-semibold text-brand-400 hover:text-brand-300 transition">Inicia sesion</a>
                    </p>
                </div>
            </div>

        </div>
    </div>

@endsection
