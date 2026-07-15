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

    public function deposit(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:1|max:50000',
        ]);

        $user = $request->user();
        $amount = round($request->amount, 2);

        if (!$user->cartera) {
            return response()->json(['error' => 'No tienes una cartera activa.'], 422);
        }

        $user->cartera->ganar($amount);

        return response()->json([
            'ok' => true,
            'saldo' => $user->cartera->saldo,
            'message' => "€{$amount} anadidos a tu cartera.",
        ]);
    }
}
