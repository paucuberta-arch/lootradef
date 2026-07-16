@props(['name', 'title', 'closeable' => true, 'maxWidth' => 'max-w-lg'])
<div x-show="{{ $name }}" x-cloak x-transition.opacity @keydown.escape.window="@if($closeable) {{ $name }} = false @endif" class="fixed inset-0 z-[120] grid place-items-center bg-black/75 p-4 backdrop-blur-sm" role="presentation">
    @if($closeable)<button class="absolute inset-0" @click="{{ $name }} = false" aria-label="Cerrar"></button>@endif
    <section role="dialog" aria-modal="true" aria-labelledby="{{ $name }}-title" {{ $attributes->class(['relative w-full rounded-2xl border border-white/10 bg-[#0F1626] p-6 shadow-2xl', $maxWidth]) }}>
        <h2 id="{{ $name }}-title" class="font-display text-xl font-bold">{{ $title }}</h2>
        <div class="mt-4">{{ $slot }}</div>
    </section>
</div>
