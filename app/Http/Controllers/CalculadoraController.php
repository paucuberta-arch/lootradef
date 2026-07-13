<?php

namespace App\Http\Controllers;

use App\Models\Operacion;

class CalculadoraController extends Controller
{
    public function mostrar($tipo)
    {

        $nombres = [

            'suma' => 'Suma',

            'resta' => 'Resta',

            'multiplicacion' => 'Multiplicación',

            'division' => 'División',

        ];

        if (! isset($nombres[$tipo])) {

            abort(404);

        }

        $operaciones = Operacion::with('numeros')
            ->latest()
            ->take(3)
            ->get();

        return view('calculadora.calcular', [

            'tipo' => $tipo,

            'nombre' => $nombres[$tipo],

            'operaciones' => $operaciones,

        ]);

    }
}
