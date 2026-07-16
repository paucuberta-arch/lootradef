@props(['type' => 'info', 'live' => false])
@php
    $types = [
        'success' => 'border-emerald-400/25 bg-emerald-500/10 text-emerald-300',
        'error' => 'border-red-400/25 bg-red-500/10 text-red-300',
        'warning' => 'border-amber-400/25 bg-amber-500/10 text-amber-200',
        'info' => 'border-cyan-400/25 bg-cyan-500/10 text-cyan-200',
    ];
@endphp
<div {{ $attributes->class(['rounded-xl border px-4 py-3 text-sm font-medium', $types[$type] ?? $types['info']]) }} @if($live) role="status" aria-live="polite" @endif>{{ $slot }}</div>
