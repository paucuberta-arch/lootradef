@extends('layouts.app')


@section('menu')

    @include('partials.menu',[
        'operacion'=>'resultados'
    ])

@endsection



@section('contenido')


    <div class="
w-full
max-w-xl
mx-auto
px-3
sm:px-0
">


        <div class="
bg-white
rounded-3xl
shadow-xl
p-6
sm:p-10
text-center
">


            <h1 class="text-3xl sm:text-4xl font-bold mb-8">
                Detalle operación
            </h1>




            <div class="space-y-6 text-lg">



                <p>

                    <strong>ID:</strong>

                    {{ $operacion->id }}

                </p>




                <div>

                    <strong>
                        Operación:
                    </strong>


                    <p class="
mt-3
text-2xl
font-bold
text-blue-600
">

                        {{ $operacion->operacion }}

                    </p>


                </div>







                <div class="
mt-8
bg-green-500
text-white
rounded-2xl
p-6
">


                    <h2 class="
text-xl
font-bold
">

                        Resultado

                    </h2>




                    <p class="
text-5xl
font-black
mt-3
">

                        {{ $operacion->resultado }}

                    </p>


                </div>






                <p class="
text-gray-500
">

                    Creado:

                    {{ $operacion->created_at->format('d/m/Y H:i') }}

                </p>




            </div>






            <a href="{{ route('resultados.index') }}"

               class="
inline-block
mt-10
px-6
py-3
rounded-xl
bg-gray-800
text-white
font-bold
hover:bg-gray-900
transition
">

                ← Volver

            </a>



        </div>


    </div>


@endsection