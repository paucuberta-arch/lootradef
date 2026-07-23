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
    <main class="relative z-10 min-h-screen">@yield('contenido')</main>
    @include('partials.analytics-consent')
    @stack('scripts')
</body>
</html>
