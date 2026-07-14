@extends('layouts.app')

@section('menu')
    @include('partials.menu')
@endsection

@section('contenido')

    <div class="w-full max-w-3xl mx-auto py-10">

        @if(session('success'))

            <div class="
                mb-6
                rounded-xl
                bg-green-100
                border
                border-green-300
                text-green-800
                p-4
            ">
                {{ session('success') }}
            </div>

        @endif

        <div class="
            bg-white
            rounded-3xl
            shadow-2xl
            p-6
            sm:p-10
        ">

            <div class="text-center mb-10">

                <div class="
                    inline-flex
                    items-center
                    justify-center
                    w-24
                    h-24
                    rounded-full
                    bg-blue-600
                    text-white
                    text-4xl
                    font-black
                    mb-5
                ">
                    {{ strtoupper(substr($usuario->name, 0, 1)) }}
                </div>

                <h1 class="
                    text-3xl
                    sm:text-4xl
                    font-black
                    text-gray-900
                ">
                    {{ $usuario->name }}
                </h1>

                <p class="text-gray-500 mt-2">
                    Mi perfil
                </p>

            </div>

            <div class="
                rounded-2xl
                border
                border-gray-200
                divide-y
                divide-gray-200
            ">

                <div class="p-5">

                    <p class="text-sm text-gray-500">
                        Nombre
                    </p>

                    <p class="font-bold text-gray-900 mt-1">
                        {{ $usuario->name }}
                    </p>

                </div>

                <div class="p-5">

                    <p class="text-sm text-gray-500">
                        Correo electrónico
                    </p>

                    <p class="font-bold text-gray-900 mt-1">
                        {{ $usuario->email }}
                    </p>

                </div>

                <div class="p-5">

                    <p class="text-sm text-gray-500">
                        Miembro desde
                    </p>

                    <p class="font-bold text-gray-900 mt-1">
                        {{ $usuario->created_at->format('d/m/Y') }}
                    </p>

                </div>

            </div>

            <form
                    action="{{ route('logout') }}"
                    method="POST"
                    class="mt-8"
            >

                @csrf

                <button
                        type="submit"
                        class="
                        w-full
                        rounded-xl
                        bg-red-600
                        hover:bg-red-700
                        text-white
                        font-bold
                        py-3
                        transition
                    "
                >
                    Cerrar sesión
                </button>

            </form>

        </div>

    </div>

@endsection