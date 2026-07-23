<!DOCTYPE html>
<html lang="es" class="scroll-smooth">
<head>
    @include('partials.google-tag')
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Acceso — Lootra Casino')</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="site-shell theme-auth min-h-screen bg-[#060812] text-white font-sans antialiased">
    <div class="ambient-bg" aria-hidden="true"><span class="ambient-orb ambient-orb--one"></span><span class="ambient-orb ambient-orb--two"></span></div>
    <div class="page-atmosphere" aria-hidden="true"><span class="page-atmosphere__art"></span><span class="page-atmosphere__scan"></span></div>
    <a href="{{ route('inicio') }}" aria-label="Lootra Casino, inicio" class="absolute left-5 top-5 z-20 inline-flex rounded-xl border border-white/10 bg-black/20 px-3 py-2 backdrop-blur-xl transition hover:border-brand-300/30 sm:left-8 sm:top-8">
        <img src="{{ asset('images/logo/lootra-wordmark-transparent.png') }}" alt="Lootra Casino" class="h-8 w-auto max-w-[10rem] object-contain object-left" width="2172" height="724" decoding="async">
    </a>
    <main class="relative z-10 min-h-screen">@yield('contenido')</main>
    @include('partials.analytics-consent')
    @stack('scripts')
</body>
</html>
