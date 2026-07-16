@props(['value' => 0, 'label' => 'Saldo'])
<div {{ $attributes->class('balance-chip inline-flex min-h-11 items-center gap-2 rounded-xl px-4 py-2 text-sm') }} aria-live="polite">
    <span class="text-slate-400">{{ $label }}</span>
    <strong class="text-brand-300" x-text="$store.wallet.saldo.toLocaleString('es-ES',{style:'currency',currency:'EUR'})">{{ number_format((float) $value, 2, ',', '.') }} €</strong>
</div>
