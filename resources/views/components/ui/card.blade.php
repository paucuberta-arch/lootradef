@props(['padding' => 'p-5 sm:p-6', 'interactive' => false])
<section {{ $attributes->class(['ui-card rounded-2xl border border-white/10', $padding, 'transition hover:-translate-y-0.5 hover:border-white/15' => $interactive]) }}>{{ $slot }}</section>
