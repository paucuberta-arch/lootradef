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
        $user = $request->user()->load('cartera');

        return view('perfil.index', [
            'usuario' => $user,
            'movimientos' => $user->movimientosCartera()->latest()->take(20)->get(),
            'inventario' => $user->inventario()->where('estado', 'disponible')->latest()->take(4)->get(),
            'ultimasPartidas' => $user->partidas()->latest()->take(5)->get(),
            'resumen' => [
                'partidas' => $user->partidas()->count(),
                'inventario' => $user->inventario()->where('estado', 'disponible')->count(),
                'apuestas' => $user->apuestasDeportivas()->count(),
            ],
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
        $movement = $user->movimientosCartera()->latest()->first();
        $this->accountMail->deposit($user, $amount);

        return response()->json([
            'ok' => true,
            'saldo' => $user->cartera->saldo,
            'message' => "€{$amount} anadidos a tu cartera.",
            'movement' => [
                'label' => $movement?->etiqueta,
                'amount' => $movement?->importe,
                'balance' => $movement?->saldo_posterior,
                'created_at' => $movement?->created_at?->format('d/m/Y H:i'),
            ],
        ]);
    }

    public function saldo(Request $request): JsonResponse
    {
        return response()->json([
            'saldo' => (float) ($request->user()->cartera()->value('saldo') ?? 0),
        ]);
    }
}
