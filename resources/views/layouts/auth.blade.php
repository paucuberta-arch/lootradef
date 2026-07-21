<!DOCTYPE html>
<html lang="es" class="scroll-smooth">
<head>
    @include('partials.google-tag')
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Acceso — Lootra Casino')</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Space+Grotesk:wght@500;600;700&display=swap" rel="stylesheet" media="print" onload="this.media='all'">
    <noscript><link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Space+Grotesk:wght@500;600;700&display=swap" rel="stylesheet"></noscript>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="site-shell theme-auth min-h-screen bg-[#060812] text-white font-sans antialiased">
    <div class="ambient-bg" aria-hidden="true"><span class="ambient-orb ambient-orb--one"></span><span class="ambient-orb ambient-orb--two"></span></div>
    <div class="page-atmosphere" aria-hidden="true"><span class="page-atmosphere__art"></span><span class="page-atmosphere__scan"></span></div>
    <main class="relative z-10 min-h-screen">@yield('contenido')</main>
    @stack('scripts')
</body>
</html>
