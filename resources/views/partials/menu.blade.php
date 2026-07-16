<nav class="premium-nav sticky top-0 z-50 bg-[#080814]/75 backdrop-blur-2xl border-b border-white/5" x-data="navbar()">
    <div class="max-w-[1400px] mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between h-16">

            {{-- LOGO --}}
            <a href="{{ route('inicio') }}" class="flex items-center gap-2.5 group shrink-0">
                <div class="brand-gem w-10 h-10 rounded-xl bg-gradient-to-br from-fuchsia-400 via-brand-400 to-cyan-400 flex items-center justify-center text-black font-black text-sm shadow-lg shadow-brand-500/20">
                    <span>L</span>
                </div>
                <span class="font-display text-lg font-extrabold tracking-tight hidden sm:block">
                    <span class="text-white">Lootra</span>
                    <span class="text-brand-400">Casino</span>
                </span>
            </a>

            {{-- NAV LINKS DESKTOP --}}
            <div class="hidden md:flex items-center gap-1">

                {{-- Categorias dropdown --}}
                <div class="relative" @mouseenter="catOpen = true" @mouseleave="catOpen = false">
                    <button
                        @click="catOpen = !catOpen"
                        class="flex items-center gap-1.5 px-3 py-2 rounded-lg text-sm font-medium text-slate-400 hover:text-white hover:bg-white/5 transition"
                    >
                        Categorias
                        <svg class="w-4 h-4 transition-transform" :class="catOpen && 'rotate-180'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </button>
                    <div
                        x-show="catOpen"
                        x-transition:enter="transition ease-out duration-150"
                        x-transition:enter-start="opacity-0 -translate-y-1"
                        x-transition:enter-end="opacity-100 translate-y-0"
                        x-transition:leave="transition ease-in duration-100"
                        x-transition:leave-start="opacity-100 translate-y-0"
                        x-transition:leave-end="opacity-0 -translate-y-1"
                        class="absolute top-full left-0 mt-1 w-52 py-2 rounded-xl bg-[#14142a] border border-white/10 shadow-2xl shadow-black/60"
                    >
                        @foreach(['Slots' => 'slots', 'Ruleta' => 'ruleta', 'Blackjack' => 'blackjack', 'Poker' => 'poker', 'Live Casino' => 'live', 'Crash' => 'crash', 'Originales' => 'arcade'] as $label => $catId)
                            <a href="{{ route('games.index', ['cat' => $catId]) }}" class="flex items-center gap-2.5 px-4 py-2.5 text-sm text-slate-400 hover:text-white hover:bg-white/5 transition">
                                {{ $label }}
                            </a>
                        @endforeach
                    </div>
                </div>

                <a href="{{ route('sports.index') }}" class="px-3 py-2 rounded-lg text-sm font-medium text-slate-400 hover:text-white hover:bg-white/5 transition">
                    Apuestas
                </a>
                <a href="{{ route('cases.index') }}" class="px-3 py-2 rounded-lg text-sm font-medium text-slate-400 hover:text-white hover:bg-white/5 transition">
                    Cajas
                </a>

                <div class="w-px h-6 bg-white/10 mx-2"></div>

                @guest
                    <a href="{{ route('login') }}" class="px-3 py-2 rounded-lg text-sm font-medium text-slate-400 hover:text-white hover:bg-white/5 transition">
                        Iniciar sesion
                    </a>
                    <a href="{{ route('registro') }}" class="ml-1 px-4 py-2 rounded-lg bg-brand-500 hover:bg-brand-400 text-black text-sm font-bold transition shadow-lg shadow-brand-500/20 hover:shadow-brand-400/30">
                        Registrarse
                    </a>
                @endguest

                @auth
                    @if(auth()->user()->hasAnyRole(['super_admin', 'admin', 'moderator']))
                        <a href="{{ route('admin.dashboard') }}" class="px-3 py-2 rounded-lg text-sm font-medium text-brand-400 hover:text-brand-300 hover:bg-brand-500/5 transition">
                            Admin
                        </a>
                    @endif

                    {{-- Saldo + deposit dropdown --}}
                    <div class="relative" @mouseenter="depositOpen = true" @mouseleave="depositOpen = false">
                        <div class="flex items-center gap-1">
                            <div class="px-3 py-1.5 rounded-lg bg-brand-500/10 border border-brand-500/20 text-sm font-bold text-brand-400 cursor-default">
                                €<span x-text="$store.wallet.saldo.toFixed(2)">{{ number_format(auth()->user()->saldo, 2) }}</span>
                            </div>
                            <button @click="depositOpen = !depositOpen" class="px-2 py-1.5 rounded-lg bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 hover:bg-emerald-500/20 transition text-sm font-bold" title="Añadir saldo demo">
                                +
                            </button>
                        </div>

                        {{-- Dropdown deposito rapido --}}
                        <div
                            x-show="depositOpen"
                            x-transition:enter="transition ease-out duration-150"
                            x-transition:enter-start="opacity-0 -translate-y-1 scale-95"
                            x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                            x-transition:leave="transition ease-in duration-100"
                            x-transition:leave-start="opacity-100 translate-y-0 scale-100"
                            x-transition:leave-end="opacity-0 -translate-y-1 scale-95"
                            @click.outside="depositOpen = false"
                            class="absolute top-full right-0 mt-2 w-56 p-3 rounded-xl bg-[#14142a] border border-white/10 shadow-2xl shadow-black/60 z-50"
                        >
                            <p class="text-xs text-slate-500 font-medium mb-2">Añadir saldo demo</p>
                            <div class="grid grid-cols-3 gap-2 mb-2">
                                @foreach([10, 25, 50, 100, 250, 500] as $amt)
                                    <button @click="quickDeposit({{ $amt }})"
                                            :disabled="depositing"
                                            class="py-2 rounded-lg bg-white/5 border border-white/10 text-sm font-bold text-slate-300 hover:text-emerald-400 hover:border-emerald-500/30 hover:bg-emerald-500/10 transition disabled:opacity-50"
                                            x-text="'€{{ $amt }}'">
                                    </button>
                                @endforeach
                            </div>
                            <div class="flex gap-2">
                                <input x-model.number="customAmt" type="number" min="1" max="50000"
                                       class="flex-1 px-3 py-2 rounded-lg bg-white/5 border border-white/10 text-white text-sm outline-none focus:border-emerald-500 transition">
                                <button @click="quickDeposit(customAmt)"
                                        :disabled="depositing || customAmt < 1"
                                        class="px-3 py-2 rounded-lg bg-emerald-500 hover:bg-emerald-400 text-black text-sm font-bold transition disabled:opacity-50">
                                    OK
                                </button>
                            </div>
                        </div>
                    </div>

                    <a href="{{ route('profile.show') }}" class="flex items-center gap-2 px-3 py-2 rounded-lg text-sm font-medium text-slate-400 hover:text-white hover:bg-white/5 transition">
                        <div class="w-7 h-7 rounded-full bg-gradient-to-br from-brand-400 to-brand-600 flex items-center justify-center text-black text-xs font-bold">
                            {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                        </div>
                        {{ auth()->user()->name }}
                    </a>
                    <form action="{{ route('logout') }}" method="POST" class="ml-1">
                        @csrf
                        <button type="submit" class="px-3 py-2 rounded-lg text-sm font-medium text-slate-500 hover:text-red-400 hover:bg-red-500/10 transition">
                            Salir
                        </button>
                    </form>
                @endauth
            </div>

            {{-- BOTON MOVIL --}}
            <button @click="open = !open" class="md:hidden p-2 rounded-lg text-slate-400 hover:text-white hover:bg-white/5 transition">
                <svg x-show="!open" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                </svg>
                <svg x-show="open" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>

        </div>

        {{-- MENU MOVIL --}}
        <div
            x-show="open"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 -translate-y-2"
            x-transition:enter-end="opacity-100 translate-y-0"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100 translate-y-0"
            x-transition:leave-end="opacity-0 -translate-y-2"
            class="md:hidden pb-4 border-t border-white/5 mt-2 pt-4"
        >
            <div class="flex flex-col gap-1">
                <a href="{{ route('inicio') }}" class="px-3 py-2.5 rounded-lg text-sm text-slate-400 hover:text-white hover:bg-white/5 transition">Inicio</a>
                <a href="{{ route('sports.index') }}" class="px-3 py-2.5 rounded-lg text-sm text-slate-400 hover:text-white hover:bg-white/5 transition">Apuestas Deportivas</a>
                <a href="{{ route('cases.index') }}" class="px-3 py-2.5 rounded-lg text-sm text-slate-400 hover:text-white hover:bg-white/5 transition">Cajas de Azar</a>

                <div class="h-px bg-white/5 my-2"></div>

                @foreach(['Slots' => 'slots', 'Ruleta' => 'ruleta', 'Blackjack' => 'blackjack', 'Poker' => 'poker', 'Live Casino' => 'live', 'Originales' => 'arcade'] as $label => $catId)
                    <a href="{{ route('games.index', ['cat' => $catId]) }}" class="px-3 py-2.5 rounded-lg text-sm text-slate-400 hover:text-white hover:bg-white/5 transition">{{ $label }}</a>
                @endforeach

                <div class="h-px bg-white/5 my-2"></div>

                @guest
                    <a href="{{ route('login') }}" class="px-3 py-2.5 rounded-lg text-sm font-medium text-slate-400 hover:text-white hover:bg-white/5 transition">Iniciar sesion</a>
                    <a href="{{ route('registro') }}" class="mt-1 px-4 py-2.5 rounded-lg bg-brand-500 hover:bg-brand-400 text-black text-sm font-bold text-center transition">Registrarse</a>
                @endguest

                @auth
                    @if(auth()->user()->hasAnyRole(['super_admin', 'admin', 'moderator']))
                        <a href="{{ route('admin.dashboard') }}" class="px-3 py-2.5 rounded-lg text-sm font-medium text-brand-400 hover:text-brand-300 hover:bg-brand-500/5 transition">Panel Admin</a>
                    @endif

                    <div class="flex items-center gap-2 px-3 py-2 rounded-lg bg-brand-500/10 border border-brand-500/20 text-sm font-bold text-brand-400">
                        <span>Saldo: €<span x-text="$store.wallet.saldo.toFixed(2)">{{ number_format(auth()->user()->saldo, 2) }}</span></span>
                        <button @click="depositOpen = !depositOpen" class="px-2 py-0.5 rounded bg-emerald-500/20 border border-emerald-500/30 text-emerald-400 text-xs font-bold" aria-label="Añadir saldo demo">+</button>
                    </div>

                    {{-- Dropdown deposito movil --}}
                    <div x-show="depositOpen" x-cloak class="px-3 py-2">
                        <p class="mb-2 text-xs font-medium text-slate-500">Añadir saldo demo</p>
                        <div class="grid grid-cols-3 gap-2 mb-2">
                            @foreach([10, 25, 50, 100, 250, 500] as $amt)
                                <button @click="quickDeposit({{ $amt }})"
                                        :disabled="depositing"
                                        class="py-2 rounded-lg bg-white/5 border border-white/10 text-sm font-bold text-slate-300 hover:text-emerald-400 hover:border-emerald-500/30 hover:bg-emerald-500/10 transition disabled:opacity-50"
                                        x-text="'€{{ $amt }}'">
                                </button>
                            @endforeach
                        </div>
                        <div class="flex gap-2">
                            <input x-model.number="customAmt" type="number" min="1" max="50000"
                                   class="flex-1 px-3 py-2 rounded-lg bg-white/5 border border-white/10 text-white text-sm outline-none focus:border-emerald-500 transition">
                            <button @click="quickDeposit(customAmt)"
                                    :disabled="depositing || customAmt < 1"
                                    class="px-3 py-2 rounded-lg bg-emerald-500 hover:bg-emerald-400 text-black text-sm font-bold transition disabled:opacity-50">
                                OK
                            </button>
                        </div>
                    </div>

                    <a href="{{ route('profile.show') }}" class="flex items-center gap-2 px-3 py-2.5 rounded-lg text-sm font-medium text-slate-400 hover:text-white hover:bg-white/5 transition">
                        <div class="w-7 h-7 rounded-full bg-gradient-to-br from-brand-400 to-brand-600 flex items-center justify-center text-black text-xs font-bold">
                            {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                        </div>
                        {{ auth()->user()->name }}
                    </a>
                    <form action="{{ route('logout') }}" method="POST">
                        @csrf
                        <button type="submit" class="w-full text-left px-3 py-2.5 rounded-lg text-sm font-medium text-slate-500 hover:text-red-400 hover:bg-red-500/10 transition">Cerrar sesion</button>
                    </form>
                @endauth
            </div>
        </div>
    </div>

</nav>

<script>
function navbar() {
    return {
        open: false,
        catOpen: false,
        depositOpen: false,
        depositing: false,
        customAmt: 100,

        init() {
            window.addEventListener('saldo-updated', function(e) {
                Alpine.store('wallet').saldo = e.detail.saldo;
            });
        },

        quickDeposit: function(amount) {
            if (this.depositing || amount < 1) return;
            var self = this;
            this.depositing = true;

            var csrfToken = document.querySelector('meta[name="csrf-token"]').content;

            fetch('{{ route("wallet.demo-deposit") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ amount: amount })
            })
            .then(function(res) { return res.json(); })
            .then(function(data) {
                if (data.ok) {
                    Alpine.store('wallet').saldo = data.saldo;
                    window.dispatchEvent(new CustomEvent('saldo-updated', { detail: { saldo: data.saldo } }));
                }
                self.depositing = false;
                self.depositOpen = false;
            })
            .catch(function() {
                self.depositing = false;
            });
        }
    };
}
</script>
