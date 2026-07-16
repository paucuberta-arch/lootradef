<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Cartera;
use App\Models\InventarioItem;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class CajaController extends Controller
{
    public function index(Request $request): View
    {
        $inventario = $request->user()
            ? $request->user()->inventario()->latest()->get()
            : collect();

        return view('cajas.index', [
            'cajas' => config('cajas'),
            'inventario' => $inventario,
        ]);
    }

    public function open(Request $request, string $caja): JsonResponse
    {
        $definition = config("cajas.{$caja}");

        if (! $definition) {
            return response()->json(['message' => 'La caja seleccionada no existe.'], 404);
        }

        $item = DB::transaction(function () use ($request, $caja, $definition) {
            $cartera = Cartera::where('usuario_id', $request->user()->id)->lockForUpdate()->first();

            if (! $cartera || $cartera->saldo < $definition['precio']) {
                return null;
            }

            $prize = $this->pickPrize($definition['premios']);

            $item = InventarioItem::create([
                'usuario_id' => $request->user()->id,
                'caja' => $caja,
                'nombre' => $prize['nombre'],
                'imagen' => $prize['imagen'],
                'rareza' => $prize['rareza'],
                'precio_caja' => $definition['precio'],
                'valor_canje' => $prize['valor'],
            ]);
            abort_unless($cartera->apostar($definition['precio'], 'apertura_caja', ['caja' => $caja], $item), 422, 'No tienes saldo suficiente para abrir esta caja.');

            ActivityLog::create([
                'usuario_id' => $request->user()->id,
                'accion' => 'caja_abierta',
                'modelo' => 'InventarioItem',
                'modelo_id' => $item->id,
                'detalles' => ['caja' => $caja, 'premio' => $prize['nombre']],
                'ip' => $request->ip(),
            ]);

            return ['item' => $item, 'saldo' => (float) $cartera->saldo];
        });

        if (! $item) {
            return response()->json(['message' => 'No tienes saldo suficiente para abrir esta caja.'], 422);
        }

        return response()->json([
            'item' => $this->serializeItem($item['item']),
            'saldo' => $item['saldo'],
        ]);
    }

    public function redeem(Request $request, InventarioItem $item): JsonResponse
    {
        if ($item->usuario_id !== $request->user()->id) {
            abort(404);
        }

        $result = DB::transaction(function () use ($request, $item) {
            $lockedItem = InventarioItem::whereKey($item->id)->lockForUpdate()->firstOrFail();

            if ($lockedItem->estado !== 'disponible') {
                return null;
            }

            $cartera = Cartera::where('usuario_id', $request->user()->id)->lockForUpdate()->firstOrFail();
            $cartera->ganar($lockedItem->valor_canje, 'canje_inventario', ['premio' => $lockedItem->nombre], $lockedItem);

            $lockedItem->update(['estado' => 'canjeado', 'canjeado_at' => now()]);

            ActivityLog::create([
                'usuario_id' => $request->user()->id,
                'accion' => 'premio_canjeado',
                'modelo' => 'InventarioItem',
                'modelo_id' => $lockedItem->id,
                'detalles' => ['premio' => $lockedItem->nombre, 'valor' => $lockedItem->valor_canje],
                'ip' => $request->ip(),
            ]);

            return ['saldo' => (float) $cartera->saldo, 'valor' => (float) $lockedItem->valor_canje];
        });

        if (! $result) {
            return response()->json(['message' => 'Este premio ya ha sido canjeado.'], 409);
        }

        return response()->json($result);
    }

    private function pickPrize(array $prizes): array
    {
        $roll = random_int(1, array_sum(array_column($prizes, 'peso')));

        foreach ($prizes as $prize) {
            $roll -= $prize['peso'];
            if ($roll <= 0) {
                return $prize;
            }
        }

        return $prizes[array_key_last($prizes)];
    }

    private function serializeItem(InventarioItem $item): array
    {
        return [
            'id' => $item->id,
            'nombre' => $item->nombre,
            'imagen' => $item->imagen,
            'rareza' => $item->rareza,
            'valor_canje' => $item->valor_canje,
            'estado' => $item->estado,
            'created_at' => $item->created_at->diffForHumans(),
        ];
    }
}
