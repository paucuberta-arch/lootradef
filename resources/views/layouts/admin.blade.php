<!DOCTYPE html>
<html lang="es" class="scroll-smooth">
<head>
    @include('partials.google-tag')
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('admin-title', 'Admin') — Lootra Admin</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @yield('admin-styles')
</head>
<body class="min-h-screen bg-[#090D18] text-white font-sans antialiased" x-data="{ sidebar: false, confirmForm: null, confirmMessage: '', toggleSidebar(open) { this.sidebar = open; document.body.style.overflow = open ? 'hidden' : ''; this.$nextTick(() => (open ? this.$refs.sidebarClose : this.$refs.sidebarToggle)?.focus()); }, requestDelete(form, message) { this.confirmForm = form; this.confirmMessage = message; document.body.style.overflow = 'hidden'; this.$nextTick(() => this.$refs.confirmCancel?.focus()); }, closeConfirm() { this.confirmForm = null; document.body.style.overflow = ''; } }" @keydown.escape.window="confirmForm ? closeConfirm() : toggleSidebar(false)">
    <a href="#admin-content" class="sr-only focus:not-sr-only focus:fixed focus:left-4 focus:top-4 focus:z-[100] focus:rounded-lg focus:bg-brand-300 focus:px-4 focus:py-2 focus:text-slate-950">Saltar al contenido</a>

    <div class="flex min-h-screen overflow-x-clip">

        <button x-show="sidebar" x-transition.opacity @click="toggleSidebar(false)" class="fixed inset-0 z-40 bg-black/70 backdrop-blur-sm lg:hidden" aria-label="Cerrar menú"></button>

        {{-- SIDEBAR --}}
        <aside x-ref="sidebar" class="fixed inset-y-0 left-0 z-50 flex w-64 shrink-0 flex-col border-r border-white/10 bg-[#0F1626] shadow-2xl transition-transform duration-300 lg:static lg:z-auto lg:translate-x-0"
               :class="sidebar ? 'translate-x-0' : '-translate-x-full'">
            <button x-ref="sidebarClose" @click="toggleSidebar(false)" class="absolute right-3 top-3 grid h-10 w-10 place-items-center rounded-xl text-slate-400 hover:bg-white/5 lg:hidden" aria-label="Cerrar menú lateral">×</button>

            {{-- Logo --}}
            <div class="h-16 flex items-center gap-3 px-5 border-b border-white/5">
                <a href="{{ route('inicio') }}" aria-label="Lootra Casino, inicio" class="grid h-9 w-9 shrink-0 place-items-center rounded-lg bg-white/[.03]">
                    <img src="{{ asset('images/logo/lootra-mark-transparent.png') }}" alt="" class="h-8 w-8 object-contain" width="1254" height="1254" decoding="async">
                </a>
                <div>
                    <span class="text-sm font-extrabold text-white">Lootra</span>
                    <span class="text-xs font-bold text-brand-400 ml-1">Admin</span>
                </div>
            </div>

            {{-- Nav --}}
            <nav class="flex-1 py-4 px-3 space-y-1 overflow-y-auto">
                @php
                    $navItems = [
                        ['route' => 'admin.dashboard', 'icon' => 'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6', 'label' => 'Dashboard'],
                        ['route' => 'admin.users', 'icon' => 'M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z', 'label' => 'Usuarios'],
                        ['route' => 'admin.reviews', 'icon' => 'M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z', 'label' => 'Reviews'],
                        ['route' => 'admin.feedback', 'icon' => 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01', 'label' => 'Feedback'],
                    ];

                    $navItems = array_values(array_filter($navItems, fn ($item) => match ($item['route']) {
                        'admin.users' => auth()->user()->can('users.view'),
                        'admin.reviews' => auth()->user()->can('reviews.view'),
                        'admin.feedback' => auth()->user()->can('feedback.view'),
                        default => true,
                    }));

                    if (auth()->user()->can('stats.view') && auth()->user()->hasAnyRole(['super_admin', 'admin'])) {
                        array_splice($navItems, 1, 0, [[
                            'route' => 'admin.charts',
                            'icon' => 'M7 12l3-3 4 4 6-7M5 20V10m5 10V4m5 16v-7m5 7V8',
                            'label' => 'Gráficos',
                        ]]);
                    }
                    if (auth()->user()->can('roles.view')) $navItems[] = ['route' => 'admin.roles', 'icon' => 'M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z', 'label' => 'Roles'];
                    if (auth()->user()->can('logs.view')) $navItems[] = ['route' => 'admin.logs', 'icon' => 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01', 'label' => 'Activity Log'];
                    if (auth()->user()->can('case-prizes.manage')) {
                        $navItems[] = ['route' => 'admin.case-prizes.index', 'icon' => 'M20 12v8H4v-8m16 0H4m16 0-2-4H6l-2 4m8-4v12m-3-9 3 3 3-3', 'label' => 'Premios de cajas'];
                        $navItems[] = ['route' => 'admin.case-history.index', 'icon' => 'M12 8v4l3 2m6-2a9 9 0 11-18 0 9 9 0 0118 0z', 'label' => 'Historial de cajas'];
                    }
                    if (auth()->user()->can('campaigns.stats.view')) $navItems[] = ['route' => 'admin.campaigns.rickyedit', 'icon' => 'M11.049 2.927c.3-.921 1.603-.921 1.902 0l2.122 6.53a1 1 0 00.95.69h6.866c.969 0 1.371 1.24.588 1.81l-5.555 4.036a1 1 0 00-.364 1.118l2.122 6.53c.3.921-.755 1.688-1.539 1.118l-5.555-4.036a1 1 0 00-1.176 0l-5.555 4.036c-.783.57-1.838-.197-1.539-1.118l2.122-6.53a1 1 0 00-.364-1.118L.52 11.956c-.783-.57-.38-1.81.588-1.81h6.866a1 1 0 00.951-.69l2.122-6.529z', 'label' => 'Campaña RickyEdit'];
                @endphp

                @foreach($navItems as $item)
                    <a href="{{ route($item['route']) }}"
                       class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition {{ request()->routeIs($item['route']) ? 'bg-brand-500/10 text-brand-400 border border-brand-500/20' : 'text-slate-400 hover:text-white hover:bg-white/5 border border-transparent' }}">
                        <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="{{ $item['icon'] }}"/>
                        </svg>
                        {{ $item['label'] }}
                    </a>
                @endforeach
            </nav>

            {{-- User --}}
            <div class="p-3 border-t border-white/5">
                <div class="flex items-center gap-3 px-3 py-2">
                    <x-ui.user-avatar :user="auth()->user()" size="sm" />
                    <div class="min-w-0 flex-1">
                        <p class="text-sm font-semibold text-white truncate">{{ auth()->user()->name }}</p>
                        <p class="text-xs text-slate-500">{{ auth()->user()->role_badge }}</p>
                    </div>
                    <form action="{{ route('logout') }}" method="POST">
                        @csrf
                        <button type="submit" class="text-slate-500 hover:text-red-400 transition">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                        </button>
                    </form>
                </div>
            </div>
        </aside>

        {{-- MAIN --}}
        <div class="flex-1 min-w-0">

            {{-- Topbar --}}
            <header class="h-16 bg-[#0F1626]/85 backdrop-blur-xl border-b border-white/5 flex items-center justify-between px-4 sm:px-6 sticky top-0 z-30">
                <div class="flex min-w-0 items-center gap-2 sm:gap-4">
                    <button x-ref="sidebarToggle" @click="toggleSidebar(!sidebar)" class="grid min-h-11 min-w-11 place-items-center rounded-xl text-slate-400 hover:bg-white/5 hover:text-white transition lg:hidden" aria-label="Abrir menú" :aria-expanded="sidebar.toString()">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                    </button>
                    <h1 class="truncate text-base sm:text-lg font-bold text-white">@yield('admin-title', 'Dashboard')</h1>
                </div>
                <a href="{{ route('inicio') }}" class="text-sm text-slate-400 hover:text-white transition flex items-center gap-1.5">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                    <span class="hidden min-[420px]:inline">Ver web</span>
                </a>
            </header>

            {{-- Content --}}
            <main id="admin-content" tabindex="-1" class="p-4 sm:p-6 lg:p-8">
                @if(session('success'))
                    <x-ui.alert type="success" class="mb-6">{{ session('success') }}</x-ui.alert>
                @endif

                @if($errors->any())
                    <x-ui.alert type="error" class="mb-6">
                        @foreach($errors->all() as $error)
                            <div>{{ $error }}</div>
                        @endforeach
                    </x-ui.alert>
                @endif

                @yield('admin-content')
            </main>
        </div>
    </div>

    @include('partials.analytics-consent')

    <div x-show="confirmForm" x-cloak class="fixed inset-0 z-[100] grid place-items-center p-4" role="dialog" aria-modal="true" aria-labelledby="admin-confirm-title">
        <button class="absolute inset-0 bg-black/80 backdrop-blur-sm" @click="closeConfirm()" aria-label="Cancelar"></button>
        <div class="relative w-full max-w-md rounded-2xl border border-white/15 bg-[#111827] p-6 shadow-2xl">
            <p class="text-xs font-black uppercase tracking-[.2em] text-red-300">Acción irreversible</p>
            <h2 id="admin-confirm-title" class="mt-2 text-xl font-bold">Confirmar eliminación</h2>
            <p class="mt-3 text-sm text-slate-400" x-text="confirmMessage"></p>
            <div class="mt-6 grid grid-cols-2 gap-3">
                <button x-ref="confirmCancel" @click="closeConfirm()" class="min-h-11 rounded-xl border border-white/10 bg-white/5 font-bold">Cancelar</button>
                <button @click="confirmForm.submit()" class="min-h-11 rounded-xl bg-red-500 font-bold text-white">Eliminar</button>
            </div>
        </div>
    </div>

    @stack('admin-scripts')
</body>
</html>
