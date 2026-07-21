<!DOCTYPE html>
<html lang="es" class="scroll-smooth">

<head>
    @include('partials.google-tag')
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Lootra Casino')</title>
    @yield('preloads')

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Space+Grotesk:wght@500;600;700&display=swap" rel="stylesheet" media="print" onload="this.media='all'">
    <noscript><link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Space+Grotesk:wght@500;600;700&display=swap" rel="stylesheet"></noscript>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @yield('styles')
</head>

@php
    $detailSlug = request()->route('slug');
    $visualTheme = match (true) {
        request()->routeIs('inicio') => 'home',
        request()->routeIs('games.index') => 'casino',
        request()->routeIs('sports.*', 'apuestas*') => 'live',
        request()->routeIs('cases.*', 'cajas*', 'inventory.*', 'inventario.*') => 'vault',
        request()->routeIs('profile.*', 'perfil*', 'wallet.*') => 'profile',
        request()->routeIs('feedback.*', 'info') => 'community',
        request()->routeIs('rickyedit.*') => 'campaign',
        request()->routeIs('games.slots.*', 'slots*') || (request()->routeIs('games.show', 'juego.show') && in_array($detailSlug, ['gates-of-olympus', 'sweet-bonanza', 'book-of-dead', 'starburst', 'big-bass-bonanza'], true)) => 'slots',
        request()->routeIs('games.roulette.*', 'ruleta*') || (request()->routeIs('games.show', 'juego.show') && in_array($detailSlug, ['european-roulette', 'lightning-roulette'], true)) => 'roulette',
        request()->routeIs('games.blackjack.*', 'blackjack*') || (request()->routeIs('games.show', 'juego.show') && in_array($detailSlug, ['blackjack-vip', 'blackjack-classic', 'high-low', 'baccarat-royale'], true)) => 'table',
        request()->routeIs('games.poker.*', 'poker*') || (request()->routeIs('games.show', 'juego.show') && in_array($detailSlug, ['texas-holdem', 'dealer-poker'], true)) => 'poker',
        request()->routeIs('games.crash.*', 'crash*') || (request()->routeIs('games.show', 'juego.show') && $detailSlug === 'crash-rocket') => 'crash',
        request()->routeIs('games.originals.*', 'arcade*') || request()->routeIs('games.show', 'juego.show') => 'originals',
        request()->routeIs('games.*', 'juego.*', 'arcade*') => 'game',
        default => 'default',
    };
@endphp
<body class="site-shell theme-{{ $visualTheme }} min-h-screen flex flex-col bg-[#060812] text-white font-sans antialiased"
      data-wallet-balance="{{ $displayBalance ?? 0 }}"
      data-wallet-kind="{{ $displayBalanceKind ?? 'wallet' }}"
      @auth data-wallet-url="{{ route('wallet.balance') }}" @endauth>

    <div class="ambient-bg" aria-hidden="true">
        <span class="ambient-orb ambient-orb--one"></span>
        <span class="ambient-orb ambient-orb--two"></span>
        <span class="ambient-orb ambient-orb--three"></span>
    </div>
    <div class="page-atmosphere" aria-hidden="true"><span class="page-atmosphere__art"></span><span class="page-atmosphere__scan"></span></div>

    @include('partials.menu')

    @if(($rickyeditCampaignEnabled ?? false) && !request()->routeIs('inicio', 'rickyedit.*', 'login', 'registro', 'admin.*'))
        <x-campaign.rickyedit.banner variant="global" :closable="true" />
    @endif

    <main class="flex-1 w-full">
        @yield('contenido')
    </main>

    <div class="page-energy-divider" aria-hidden="true"></div>
    @include('partials.footer')

    <x-campaign.rickyedit.floating-button />

    @stack('scripts')
</body>

</html>
