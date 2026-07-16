@props(['variant' => 'primary', 'loading' => false, 'disabled' => false, 'type' => 'button'])
@php
    $variants = [
        'primary' => 'bg-gradient-to-r from-brand-300 to-brand-500 text-slate-950 shadow-lg shadow-brand-500/20 hover:-translate-y-0.5 hover:shadow-brand-300/25',
        'secondary' => 'border border-white/10 bg-white/5 text-white hover:bg-white/10',
        'success' => 'bg-emerald-400 text-slate-950 shadow-lg shadow-emerald-500/15 hover:bg-emerald-300',
        'danger' => 'border border-red-400/25 bg-red-500/10 text-red-300 hover:bg-red-500/20',
        'ghost' => 'text-slate-300 hover:bg-white/5 hover:text-white',
    ];
@endphp
<button type="{{ $type }}" {{ $attributes->class(['inline-flex min-h-11 items-center justify-center gap-2 rounded-xl px-5 py-2.5 text-sm font-bold transition disabled:pointer-events-none disabled:opacity-50', $variants[$variant] ?? $variants['primary']])->merge(['disabled' => $disabled || $loading]) }} aria-busy="{{ $loading ? 'true' : 'false' }}">
    @if($loading)<span class="h-4 w-4 animate-spin rounded-full border-2 border-current border-r-transparent" aria-hidden="true"></span>@endif
    {{ $slot }}
</button>
