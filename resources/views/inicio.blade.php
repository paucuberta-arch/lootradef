@extends('layouts.app')


@section('contenido')


    <div class="
w-full
min-h-[calc(100vh-180px)]
flex
items-center
justify-center
px-4
sm:px-6
lg:px-10
py-10
">


        <div class="
    w-full
    max-w-3xl
    bg-white
    rounded-3xl
    shadow-2xl
    p-6
    sm:p-10
    lg:p-16
    text-center
    ">


            <h1 class="
        text-4xl
        sm:text-5xl
        lg:text-6xl
        font-black
        text-blue-600
        mb-6
        sm:mb-8
        ">

                BIENVENIDO

            </h1>




            <p class="
        max-w-2xl
        mx-auto
        text-base
        sm:text-xl
        text-gray-600
        leading-relaxed
        mb-8
        sm:mb-10
        ">

                Bienvenido a mi proyecto Laravel.

                Aquí podrás encontrar una calculadora y próximamente un casino.

            </p>





            <div class="
        flex
        flex-col
        sm:flex-row
        justify-center
        items-center
        gap-4
        sm:gap-6
        ">


                <a href="/hola"

                   class="
               w-full
               sm:w-auto
               px-6
               py-3
               sm:px-10
               sm:py-4
               bg-gray-900
               text-white
               rounded-xl
               font-bold
               hover:bg-gray-700
               hover:scale-105
               transition
               shadow-lg
               ">

                    PRÓXIMAMENTE

                </a>





                <a href="{{ route('suma') }}"

                   class="
               w-full
               sm:w-auto
               px-6
               py-3
               sm:px-10
               sm:py-4
               bg-blue-600
               text-white
               rounded-xl
               font-bold
               hover:bg-blue-700
               hover:scale-105
               transition
               shadow-lg
               ">

                    CALCULADORA

                </a>



            </div>


        </div>


    </div>


@endsection