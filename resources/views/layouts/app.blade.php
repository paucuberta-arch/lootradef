<!DOCTYPE html>
<html lang="es" class="scroll-smooth">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Lootra Casino')</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Space+Grotesk:wght@500;600;700&display=swap" rel="stylesheet">

    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Inter', 'system-ui', 'sans-serif'],
                        display: ['Space Grotesk', 'Inter', 'sans-serif'],
                    },
                    colors: {
                        brand: {
                            50: '#fffbeb',
                            100: '#fef3c7',
                            200: '#fde68a',
                            300: '#fcd34d',
                            400: '#fbbf24',
                            500: '#f59e0b',
                            600: '#d97706',
                            700: '#b45309',
                            800: '#92400e',
                            900: '#78350f',
                        },
                    },
                },
            },
        }
    </script>

    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    <style>[x-cloak] { display: none !important; }</style>
    @yield('styles')
</head>

<body class="site-shell min-h-screen flex flex-col bg-[#060611] text-white font-sans antialiased">

    <div class="ambient-bg" aria-hidden="true">
        <span class="ambient-orb ambient-orb--one"></span>
        <span class="ambient-orb ambient-orb--two"></span>
        <span class="ambient-orb ambient-orb--three"></span>
    </div>

    @include('partials.menu')

    <main class="flex-1 w-full">
        @yield('contenido')
    </main>

    @include('partials.footer')

    <script>
    document.addEventListener('alpine:init', () => {
        Alpine.store('wallet', {
            saldo: {{ auth()->user()?->cartera?->saldo ?? 0 }},
        });

        @auth
        const refreshWallet = async () => {
            try {
                const response = await fetch('{{ route('wallet.balance') }}', {
                    headers: { 'Accept': 'application/json' },
                    cache: 'no-store',
                });

                if (!response.ok) return;

                const data = await response.json();
                const saldo = Number(data.saldo);

                if (Number.isFinite(saldo) && saldo !== Alpine.store('wallet').saldo) {
                    Alpine.store('wallet').saldo = saldo;
                    window.dispatchEvent(new CustomEvent('saldo-updated', { detail: { saldo } }));
                }
            } catch (error) {
                // A temporary network failure must not interrupt the page.
            }
        };

        refreshWallet();
        window.setInterval(refreshWallet, 15000);
        window.addEventListener('focus', refreshWallet);
        document.addEventListener('visibilitychange', () => {
            if (!document.hidden) refreshWallet();
        });
        @endauth
    });
    </script>

    @stack('scripts')
</body>

</html>
