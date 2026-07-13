@extends('layouts.app')

@section('menu')

    @include('partials.menu', [
        'operacion' => 'suma'
    ])

@endsection


@section('contenido')

    <div class="w-full max-w-3xl mx-auto">

        <h1 class="
        text-4xl
        sm:text-5xl
        font-bold
        text-center
        mb-8
    ">
            Calculadora de suma
        </h1>


        <div class="
        bg-white
        rounded-3xl
        shadow-xl
        p-6
        sm:p-10
    ">

            <form action="{{ route('calcular') }}" method="POST">

                @csrf


                {{-- ERRORES DEL BACKEND --}}

                @if($errors->any())

                    <div class="
                    mb-6
                    rounded-xl
                    border
                    border-red-300
                    bg-red-50
                    text-red-700
                    p-4
                ">

                        <ul class="list-disc list-inside">

                            @foreach($errors->all() as $error)

                                <li>{{ $error }}</li>

                            @endforeach

                        </ul>

                    </div>

                @endif


                {{-- INPUTS --}}

                <div id="numeros-container">

                    @php

                        $numeros = old('numeros', ['', '']);

                    @endphp

                    @foreach($numeros as $numero)

                        <div class="mb-4">

                            <input
                                    type="number"
                                    name="numeros[]"
                                    value="{{ $numero }}"
                                    placeholder="Introduce un número"
                                    required

                                    class="
                                numero-input
                                w-full
                                rounded-xl
                                border-2
                                border-gray-300
                                p-3
                                text-center
                                focus:border-blue-500
                                focus:ring-2
                                focus:ring-blue-200
                                outline-none
                                transition
                            "

                            >

                        </div>

                    @endforeach

                </div>


                {{-- BOTÓN CALCULAR --}}

                <button

                        type="submit"

                        class="
                    w-full
                    mt-6
                    bg-green-600
                    hover:bg-green-700
                    text-white
                    font-bold
                    py-3
                    rounded-xl
                    transition
                    shadow-lg
                "

                >

                    Calcular

                </button>

            </form>

            {{-- BOTONES AÑADIR / ELIMINAR --}}

            <div class="
            flex
            flex-col
            sm:flex-row
            justify-center
            gap-4
            mt-8
        ">

                <button
                        type="button"
                        onclick="anadirNumero()"

                        class="
                    bg-blue-600
                    hover:bg-blue-700
                    text-white
                    font-bold
                    px-5
                    py-3
                    rounded-xl
                    transition
                    shadow
                "
                >

                    + Añadir número

                </button>



                <button
                        type="button"
                        onclick="borrarNumero()"

                        class="
                    bg-red-600
                    hover:bg-red-700
                    text-white
                    font-bold
                    px-5
                    py-3
                    rounded-xl
                    transition
                    shadow
                "
                >

                    − Eliminar número

                </button>

            </div>

        </div>



        {{-- RESULTADO --}}

        @if(session('resultado'))

            <div class="
            resultado
            mt-10
            bg-green-600
            text-white
            rounded-3xl
            p-8
            text-center
            shadow-xl
        ">

                <h2 class="text-2xl font-bold mb-4">

                    Resultado

                </h2>

                <strong class="text-5xl">

                    {{ session('resultado') }}

                </strong>

            </div>

        @endif



        {{-- HISTORIAL --}}

        <div class="
        mt-16
        mb-20
        overflow-x-auto
        rounded-3xl
        shadow-xl
        bg-white
    ">

            <table class="
            w-full
            min-w-[450px]
            text-center
            text-sm
            md:text-base
        ">

                <thead class="
                bg-gradient-to-r
                from-blue-600
                to-indigo-600
                text-white
            ">

                <tr>

                    <th class="px-4 py-4">

                        Operación

                    </th>

                    <th class="px-4 py-4">

                        Resultado

                    </th>

                </tr>

                </thead>

                <tbody>

                @forelse($operaciones as $op)

                    <tr class="
                        border-b
                        hover:bg-blue-50
                        transition
                    ">

                        <td class="
                            px-4
                            py-4
                            font-semibold
                            whitespace-nowrap
                        ">

                            {{ $op->operacion }}

                        </td>

                        <td class="
                            px-4
                            py-4
                            font-bold
                            text-green-600
                        ">

                            {{ $op->resultado }}

                        </td>

                    </tr>

                @empty

                    <tr>

                        <td colspan="2" class="
                            py-8
                            text-gray-500
                        ">

                            Todavía no hay operaciones.

                        </td>

                    </tr>

                @endforelse

                </tbody>

            </table>

        </div>

    </div>

@endsection

@push('scripts')

    <script>

        function anadirNumero(){

            let container = document.getElementById('numeros-container');

            let div = document.createElement('div');

            div.className = "mb-4";

            div.innerHTML = `

        <input

        type="number"

        name="numeros[]"

        placeholder="Introduce un número"

        required

        class="
        numero-input
        w-full
        rounded-xl
        border-2
        p-3
        text-center
        "

        >

    `;

            container.appendChild(div);

        }



        function borrarNumero(){

            let container = document.getElementById('numeros-container');

            let inputs = container.querySelectorAll('.numero-input');


            if(inputs.length <= 2){

                alert('Debe haber al menos dos números');

                return;

            }


            inputs[inputs.length - 1].parentElement.remove();

        }

    </script>

@endpush