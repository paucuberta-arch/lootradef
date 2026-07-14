@extends('layouts.app')
@section('title', 'Feedback — Lootra Casino')

@section('contenido')
<div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8 py-10">

    <h1 class="text-3xl font-extrabold text-white mb-2">Enviar feedback</h1>
    <p class="text-slate-500 mb-8">Tu opinion nos ayuda a mejorar. Reporta bugs, sugiere mejoras o danos tu opinion.</p>

    <form method="POST" action="{{ route('feedback.store') }}" class="space-y-5">
        @csrf

        <div class="grid grid-cols-2 gap-4">
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

        <button type="submit" class="px-6 py-3 rounded-xl bg-brand-500 hover:bg-brand-400 text-black font-bold transition shadow-lg shadow-brand-500/20">
            Enviar feedback
        </button>
    </form>
</div>
@endsection
