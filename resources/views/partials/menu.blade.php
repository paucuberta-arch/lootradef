@php
    $navLink = static fn (bool $active) => $active
        ? 'border-brand-400/25 bg-brand-400/10 text-brand-300'
        : 'border-transparent text-slate-400 hover:border-white/10 hover:bg-white/5 hover:text-white';
    $categories = ['Slots' => 'slots', 'Ruleta' => 'ruleta', 'Blackjack' => 'blackjack', 'Póker' => 'poker', 'Live Casino' => 'live', 'Crash' => 'crash', 'Originales' => 'arcade'];
@endphp

<div class="contents" x-data="navbar()" @keydown.escape.window="closeAll()">
<nav class="premium-nav sticky top-0 z-[300] border-b border-white/5 bg-[#080d18]/85 backdrop-blur-2xl">
    <div class="mx-auto max-w-[1400px] px-4 sm:px-6 lg:px-8">
        <div class="flex h-16 items-center justify-between">
            <a href="{{ route('inicio') }}" class="group flex shrink-0 items-center gap-2.5" aria-label="Lootra Casino, inicio">
                <div class="brand-gem grid h-10 w-10 place-items-center rounded-xl bg-gradient-to-br from-brand-300 via-brand-400 to-emerald-400 text-sm font-black text-black shadow-lg shadow-brand-500/20"><span>L</span></div>
                <span class="hidden font-display text-lg font-extrabold tracking-tight sm:block">Lootra<span class="text-brand-300">Casino</span></span>
            </a>

            <div class="hidden items-center gap-1 lg:flex">
                <div class="relative" @mouseenter="catOpen=true" @mouseleave="catOpen=false">
                    <button @click="catOpen=!catOpen" class="flex min-h-11 items-center gap-1.5 rounded-xl border px-3 text-sm font-medium {{ $navLink(request()->routeIs('games.*')) }}" :aria-expanded="catOpen.toString()">
                        Juegos
                        <svg class="h-4 w-4 transition" :class="catOpen&&'rotate-180'" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m19 9-7 7-7-7"/></svg>
                    </button>
                    <div x-show="catOpen" x-cloak x-transition.origin.top.left @click.outside="catOpen=false" class="absolute left-0 top-full mt-2 w-56 rounded-2xl border border-white/10 bg-[#0f1626] p-2 shadow-2xl">
                        <a href="{{ route('games.index') }}" class="block rounded-xl px-3 py-2.5 text-sm text-slate-300 hover:bg-white/5 hover:text-white">Todos los juegos</a>
                        @foreach($categories as $label => $category)
                            <a href="{{ route('games.index', ['cat' => $category]) }}" class="block rounded-xl px-3 py-2.5 text-sm text-slate-400 hover:bg-white/5 hover:text-white">{{ $label }}</a>
                        @endforeach
                    </div>
                </div>
                <a href="{{ route('sports.index') }}" class="flex min-h-11 items-center rounded-xl border px-3 text-sm font-medium {{ $navLink(request()->routeIs('sports.*')) }}">Apuestas</a>
                <a href="{{ route('cases.index') }}" class="flex min-h-11 items-center rounded-xl border px-3 text-sm font-medium {{ $navLink(request()->routeIs('cases.*') || request()->routeIs('inventory.*')) }}">Cajas</a>
                @if($rickyeditCampaignEnabled ?? false)<a href="{{ route('rickyedit.landing') }}" class="flex min-h-11 items-center rounded-xl border border-fuchsia-400/20 bg-fuchsia-400/10 px-3 text-sm font-bold text-fuchsia-200">Reto RickyEdit</a>@endif
            </div>

            <div class="hidden items-center gap-2 lg:flex">
                @guest
                    <a href="{{ route('login') }}" class="rounded-xl px-3 py-2 text-sm font-medium text-slate-300 hover:bg-white/5">Iniciar sesión</a>
                    <a href="{{ route('registro') }}" class="rounded-xl bg-gradient-to-r from-brand-300 to-brand-500 px-4 py-2.5 text-sm font-bold text-slate-950 shadow-lg shadow-brand-500/20">Crear cuenta</a>
                @else
                    @if(auth()->user()->hasAnyRole(['super_admin', 'admin', 'moderator']))
                        <a href="{{ route('admin.dashboard') }}" class="rounded-xl px-3 py-2 text-sm font-medium text-brand-300 hover:bg-brand-400/10">Admin</a>
                    @endif
                    <a href="{{ route('wallet.show') }}" class="balance-chip flex min-h-11 items-center rounded-xl px-3 text-sm" aria-label="Abrir cartera"><span class="text-slate-500">{{ ($displayBalanceKind ?? 'wallet') === 'campaign' ? 'Reto' : 'Saldo' }}</span><b class="ml-2 text-brand-300" x-text="$store.wallet.saldo.toLocaleString('es-ES',{maximumFractionDigits:2})">{{ number_format($displayBalance ?? auth()->user()->saldo, 2, ',', '.') }}</b></a>
                    <a href="{{ route('profile.show') }}" class="flex min-h-11 items-center gap-2 rounded-xl border px-2.5 text-sm {{ $navLink(request()->routeIs('profile.*') || request()->routeIs('wallet.*')) }}">
                        <span class="grid h-7 w-7 place-items-center rounded-lg bg-gradient-to-br from-brand-300 to-brand-500 text-xs font-black text-black">{{ strtoupper(substr(auth()->user()->name, 0, 1)) }}</span>
                        <span class="max-w-28 truncate">{{ auth()->user()->name }}</span>
                    </a>
                @endguest
            </div>

            <button @click="open=!open" class="relative z-[320] grid min-h-11 min-w-11 place-items-center rounded-xl text-slate-300 hover:bg-white/5 lg:hidden" :aria-expanded="open.toString()" aria-controls="mobile-navigation" aria-label="Abrir navegación">
                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path x-show="!open" stroke-linecap="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/><path x-show="open" stroke-linecap="round" stroke-width="2" d="m6 6 12 12M18 6 6 18"/></svg>
            </button>
        </div>
    </div>

</nav>

    <button x-show="open" x-cloak x-transition.opacity @click="open=false" class="fixed inset-0 top-16 z-[300] bg-black/65 backdrop-blur-sm lg:hidden" aria-label="Cerrar navegación"></button>
    <div id="mobile-navigation" x-show="open" x-cloak x-transition:enter="transition duration-200" x-transition:enter-start="translate-x-full" x-transition:leave="transition duration-150" x-transition:leave-end="translate-x-full" class="fixed bottom-0 right-0 top-16 z-[310] w-[min(92vw,24rem)] overflow-y-auto border-l border-white/10 bg-[#0f1626] p-4 shadow-2xl lg:hidden">
        <div class="space-y-1">
            <a href="{{ route('games.index') }}" class="block min-h-11 rounded-xl px-3 py-3 text-sm text-slate-200 hover:bg-white/5">Todos los juegos</a>
            <a href="{{ route('sports.index') }}" class="block min-h-11 rounded-xl px-3 py-3 text-sm text-slate-300 hover:bg-white/5">Apuestas deportivas</a>
            <a href="{{ route('cases.index') }}" class="block min-h-11 rounded-xl px-3 py-3 text-sm text-slate-300 hover:bg-white/5">Cajas e inventario</a>
            @if($rickyeditCampaignEnabled ?? false)<a href="{{ route('rickyedit.landing') }}" class="block min-h-11 rounded-xl bg-fuchsia-400/10 px-3 py-3 text-sm font-bold text-fuchsia-200">Reto RickyEdit</a>@endif
            <div class="my-3 h-px bg-white/10"></div>
            <p class="px-3 pb-1 text-[10px] font-black uppercase tracking-[.18em] text-slate-600">Categorías</p>
            <div class="grid grid-cols-2 gap-1">@foreach($categories as $label => $category)<a href="{{ route('games.index', ['cat' => $category]) }}" class="min-h-11 rounded-xl px-3 py-3 text-sm text-slate-400 hover:bg-white/5 hover:text-white">{{ $label }}</a>@endforeach</div>
            <div class="my-3 h-px bg-white/10"></div>
            @guest
                <a href="{{ route('login') }}" class="block min-h-11 rounded-xl px-3 py-3 text-center text-sm text-slate-300">Iniciar sesión</a>
                <a href="{{ route('registro') }}" class="block min-h-11 rounded-xl bg-brand-400 px-3 py-3 text-center text-sm font-bold text-black">Crear cuenta</a>
            @else
                <a href="{{ route('wallet.show') }}" class="balance-chip mb-2 flex min-h-12 items-center justify-between rounded-xl px-4"><span class="text-sm text-slate-400">Mi saldo</span><b class="text-brand-300" x-text="$store.wallet.saldo.toLocaleString('es-ES',{style:'currency',currency:'EUR'})"></b></a>
                <a href="{{ route('profile.show') }}" class="block min-h-11 rounded-xl px-3 py-3 text-sm text-slate-300 hover:bg-white/5">Mi perfil</a>
                @if(auth()->user()->hasAnyRole(['super_admin', 'admin', 'moderator']))<a href="{{ route('admin.dashboard') }}" class="block min-h-11 rounded-xl px-3 py-3 text-sm text-brand-300 hover:bg-brand-400/10">Administración</a>@endif
                <form action="{{ route('logout') }}" method="POST">@csrf<button class="min-h-11 w-full rounded-xl px-3 py-3 text-left text-sm text-red-300 hover:bg-red-500/10">Cerrar sesión</button></form>
            @endguest
        </div>
    </div>
</div>

<script>
function navbar(){return{open:false,catOpen:false,init(){this.$watch('open',value=>document.documentElement.classList.toggle('overflow-hidden',value));},closeAll(){this.open=false;this.catOpen=false;}}}
</script>
