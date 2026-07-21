<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Cartera;
use App\Models\InventarioItem;
use App\Services\CampaignChallengeService;
use App\Services\CasePrizeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class CajaController extends Controller
{
    public function __construct(
        private readonly CampaignChallengeService $campaignChallenges,
        private readonly CasePrizeService $prizes,
    ) {}

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
        abort_if(
            $this->campaignChallenges->activeForUser($request->user()),
            409,
            'Las cajas no están incluidas en el reto. Finaliza el intento antes de abrir una.'
        );
        $definition = config("cajas.{$caja}");

        if (! $definition) {
            return response()->json(['message' => 'La caja seleccionada no existe.'], 404);
        }

        $item = DB::transaction(function () use ($request, $caja, $definition) {
            $cartera = Cartera::where('usuario_id', $request->user()->id)->lockForUpdate()->first();

            if (! $cartera || $cartera->saldo < $definition['precio']) {
                return null;
            }

            $prize = $this->prizes->pick($caja, $definition, $request->user());
            $reel = $this->prizes->buildReel($definition['premios'], $prize);

            $item = InventarioItem::create([
                'usuario_id' => $request->user()->id,
                'caja' => $caja,
                'nombre' => $prize['nombre'],
                'imagen' => $prize['imagen'],
                'rareza' => $prize['rareza'],
                'precio_caja' => $definition['precio'],
                'valor_canje' => $prize['valor'],
                'estado' => 'disponible',
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

            return ['item' => $item, 'saldo' => (float) $cartera->saldo, 'reel' => $reel];
        });

        if (! $item) {
            return response()->json(['message' => 'No tienes saldo suficiente para abrir esta caja.'], 422);
        }

        $serializedItem = $this->serializeItem($item['item']);
        $winnerIndex = $item['reel']['winner_index'];
        $reel = array_map(fn (array $prize) => $this->serializePrize($prize), $item['reel']['items']);

        // The reel winner is built from the persisted inventory item itself. This keeps
        // the visual result and the authoritative server award as one identical object.
        $reel[$winnerIndex] = $this->serializeWinner($serializedItem);

        return response()->json([
            'item' => $serializedItem,
            'winner' => $this->serializeWinner($serializedItem),
            'saldo' => $item['saldo'],
            'reel' => $reel,
            'winner_index' => $winnerIndex,
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

    private function serializeItem(InventarioItem $item): array
    {
        return [
            'id' => $item->id,
            'prize_key' => $this->prizes->prizeKey(['nombre' => $item->nombre]),
            'nombre' => $item->nombre,
            'imagen' => $item->imagen,
            'rareza' => $item->rareza,
            'valor_canje' => $item->valor_canje,
            'estado' => $item->estado,
            'created_at' => $item->created_at->diffForHumans(),
        ];
    }

    private function serializePrize(array $prize): array
    {
        return [
            'prize_key' => $this->prizes->prizeKey($prize),
            'nombre' => $prize['nombre'],
            'imagen' => $prize['imagen'],
            'rareza' => $prize['rareza'],
            'valor_canje' => (float) $prize['valor'],
        ];
    }

    private function serializeWinner(array $item): array
    {
        return [
            'prize_key' => $item['prize_key'],
            'nombre' => $item['nombre'],
            'imagen' => $item['imagen'],
            'rareza' => $item['rareza'],
            'valor_canje' => (float) $item['valor_canje'],
        ];
    }
}
