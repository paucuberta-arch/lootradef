<nav class="
bg-gradient-to-r
from-gray-900
to-blue-600
shadow-xl
">


    <div class="
max-w-7xl
mx-auto
px-4
py-4
">


        <div class="
flex
items-center
justify-between
md:hidden
">


            <!-- LOGO MOVIL -->

            <a href="{{ url('/') }}"
               class="
           text-white
           text-2xl
           font-bold
           ">

                🎰 Calculadora

            </a>



            <!-- BOTON BURGER -->

            <button
                    onclick="document.getElementById('menu-mobile').classList.toggle('hidden')"
                    class="
            text-white
            text-3xl
            focus:outline-none
            ">

                ☰

            </button>


        </div>





        <!-- MENU MOVIL -->

        <div id="menu-mobile"

             class="
         hidden
         md:hidden
         mt-5
         pb-4
         pr-4
         ">


            <ul class="
        flex
        flex-col
        items-end
        gap-5
        ">



                <li>

                    <a href="{{ route('suma') }}"

                       class="
                   font-semibold
                   text-lg
                   {{ request()->routeIs('suma')
                   ?
                   'text-yellow-400'
                   :
                   'text-white hover:text-yellow-300'
                   }}
                               ">

                        Suma

                    </a>

                </li>



                <li>

                    <a href="{{ route('resta') }}"

                       class="
                   font-semibold
                   text-lg
                   {{ request()->routeIs('resta')
                   ?
                   'text-yellow-400'
                   :
                   'text-white hover:text-yellow-300'
                   }}
                               ">

                        Resta

                    </a>

                </li>




                <li>

                    <a href="{{ route('multiplicacion') }}"

                       class="
                   font-semibold
                   text-lg
                   {{ request()->routeIs('multiplicacion')
                   ?
                   'text-yellow-400'
                   :
                   'text-white hover:text-yellow-300'
                   }}
                               ">

                        Multiplicación

                    </a>

                </li>




                <li>

                    <a href="{{ route('division') }}"

                       class="
                   font-semibold
                   text-lg
                   {{ request()->routeIs('division')
                   ?
                   'text-yellow-400'
                   :
                   'text-white hover:text-yellow-300'
                   }}
                               ">

                        División

                    </a>

                </li>




                <li>

                    <a href="{{ route('resultados.index') }}"

                       class="
                   font-semibold
                   text-lg
                   {{ request()->routeIs('resultados.*')
                   ?
                   'text-yellow-400'
                   :
                   'text-white hover:text-yellow-300'
                   }}
                               ">

                        Resultados

                    </a>

                </li>



            </ul>


        </div>







        <!-- MENU ESCRITORIO -->

        <div class="
    hidden
    md:flex
    items-center
    justify-between
    ">



            <!-- LOGO -->

            <a href="{{ url('/') }}"
               class="
           text-white
           text-2xl
           sm:text-3xl
           font-bold
           hover:text-yellow-300
           transition
           ">

                🎰 Calculadora

            </a>






            <ul class="
        flex
        gap-6
        lg:gap-8
        items-center
        ">



                <li>

                    <a href="{{ route('suma') }}"

                       class="
                   font-semibold
                   text-lg
                   transition
                   {{ request()->routeIs('suma')
                   ?
                   'text-yellow-400 border-b-4 border-yellow-400 pb-1'
                   :
                   'text-white hover:text-yellow-300'
                   }}
                               ">

                        Suma

                    </a>

                </li>




                <li>

                    <a href="{{ route('resta') }}"

                       class="
                   font-semibold
                   text-lg
                   transition
                   {{ request()->routeIs('resta')
                   ?
                   'text-yellow-400 border-b-4 border-yellow-400 pb-1'
                   :
                   'text-white hover:text-yellow-300'
                   }}
                               ">

                        Resta

                    </a>

                </li>





                <li>

                    <a href="{{ route('multiplicacion') }}"

                       class="
                   font-semibold
                   text-lg
                   transition
                   {{ request()->routeIs('multiplicacion')
                   ?
                   'text-yellow-400 border-b-4 border-yellow-400 pb-1'
                   :
                   'text-white hover:text-yellow-300'
                   }}
                               ">

                        Multiplicación

                    </a>

                </li>





                <li>

                    <a href="{{ route('division') }}"

                       class="
                   font-semibold
                   text-lg
                   transition
                   {{ request()->routeIs('division')
                   ?
                   'text-yellow-400 border-b-4 border-yellow-400 pb-1'
                   :
                   'text-white hover:text-yellow-300'
                   }}
                               ">

                        División

                    </a>

                </li>





                <li>

                    <a href="{{ route('resultados.index') }}"

                       class="
                   font-semibold
                   text-lg
                   transition
                   {{ request()->routeIs('resultados.*')
                   ?
                   'text-yellow-400 border-b-4 border-yellow-400 pb-1'
                   :
                   'text-white hover:text-yellow-300'
                   }}
                               ">

                        Resultados

                    </a>

                </li>



            </ul>



        </div>


    </div>


</nav>