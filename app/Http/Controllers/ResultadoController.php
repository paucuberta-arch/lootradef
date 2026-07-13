<?php

namespace App\Http\Controllers;

use App\Models\Operacion;

class ResultadoController extends Controller
{
    public function index()
    {

        $operaciones = Operacion::with('numeros')

            ->latest()

            ->get();

        return view(

            'resultados.index',

            compact('operaciones')

        );

    }

    public function show($id)
    {

        $operacion = Operacion::with('numeros')

            ->findOrFail($id);

        return view(

            'resultados.show',

            compact('operacion')

        );

    }
}
