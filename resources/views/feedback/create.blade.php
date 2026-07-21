@extends('layouts.app')
@section('title', 'Feedback — Lootra Casino')

@section('contenido')
<div class="mx-auto max-w-6xl px-4 py-8 sm:px-6 sm:py-14 lg:px-8">
    <header class="relative mb-6 min-h-64 overflow-hidden rounded-3xl border border-white/10 sm:min-h-72">
        <img src="{{ asset('images/lootra_visual_pack/05_top_panels/panel_comunidad_1920x360.webp') }}" alt="Comunidad Lootra" class="absolute inset-0 h-full w-full object-cover" decoding="async">
        <div class="absolute inset-0 bg-gradient-to-r from-[#070816]/95 via-[#070816]/65 to-transparent"></div>
        <div class="relative z-10 max-w-xl p-6 sm:p-10">
            <span class="world-kicker">Lootra Community</span>
            <h1 class="mt-4 font-display text-3xl font-black tracking-tight text-white sm:text-5xl">Tu mirada también construye Lootra.</h1>
            <p class="mt-4 text-sm leading-relaxed text-slate-300 sm:text-base">Cuéntanos qué funciona, qué podemos pulir o qué debería existir después. Cada mensaje llega al equipo con su contexto.</p>
        </div>
    </header>

    <div class="grid gap-6 lg:grid-cols-[minmax(0,1fr)_280px]">
    <form method="POST" action="{{ route('feedback.store') }}" class="world-panel space-y-5 rounded-3xl p-5 sm:p-8">
        @csrf

        <div class="grid gap-4 sm:grid-cols-2">
            <div>
                <label class="text-xs text-slate-500 mb-1 block">Tipo</label>
                <select name="tipo" class="w-full px-4 py-3 rounded-xl bg-white/5 border border-white/10 text-white text-sm outline-none focus:border-brand-500 transition">
                    <option value="sugerencia">Sugerencia</option>
                    <option value="bug">Bug / Error</option>
                    <option value="mejora">Mejora</option>
                    <option value="otro">Otro</option>
                </select>
            </div>
            <div>
                <label class="text-xs text-slate-500 mb-1 block">Prioridad</label>
                <select name="prioridad" class="w-full px-4 py-3 rounded-xl bg-white/5 border border-white/10 text-white text-sm outline-none focus:border-brand-500 transition">
                    <option value="baja">Baja</option>
                    <option value="normal" selected>Normal</option>
                    <option value="alta">Alta</option>
                    <option value="urgente">Urgente</option>
                </select>
            </div>
        </div>

        <div>
            <label class="text-xs text-slate-500 mb-1 block">Asunto</label>
            <input type="text" name="asunto" value="{{ old('asunto') }}" required maxlength="200" placeholder="Resumen breve..."
                   class="w-full px-4 py-3 rounded-xl bg-white/5 border border-white/10 text-white text-sm placeholder-slate-600 outline-none focus:border-brand-500 transition">
        </div>

        <div>
            <label class="text-xs text-slate-500 mb-1 block">Detalle</label>
            <textarea name="contenido" rows="6" required placeholder="Describe tu feedback con detalle..."
                      class="w-full px-4 py-3 rounded-xl bg-white/5 border border-white/10 text-white text-sm placeholder-slate-600 outline-none focus:border-brand-500 transition resize-none">{{ old('contenido') }}</textarea>
        </div>

        <button type="submit" class="cta-shine w-full rounded-xl bg-gradient-to-r from-cyan-400 via-brand-400 to-fuchsia-500 px-6 py-3 text-black font-black transition shadow-lg shadow-brand-500/20 sm:w-auto">
            Enviar feedback
        </button>
    </form>
    <aside class="world-panel h-fit rounded-3xl p-6">
        <span class="world-kicker">Una buena señal</span>
        <h2 class="mt-3 text-xl font-bold">Cuanto más concreto, mejor.</h2>
        <div class="mt-5 space-y-4 text-sm text-slate-400">
            <p class="border-l-2 border-cyan-300/60 pl-3">Indica en qué pantalla ocurrió.</p>
            <p class="border-l-2 border-fuchsia-300/60 pl-3">Explica qué esperabas que pasara.</p>
            <p class="border-l-2 border-amber-300/60 pl-3">Si es un error, cuenta cómo repetirlo.</p>
        </div>
    </aside>
    </div>
</div>
@endsection
