@props(['eyebrow' => null, 'title', 'description' => null, 'balance' => null])
<header {{ $attributes->class('mb-6 flex flex-wrap items-start justify-between gap-4') }}>
    <div>@if($eyebrow)<p class="text-xs font-black uppercase tracking-[.2em] text-brand-300">{{ $eyebrow }}</p>@endif<h1 class="game-heading mt-1 font-black">{{ $title }}</h1>@if($description)<p class="mt-2 max-w-2xl text-sm text-slate-400">{{ $description }}</p>@endif</div>
    @if(!is_null($balance))<x-ui.balance-chip :value="$balance" />@endif
</header>
