<?php

namespace App\Http\Controllers;

use App\Models\Operacion;
use Illuminate\Http\Request;

class CalcularController extends Controller
{
    public function calcular(Request $request)
    {

        $request->validate([

            'tipo' => 'required|in:suma,resta,multiplicacion,division',

            'numeros' => 'required|array|min:2',

            'numeros.*' => 'required|numeric',

        ],
            [

                'tipo.required' => 'Debe seleccionarse una operación.',

                'numeros.required' => 'Debes introducir números.',

                'numeros.min' => 'La operación necesita mínimo dos números.',

                'numeros.*.required' => 'Todos los campos son obligatorios.',

                'numeros.*.numeric' => 'Todos los valores deben ser números.',

            ]);

        $tipo = $request->tipo;

        $numeros = $request->numeros;

        switch ($tipo) {

            case 'suma':

                $resultado = array_sum($numeros);

                break;

            case 'resta':

                $resultado = array_shift($numeros);

                foreach ($numeros as $numero) {

                    $resultado -= $numero;

                }

                break;

            case 'multiplicacion':

                $resultado = 1;

                foreach ($numeros as $numero) {

                    $resultado *= $numero;

                }

                break;

            case 'division':

                $resultado = array_shift($numeros);

                foreach ($numeros as $numero) {

                    if ($numero == 0) {

                        return back()
                            ->withErrors([
                                'No se puede dividir entre cero.',
                            ])
                            ->withInput();

                    }

                    $resultado /= $numero;

                }

                break;

        }

        $operacion = Operacion::create([

            'tipo' => $tipo,

            'resultado' => $resultado,

        ]);

        foreach ($request->numeros as $numero) {

            $operacion->numeros()->create([

                'numero' => $numero,

            ]);

        }

        return back()

            ->with('resultado', $resultado);

    }
}
