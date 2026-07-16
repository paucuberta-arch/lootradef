@extends('layouts.admin')
@section('admin-title', 'Feedback')

@section('admin-content')
<div class="flex flex-wrap gap-2 mb-6">
    <a href="{{ route('admin.feedback') }}" class="px-3 py-1.5 rounded-lg text-xs font-medium {{ !request('estado') ? 'bg-brand-500/10 text-brand-400 border border-brand-500/20' : 'text-slate-400 bg-white/5 border border-white/5' }}">Todos ({{ $counts['total'] }})</a>
    <a href="{{ route('admin.feedback', ['estado' => 'abierto']) }}" class="px-3 py-1.5 rounded-lg text-xs font-medium {{ request('estado') === 'abierto' ? 'bg-blue-500/10 text-blue-400 border border-blue-500/20' : 'text-slate-400 bg-white/5 border border-white/5' }}">Abiertos ({{ $counts['abierto'] }})</a>
    <a href="{{ route('admin.feedback', ['estado' => 'en_progreso']) }}" class="px-3 py-1.5 rounded-lg text-xs font-medium {{ request('estado') === 'en_progreso' ? 'bg-amber-500/10 text-amber-400 border border-amber-500/20' : 'text-slate-400 bg-white/5 border border-white/5' }}">En progreso ({{ $counts['en_progreso'] }})</a>
    <a href="{{ route('admin.feedback', ['estado' => 'resuelto']) }}" class="px-3 py-1.5 rounded-lg text-xs font-medium {{ request('estado') === 'resuelto' ? 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/20' : 'text-slate-400 bg-white/5 border border-white/5' }}">Resueltos ({{ $counts['resuelto'] }})</a>
    <a href="{{ route('admin.feedback', ['estado' => 'cerrado']) }}" class="px-3 py-1.5 rounded-lg text-xs font-medium {{ request('estado') === 'cerrado' ? 'bg-slate-500/10 text-slate-400 border border-slate-500/20' : 'text-slate-400 bg-white/5 border border-white/5' }}">Cerrados ({{ $counts['cerrado'] }})</a>
</div>

<div class="space-y-4">
    @forelse($feedback as $fb)
        <div class="rounded-2xl bg-white/[0.03] border border-white/5 p-4 sm:p-5">
            <div class="flex flex-col md:flex-row md:items-start justify-between gap-4">
                <div class="flex-1 min-w-0">
                    <div class="flex flex-wrap items-center gap-2 mb-2">
                        @php
                            $tipoIcons = ['sugerencia' => '💡', 'bug' => '🐛', 'mejora' => '⚡', 'otro' => '📝'];
                            $prioridadColors = ['baja' => 'slate', 'normal' => 'blue', 'alta' => 'amber', 'urgente' => 'red'];
                            $estadoColors = ['abierto' => 'blue', 'en_progreso' => 'amber', 'resuelto' => 'emerald', 'cerrado' => 'slate'];
                        @endphp
                        <span class="text-lg">{{ $tipoIcons[$fb->tipo] ?? '📝' }}</span>
                        <h3 class="min-w-0 break-words font-bold text-white">{{ $fb->asunto }}</h3>
                        <span class="tone-badge tone-{{ $prioridadColors[$fb->prioridad] }} px-2 py-0.5 rounded-lg text-[10px] font-bold uppercase">{{ $fb->prioridad }}</span>
                        <span class="tone-badge tone-{{ $estadoColors[$fb->estado] }} px-2 py-0.5 rounded-lg text-[10px] font-bold uppercase">{{ str_replace('_', ' ', $fb->estado) }}</span>
                    </div>
                    <p class="text-sm text-slate-400 mb-2">{{ $fb->contenido }}</p>
                    <div class="flex flex-wrap items-center gap-3 text-xs text-slate-600">
                        <span>{{ $fb->usuario?->name ?? 'Anonimo' }}</span>
                        <span>{{ $fb->created_at->diffForHumans() }}</span>
                    </div>

                    @if($fb->respuesta)
                        <div class="mt-3 p-3 rounded-xl bg-brand-500/5 border border-brand-500/10">
                            <p class="text-xs text-brand-400 font-semibold mb-1">Respuesta admin:</p>
                            <p class="text-sm text-slate-300">{{ $fb->respuesta }}</p>
                        </div>
                    @endif
                </div>

                <div class="w-full shrink-0 md:w-auto">
                    <form method="POST" action="{{ route('admin.feedback.update', $fb) }}" class="space-y-2" x-data="{ open: false }">
                        @csrf @method('PUT')
                        <button type="button" @click="open = !open" class="text-xs text-brand-400 hover:text-brand-300 transition">Responder</button>
                        <div x-show="open" x-transition class="space-y-2 mt-2">
                            <select name="estado" class="w-full px-3 py-2 rounded-xl bg-white/5 border border-white/10 text-xs text-white outline-none focus:border-brand-500">
                                <option value="abierto" {{ $fb->estado === 'abierto' ? 'selected' : '' }}>Abierto</option>
                                <option value="en_progreso" {{ $fb->estado === 'en_progreso' ? 'selected' : '' }}>En progreso</option>
                                <option value="resuelto" {{ $fb->estado === 'resuelto' ? 'selected' : '' }}>Resuelto</option>
                                <option value="cerrado" {{ $fb->estado === 'cerrado' ? 'selected' : '' }}>Cerrado</option>
                            </select>
                            <select name="prioridad" class="w-full px-3 py-2 rounded-xl bg-white/5 border border-white/10 text-xs text-white outline-none focus:border-brand-500">
                                <option value="baja" {{ $fb->prioridad === 'baja' ? 'selected' : '' }}>Baja</option>
                                <option value="normal" {{ $fb->prioridad === 'normal' ? 'selected' : '' }}>Normal</option>
                                <option value="alta" {{ $fb->prioridad === 'alta' ? 'selected' : '' }}>Alta</option>
                                <option value="urgente" {{ $fb->prioridad === 'urgente' ? 'selected' : '' }}>Urgente</option>
                            </select>
                            <textarea name="respuesta" rows="3" placeholder="Escribe una respuesta..."
                                      class="w-full px-3 py-2 rounded-xl bg-white/5 border border-white/10 text-xs text-white placeholder-slate-600 outline-none focus:border-brand-500">{{ $fb->respuesta }}</textarea>
                            <button class="px-4 py-2 rounded-xl bg-brand-500 hover:bg-brand-400 text-black text-xs font-bold transition">Guardar</button>
                        </div>
                    </form>
                    <form method="POST" action="{{ route('admin.feedback.destroy', $fb) }}" class="mt-2" @submit.prevent="requestDelete($el, 'Se eliminará este feedback de forma permanente.')">
                        @csrf @method('DELETE')
                        <button class="text-xs text-red-400 hover:text-red-300">Eliminar</button>
                    </form>
                </div>
            </div>
        </div>
    @empty
        <div class="text-center py-20 text-slate-600">Sin feedback.</div>
    @endforelse
</div>

<div class="mt-6">{{ $feedback->withQueryString()->links() }}</div>
@endsection
