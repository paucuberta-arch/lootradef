@extends('layouts.app')


@section('menu')

    @include('partials.menu',[
        'operacion'=>'resultados'
    ])

@endsection



@section('contenido')


    <div class="
w-full
max-w-6xl
mx-auto
px-3
sm:px-6
">


        <h1 class="
text-4xl
sm:text-5xl
font-bold
text-center
mb-10
">

            Resultados

        </h1>




        <div class="
overflow-x-auto
rounded-3xl
shadow-xl
mb-10
">


            <table class="
w-full
min-w-[650px]
bg-white
text-center
">


                <thead class="
bg-gradient-to-r
from-blue-600
to-indigo-600
text-white
">


                <tr>


                    <th class="px-4 py-3">
                        Operación
                    </th>


                    <th class="px-4 py-3">
                        Resultado
                    </th>


                    <th class="px-4 py-3">
                        Acción
                    </th>


                </tr>


                </thead>




                <tbody>


                @foreach($operaciones as $op)


                    <tr class="
border-b
hover:bg-blue-50
transition
">


                        <td class="
px-4
py-3
font-semibold
">

                            {{ $op->operacion }}

                        </td>




                        <td class="
px-4
py-3
font-bold
text-green-600
">

                            {{ $op->resultado }}

                        </td>




                        <td class="px-4 py-3">


                            <a href="{{ route('resultados.show',$op->id) }}"

                               class="
bg-blue-600
hover:bg-blue-700
text-white
px-4
py-2
rounded-lg
text-sm
font-bold
transition
">

                                Detalles

                            </a>


                        </td>



                    </tr>


                @endforeach


                </tbody>


            </table>


        </div>


    </div>


@endsection