@extends('layouts.app')


@section('menu')


@endsection



@section('contenido')


    <div class="
relative
min-h-[calc(100vh-180px)]
w-full
overflow-hidden
flex
items-center
justify-center
px-3
sm:px-6
lg:px-10
py-6
sm:py-10
bg-gradient-to-br
from-black
via-gray-950
to-yellow-950
rounded-3xl
">



        {{-- ELEMENTOS CASINO FONDO --}}


        <div class="
    hidden
    sm:block
    absolute
    top-10
    left-10
    text-[120px]
    md:text-[180px]
    opacity-10
    blur-sm
    animate-casino
    ">
            🎰
        </div>



        <div class="
    hidden
    sm:block
    absolute
    top-20
    right-10
    text-[100px]
    md:text-[150px]
    opacity-10
    blur-md
    animate-casino-delay
    ">
            🎲
        </div>




        <div class="
    hidden
    sm:block
    absolute
    bottom-20
    left-10
    text-[120px]
    md:text-[170px]
    opacity-10
    blur-sm
    animate-casino-slow
    ">
            🃏
        </div>




        <div class="
    hidden
    sm:block
    absolute
    bottom-10
    right-10
    text-[150px]
    md:text-[220px]
    opacity-10
    blur-md
    animate-casino
    ">
            💰
        </div>





        {{-- LUZ DE FONDO --}}

        <div class="
    absolute
    inset-0
    z-0
    pointer-events-none
    bg-gradient-to-r
    from-yellow-400/10
    via-transparent
    to-yellow-400/10
    animate-pulse
    ">
        </div>






        {{-- TARJETA PRINCIPAL --}}


        <div class="
casino-card
relative
z-10
w-full
max-w-5xl
min-h-[420px]
sm:min-h-[520px]
lg:min-h-[600px]
flex
flex-col
justify-center
items-center
text-center
rounded-3xl
bg-black/50
backdrop-blur-xl
border
border-yellow-400/30
shadow-2xl
px-4
sm:px-10
py-8
">




            <h1 class="
        text-3xl
        xs:text-4xl
        sm:text-5xl
        md:text-6xl
        lg:text-7xl
        tracking-wide
        break-words
        font-black
        text-yellow-400
        drop-shadow-lg
        animate-pulse
        ">

                🎰 PRÓXIMAMENTE

            </h1>




            <h2 class="
        mt-8
        text-2xl
        sm:text-3xl
        lg:text-5xl
        font-bold
        text-white
        ">

                Casino LOOTRA

            </h2>




            <p class="
        mt-6
        max-w-3xl
        text-base
        sm:text-lg
        lg:text-xl
        text-gray-300
        leading-relaxed
        ">

                Estamos preparando una experiencia de casino completamente nueva.
                Muy pronto podrás disfrutar de ruletas, juegos, estadísticas
                y muchas sorpresas.

            </p>




            <div class="
        mt-10
        flex
        flex-wrap
        justify-center
        gap-3
        ">


            <span class="
            px-4
            py-2
            rounded-xl
            bg-yellow-400/20
            border
            border-yellow-400/30
            text-yellow-300
            ">
                🎲 Juegos
            </span>



                <span class="
            px-4
            py-2
            rounded-xl
            bg-yellow-400/20
            border
            border-yellow-400/30
            text-yellow-300
            ">
                🎰 Ruletas
            </span>



                <span class="
            px-4
            py-2
            rounded-xl
            bg-yellow-400/20
            border
            border-yellow-400/30
            text-yellow-300
            ">
                🃏 Cartas
            </span>


            </div>





            <a href="{{ url('/') }}"
               class="
           relative
           z-20
           mt-10
           px-6
           py-3
           sm:px-10
           sm:py-4
           rounded-xl
           bg-yellow-400
           text-gray-900
           font-bold
           hover:bg-yellow-300
           hover:scale-105
           transition
           shadow-lg
           ">

                ← Volver al inicio

            </a>



        </div>


    </div>


@endsection