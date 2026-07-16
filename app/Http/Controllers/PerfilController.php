<?php

namespace App\Http\Controllers;

use App\Services\AccountMailService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PerfilController extends Controller
{
    public function __construct(private readonly AccountMailService $accountMail) {}

    public function index(Request $request): View
    {
        return view('perfil.index', [
            'usuario' => $request->user(),
            'movimientos' => $request->user()->movimientosCartera()->latest()->take(20)->get(),
        ]);
    }

    public function deposit(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:1|max:50000',
        ]);

        $user = $request->user();
        $amount = round($request->amount, 2);

        if (! $user->cartera) {
            return response()->json(['error' => 'No tienes una cartera activa.'], 422);
        }

        $user->cartera->ganar($amount, 'deposito_demo', ['origen' => 'perfil']);
        $this->accountMail->deposit($user, $amount);

        return response()->json([
            'ok' => true,
            'saldo' => $user->cartera->saldo,
            'message' => "€{$amount} anadidos a tu cartera.",
        ]);
    }

    public function saldo(Request $request): JsonResponse
    {
        return response()->json([
            'saldo' => (float) ($request->user()->cartera()->value('saldo') ?? 0),
        ]);
    }
}
