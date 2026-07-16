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

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @yield('styles')
</head>

<body class="site-shell min-h-screen flex flex-col bg-[#060812] text-white font-sans antialiased"
      data-wallet-balance="{{ auth()->user()?->cartera?->saldo ?? 0 }}"
      @auth data-wallet-url="{{ route('wallet.balance') }}" @endauth>

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

    @stack('scripts')
</body>

</html>
