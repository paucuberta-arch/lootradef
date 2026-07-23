@extends('layouts.app')

@section('contenido')
<main class="mx-auto max-w-xl px-4 py-12">
    <div class="rounded-2xl border border-white/10 bg-white/5 p-6 shadow-xl">
        <p class="text-xs font-black uppercase tracking-[.2em] text-brand-300">Seguridad administrativa</p>
        <h1 class="mt-2 text-2xl font-black text-white">Configura la autenticación MFA</h1>
        <p class="mt-3 text-sm text-slate-300">Añade esta cuenta a una aplicación TOTP. No compartas la URI de configuración.</p>
        <label class="mt-6 block text-xs font-bold uppercase tracking-wider text-slate-400">URI de configuración</label>
        <textarea readonly rows="4" class="mt-2 w-full rounded-xl border border-white/10 bg-black/30 p-3 text-xs text-slate-200">{{ $provisioningUri }}</textarea>
        <form method="POST" action="{{ route('admin.mfa.enable') }}" class="mt-6 space-y-4">
            @csrf
            <label for="code" class="block text-sm font-bold text-white">Código de 6 dígitos</label>
            <input id="code" name="code" inputmode="numeric" autocomplete="one-time-code" required maxlength="6" pattern="[0-9]{6}" class="w-full rounded-xl border border-white/10 bg-black/30 px-4 py-3 text-white">
            <label for="password" class="block text-sm font-bold text-white">Contraseña actual</label>
            <input id="password" name="password" type="password" autocomplete="current-password" required class="w-full rounded-xl border border-white/10 bg-black/30 px-4 py-3 text-white">
            @error('code')<p class="text-sm text-red-300">{{ $message }}</p>@enderror
            <button class="w-full rounded-xl bg-brand-400 px-4 py-3 font-black text-slate-950">Activar MFA</button>
        </form>
    </div>
</main>
@endsection
