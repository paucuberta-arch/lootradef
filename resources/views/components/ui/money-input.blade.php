@props(['name' => 'apuesta', 'label' => 'Importe', 'min' => '0.10', 'max' => '500', 'step' => '0.10'])
<label class="block">
    <span class="mb-2 block text-sm font-medium text-slate-300">{{ $label }}</span>
    <span class="flex min-h-11 items-center rounded-xl border border-white/10 bg-black/20 px-3 focus-within:border-cyan-400/60 focus-within:ring-1 focus-within:ring-cyan-400/20">
        <span class="text-slate-500">EUR Demo</span>
        <input type="number" name="{{ $name }}" min="{{ $min }}" max="{{ $max }}" step="{{ $step }}" {{ $attributes->class('w-full bg-transparent px-3 py-2.5 font-bold text-white outline-none') }}>
    </span>
</label>
