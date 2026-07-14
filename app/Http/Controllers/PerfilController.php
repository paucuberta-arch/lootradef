<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;

class PerfilController extends Controller
{
    public function index(Request $request): View
    {
        return view('perfil.index', [
            'usuario' => $request->user(),
        ]);
    }
}