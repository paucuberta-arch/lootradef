@extends('layouts.auth')

@section('title', 'Restablecer contraseña — Lootra Casino')

@section('contenido')
<div class="relative min-h-screen flex items-center justify-center px-4 py-8">
    <div class="w-full max-w-lg rounded-3xl border border-white/10 bg-[#080a16]/90 p-6 shadow-2xl sm:p-10">
        <h1 class="text-3xl font-bold text-white">Nueva contraseña</h1>
        <p class="mt-3 text-sm leading-6 text-slate-400">El enlace solo puede utilizarse una vez.</p>

        @if($errors->any())
            <x-ui.alert type="error" class="mt-6">
                @foreach($errors->all() as $error)<p>{{ $error }}</p>@endforeach
            </x-ui.alert>
        @endif

        <form action="{{ route('password.update') }}" method="POST" class="mt-8 space-y-5">
            @csrf
            <input type="hidden" name="token" value="{{ $token }}">
            <div>
                <label for="email" class="mb-2 block text-sm font-medium text-slate-300">Correo electrónico</label>
                <input id="email" type="email" name="email" value="{{ old('email', $email) }}" required autocomplete="email"
                    class="w-full rounded-xl border border-white/10 bg-white/5 px-4 py-3 text-white outline-none focus:border-brand-500">
            </div>
            <div>
                <label for="password" class="mb-2 block text-sm font-medium text-slate-300">Nueva contraseña</label>
                <input id="password" type="password" name="password" required autocomplete="new-password"
                    class="w-full rounded-xl border border-white/10 bg-white/5 px-4 py-3 text-white outline-none focus:border-brand-500">
            </div>
            <div>
                <label for="password_confirmation" class="mb-2 block text-sm font-medium text-slate-300">Repetir contraseña</label>
                <input id="password_confirmation" type="password" name="password_confirmation" required autocomplete="new-password"
                    class="w-full rounded-xl border border-white/10 bg-white/5 px-4 py-3 text-white outline-none focus:border-brand-500">
            </div>
            <x-ui.button type="submit" class="w-full">Restablecer contraseña</x-ui.button>
        </form>
    </div>
</div>
@endsection
