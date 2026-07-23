@extends('layouts.auth')

@section('title', 'Recuperar contraseña — Lootra Casino')

@section('contenido')
<div class="relative min-h-screen flex items-center justify-center px-4 py-8">
    <div class="w-full max-w-lg rounded-3xl border border-white/10 bg-[#080a16]/90 p-6 shadow-2xl sm:p-10">
        <a href="{{ route('login') }}" class="text-sm text-brand-400 hover:text-brand-300">← Volver a iniciar sesión</a>
        <h1 class="mt-8 text-3xl font-bold text-white">Recupera tu contraseña</h1>
        <p class="mt-3 text-sm leading-6 text-slate-400">Te enviaremos un enlace de un solo uso y con caducidad limitada.</p>

        @if(session('status'))
            <x-ui.alert type="success" class="mt-6">{{ session('status') }}</x-ui.alert>
        @endif
        @if($errors->any())
            <x-ui.alert type="error" class="mt-6">
                @foreach($errors->all() as $error)<p>{{ $error }}</p>@endforeach
            </x-ui.alert>
        @endif

        <form action="{{ route('password.email') }}" method="POST" class="mt-8 space-y-5">
            @csrf
            <div>
                <label for="email" class="mb-2 block text-sm font-medium text-slate-300">Correo electrónico</label>
                <input id="email" type="email" name="email" value="{{ old('email') }}" required autocomplete="email"
                    class="w-full rounded-xl border border-white/10 bg-white/5 px-4 py-3 text-white outline-none focus:border-brand-500">
                @error('email')<p class="mt-2 text-sm text-red-300">{{ $message }}</p>@enderror
            </div>
            <x-ui.button type="submit" class="w-full">Enviar enlace</x-ui.button>
        </form>
    </div>
</div>
@endsection
