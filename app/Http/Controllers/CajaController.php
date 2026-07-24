<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
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
            'cajas' => $this->prizes->publicDefinitions(),
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

        $validated = $request->validate([
            'request_token' => ['required', 'uuid'],
        ]);

        $item = DB::transaction(function () use ($request, $caja, $definition, $validated) {
            $cartera = $request->user()->cartera()->lockForUpdate()->first();
            abort_unless($cartera, 422, 'No tienes una cartera activa.');

            $existing = InventarioItem::where('usuario_id', $request->user()->id)
                ->where('request_token', $validated['request_token'])
                ->lockForUpdate()
                ->first();
            if ($existing) {
                abort_unless($existing->caja === $caja, 409, 'La clave de idempotencia ya fue usada para otra caja.');
                $prize = [
                    'nombre' => $existing->nombre,
                    'imagen' => $existing->imagen,
                    'rareza' => $existing->rareza,
                    'valor_virtual' => (int) ($existing->valor_virtual ?: round((float) $existing->valor_canje * 100)),
                ];

                return [
                    'item' => $existing,
                    'saldo' => (float) $cartera->saldo,
                    'reel' => $this->prizes->buildReel($definition['premios'], $prize),
                ];
            }

            if ($cartera->saldo < $definition['precio']) {
                return null;
            }

            $prize = $this->prizes->pick($caja, $definition, $request->user());
            $reel = $this->prizes->buildReel($definition['premios'], $prize);

            $item = InventarioItem::create([
                'usuario_id' => $request->user()->id,
                'request_token' => $validated['request_token'],
                'caja' => $caja,
                'nombre' => $prize['nombre'],
                'imagen' => $prize['imagen'],
                'rareza' => $prize['rareza'],
                'precio_caja' => $definition['precio'],
                'valor_canje' => $prize['valor'],
                'valor_virtual' => (int) round((float) ($prize['valor_virtual'] ?? ((float) $prize['valor'] * 100))),
                'estado' => 'disponible',
            ]);
            abort_unless($cartera->apostar(
                $definition['precio'],
                'apertura_caja',
                ['caja' => $caja],
                $item,
                'case-open|'.$request->user()->id.'|'.$validated['request_token'],
            ), 422, 'No tienes saldo suficiente para abrir esta caja.');

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

            $virtualValue = (int) ($lockedItem->valor_virtual ?: round((float) $lockedItem->valor_canje * 100));

            $lockedItem->update(['estado' => 'canjeado', 'valor_virtual' => $virtualValue, 'canjeado_at' => now()]);

            ActivityLog::create([
                'usuario_id' => $request->user()->id,
                'accion' => 'premio_canjeado',
                'modelo' => 'InventarioItem',
                'modelo_id' => $lockedItem->id,
                'detalles' => ['premio' => $lockedItem->nombre, 'valor_virtual' => $virtualValue],
                'ip' => $request->ip(),
            ]);

            return [
                'message' => 'Recompensa virtual activada. No se ha creado saldo ni valor monetario.',
                'valor_virtual' => $virtualValue,
            ];
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
            'valor_virtual' => (int) ($item->valor_virtual ?: round((float) $item->valor_canje * 100)),
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
            'valor_virtual' => (int) round((float) ($prize['valor_virtual'] ?? ((float) $prize['valor'] * 100))),
        ];
    }

    private function serializeWinner(array $item): array
    {
        return [
            'prize_key' => $item['prize_key'],
            'nombre' => $item['nombre'],
            'imagen' => $item['imagen'],
            'rareza' => $item['rareza'],
            'valor_virtual' => (int) $item['valor_virtual'],
        ];
    }
}
