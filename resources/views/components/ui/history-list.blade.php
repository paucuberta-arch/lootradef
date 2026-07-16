@props(['items', 'empty' => 'Todavía no hay actividad.', 'title' => 'Historial'])
<x-ui.card {{ $attributes }}>
    <h2 class="mb-4 font-display text-lg font-bold">{{ $title }}</h2>
    <div class="space-y-2">
        @forelse($items as $item)
            <div class="rounded-xl border border-white/5 bg-black/15 p-3">{{ $item }}</div>
        @empty
            <p class="rounded-xl border border-dashed border-white/10 py-7 text-center text-sm text-slate-500">{{ $empty }}</p>
        @endforelse
    </div>
</x-ui.card>
