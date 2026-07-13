<!DOCTYPE html>
<html lang="es">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>
        Calculadora Casino
    </title>


    <!-- Tailwind CDN -->
    <script src="https://cdn.tailwindcss.com"></script>


    <!-- CSS propio -->
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">

</head>



<body class="
min-h-screen
flex
flex-col
bg-gradient-to-br
from-indigo-50
to-blue-100
">


{{-- MENU SUPERIOR --}}
@yield('menu')



<main class="
    flex-1
    w-full
    px-4
    sm:px-6
    lg:px-10
    py-6
    ">

    @yield('contenido')

</main>



{{-- FOOTER SIEMPRE ACTIVO --}}
@include('partials.footer')
@stack('scripts')

</body>


</html>