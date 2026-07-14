<nav class="sticky top-0 z-50 bg-slate-950/80 backdrop-blur-xl border-b border-white/5" x-data="{ open: false, calcOpen: false }">

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between h-16">

            {{-- LOGO --}}
            <a href="{{ url('/') }}" class="flex items-center gap-2 group">
                <div class="w-9 h-9 rounded-lg bg-gradient-to-br from-brand-500 to-brand-700 flex items-center justify-center text-white font-black text-sm shadow-lg shadow-brand-500/25 group-hover:shadow-brand-500/40 transition-shadow">
                    L
                </div>
                <span class="text-lg font-bold text-white tracking-tight">Lootra</span>
            </a>

            {{-- BOTON MOVIL --}}
            <button
                @click="open = !open"
                class="md:hidden p-2 rounded-lg text-slate-400 hover:text-white hover:bg-white/5 transition"
            >
                <svg x-show="!open" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                </svg>
                <svg x-show="open" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>

            {{-- MENU ESCRITORIO --}}
            <div class="hidden md:flex items-center gap-1">

                {{-- Calculadora dropdown --}}
                <div class="relative" @mouseenter="calcOpen = true" @mouseleave="calcOpen = false">
                    <button
                        @click="calcOpen = !calcOpen"
                        class="flex items-center gap-1.5 px-3 py-2 rounded-lg text-sm font-medium transition
                            {{ request()->routeIs('suma','resta','multiplicacion','division') ? 'text-white bg-white/10' : 'text-slate-400 hover:text-white hover:bg-white/5' }}"
                    >
                        Calculadora
                        <svg class="w-4 h-4 transition-transform" :class="calcOpen && 'rotate-180'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </button>

                    <div
                        x-show="calcOpen"
                        x-transition:enter="transition ease-out duration-150"
                        x-transition:enter-start="opacity-0 -translate-y-1"
                        x-transition:enter-end="opacity-100 translate-y-0"
                        x-transition:leave="transition ease-in duration-100"
                        x-transition:leave-start="opacity-100 translate-y-0"
                        x-transition:leave-end="opacity-0 -translate-y-1"
                        class="absolute top-full left-0 mt-1 w-56 py-2 rounded-xl bg-slate-900 border border-white/10 shadow-2xl shadow-black/50"
                    >
                        <a href="{{ route('suma') }}"
                           class="flex items-center gap-3 px-4 py-2.5 text-sm transition
                               {{ request()->routeIs('suma') ? 'text-white bg-white/10' : 'text-slate-400 hover:text-white hover:bg-white/5' }}">
                            <span class="text-base">&#x2795;</span> Suma
                        </a>
                        <a href="{{ route('resta') }}"
                           class="flex items-center gap-3 px-4 py-2.5 text-sm transition
                               {{ request()->routeIs('resta') ? 'text-white bg-white/10' : 'text-slate-400 hover:text-white hover:bg-white/5' }}">
                            <span class="text-base">&#x2796;</span> Resta
                        </a>
                        <a href="{{ route('multiplicacion') }}"
                           class="flex items-center gap-3 px-4 py-2.5 text-sm transition
                               {{ request()->routeIs('multiplicacion') ? 'text-white bg-white/10' : 'text-slate-400 hover:text-white hover:bg-white/5' }}">
                            <span class="text-base">&#x2717;</span> Multiplicación
                        </a>
                        <a href="{{ route('division') }}"
                           class="flex items-center gap-3 px-4 py-2.5 text-sm transition
                               {{ request()->routeIs('division') ? 'text-white bg-white/10' : 'text-slate-400 hover:text-white hover:bg-white/5' }}">
                            <span class="text-base">&#x2797;</span> División
                        </a>
                    </div>
                </div>

                <a href="{{ route('hola') }}"
                   class="px-3 py-2 rounded-lg text-sm font-medium transition
                       {{ request()->routeIs('hola') ? 'text-white bg-white/10' : 'text-slate-400 hover:text-white hover:bg-white/5' }}">
                    Casino
                </a>

                <a href="{{ route('resultados.index') }}"
                   class="px-3 py-2 rounded-lg text-sm font-medium transition
                       {{ request()->routeIs('resultados.*') ? 'text-white bg-white/10' : 'text-slate-400 hover:text-white hover:bg-white/5' }}">
                    Resultados
                </a>

                @guest
                    <div class="w-px h-6 bg-white/10 mx-2"></div>
                    <a href="{{ route('login') }}"
                       class="px-3 py-2 rounded-lg text-sm font-medium text-slate-400 hover:text-white hover:bg-white/5 transition">
                        Iniciar sesión
                    </a>
                    <a href="{{ route('registro') }}"
                       class="ml-1 px-4 py-2 rounded-lg bg-brand-600 hover:bg-brand-500 text-white text-sm font-semibold transition shadow-lg shadow-brand-600/25 hover:shadow-brand-500/40">
                        Registrarse
                    </a>
                @endguest

                @auth
                    <div class="w-px h-6 bg-white/10 mx-2"></div>
                    <a href="{{ route('perfil') }}"
                       class="flex items-center gap-2 px-3 py-2 rounded-lg text-sm font-medium transition
                           {{ request()->routeIs('perfil') ? 'text-white bg-white/10' : 'text-slate-400 hover:text-white hover:bg-white/5' }}">
                        <div class="w-7 h-7 rounded-full bg-gradient-to-br from-brand-400 to-brand-600 flex items-center justify-center text-white text-xs font-bold">
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

                {{-- Calculadora --}}
                <button
                    @click="calcOpen = !calcOpen"
                    class="flex items-center justify-between px-3 py-2.5 rounded-lg text-sm font-medium transition
                        {{ request()->routeIs('suma','resta','multiplicacion','division') ? 'text-white bg-white/10' : 'text-slate-400 hover:text-white hover:bg-white/5' }}"
                >
                    Calculadora
                    <svg class="w-4 h-4 transition-transform" :class="calcOpen && 'rotate-180'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                    </svg>
                </button>

                <div x-show="calcOpen" x-collapse class="pl-4 flex flex-col gap-1">
                    <a href="{{ route('suma') }}"
                       class="px-3 py-2 rounded-lg text-sm transition
                           {{ request()->routeIs('suma') ? 'text-white bg-white/10' : 'text-slate-400 hover:text-white hover:bg-white/5' }}">
                        &#x2795; Suma
                    </a>
                    <a href="{{ route('resta') }}"
                       class="px-3 py-2 rounded-lg text-sm transition
                           {{ request()->routeIs('resta') ? 'text-white bg-white/10' : 'text-slate-400 hover:text-white hover:bg-white/5' }}">
                        &#x2796; Resta
                    </a>
                    <a href="{{ route('multiplicacion') }}"
                       class="px-3 py-2 rounded-lg text-sm transition
                           {{ request()->routeIs('multiplicacion') ? 'text-white bg-white/10' : 'text-slate-400 hover:text-white hover:bg-white/5' }}">
                        &#x2717; Multiplicación
                    </a>
                    <a href="{{ route('division') }}"
                       class="px-3 py-2 rounded-lg text-sm transition
                           {{ request()->routeIs('division') ? 'text-white bg-white/10' : 'text-slate-400 hover:text-white hover:bg-white/5' }}">
                        &#x2797; División
                    </a>
                </div>

                <a href="{{ route('hola') }}"
                   class="px-3 py-2.5 rounded-lg text-sm font-medium transition
                       {{ request()->routeIs('hola') ? 'text-white bg-white/10' : 'text-slate-400 hover:text-white hover:bg-white/5' }}">
                    Casino
                </a>

                <a href="{{ route('resultados.index') }}"
                   class="px-3 py-2.5 rounded-lg text-sm font-medium transition
                       {{ request()->routeIs('resultados.*') ? 'text-white bg-white/10' : 'text-slate-400 hover:text-white hover:bg-white/5' }}">
                    Resultados
                </a>

                @guest
                    <div class="h-px bg-white/5 my-2"></div>
                    <a href="{{ route('login') }}"
                       class="px-3 py-2.5 rounded-lg text-sm font-medium text-slate-400 hover:text-white hover:bg-white/5 transition">
                        Iniciar sesión
                    </a>
                    <a href="{{ route('registro') }}"
                       class="mt-1 px-4 py-2.5 rounded-lg bg-brand-600 hover:bg-brand-500 text-white text-sm font-semibold text-center transition">
                        Registrarse
                    </a>
                @endguest

                @auth
                    <div class="h-px bg-white/5 my-2"></div>
                    <a href="{{ route('perfil') }}"
                       class="flex items-center gap-2 px-3 py-2.5 rounded-lg text-sm font-medium transition
                           {{ request()->routeIs('perfil') ? 'text-white bg-white/10' : 'text-slate-400 hover:text-white hover:bg-white/5' }}">
                        <div class="w-7 h-7 rounded-full bg-gradient-to-br from-brand-400 to-brand-600 flex items-center justify-center text-white text-xs font-bold">
                            {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                        </div>
                        {{ auth()->user()->name }}
                    </a>
                    <form action="{{ route('logout') }}" method="POST">
                        @csrf
                        <button type="submit" class="w-full text-left px-3 py-2.5 rounded-lg text-sm font-medium text-slate-500 hover:text-red-400 hover:bg-red-500/10 transition">
                            Cerrar sesión
                        </button>
                    </form>
                @endauth
            </div>
        </div>

    </div>

    <script defer src="https://cdn.jsdelivr.net/npm/@alpinejs/collapse@3.x.x/dist/cdn.min.js"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</nav>
