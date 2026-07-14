<!DOCTYPE html>
<html lang="es" class="scroll-smooth">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Lootra')</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">

    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Inter', 'system-ui', 'sans-serif'],
                    },
                    colors: {
                        brand: {
                            50: '#f3f0ff',
                            100: '#e9e3ff',
                            200: '#d5ccff',
                            300: '#b5a5ff',
                            400: '#9170ff',
                            500: '#7c3aed',
                            600: '#6d28d9',
                            700: '#5b21b6',
                            800: '#4c1d95',
                            900: '#3b0f7a',
                        },
                    },
                },
            },
        }
    </script>

    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    @yield('styles')
</head>

<body class="min-h-screen flex flex-col bg-slate-950 text-white font-sans antialiased">

    @yield('menu')

    <main class="flex-1 w-full">
        @yield('contenido')
    </main>

    @include('partials.footer')
    @stack('scripts')
</body>

</html>
