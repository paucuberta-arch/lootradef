<?php

namespace App\Http\Controllers;

use App\Models\Cartera;
use App\Models\DemoWithdrawal;
use App\Services\DemoTreasuryService;
use App\Services\WalletService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DemoWithdrawalController extends Controller
{
    private const METHODS = ['demo_wallet', 'demo_card', 'demo_transfer'];

    public function __construct(
        private readonly DemoTreasuryService $treasury,
        private readonly WalletService $wallets,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $withdrawals = DemoWithdrawal::where('usuario_id', $request->user()->id)
            ->latest()->take(25)->get();

        return response()->json([
            'currency' => config('features.economy.currency_label', 'EUR Demo'),
            'withdrawals' => $withdrawals,
            'reserved' => (float) DemoWithdrawal::where('usuario_id', $request->user()->id)
                ->whereIn('status', [DemoWithdrawal::REQUESTED, DemoWithdrawal::UNDER_REVIEW, DemoWithdrawal::APPROVED, DemoWithdrawal::PROCESSING])
                ->sum('amount'),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'amount' => ['required', 'numeric', 'min:'.config('features.economy.withdrawal_minimum', 1), 'max:'.config('features.economy.withdrawal_maximum', 5000)],
            'method_key' => ['required', 'string', 'in:'.implode(',', self::METHODS)],
            'request_token' => ['required', 'uuid'],
        ]);
        $user = $request->user();
        abort_unless($user->is_demo || in_array($user->data_origin, ['test', 'simulated'], true), 403, 'Las retiradas solo están disponibles para cuentas demo.');
        $amount = round((float) $validated['amount'], 2);

        $created = false;
        $withdrawal = DB::transaction(function () use ($request, $validated, $user, $amount, &$created) {
            $existing = DemoWithdrawal::where('request_token', $validated['request_token'])->lockForUpdate()->first();
            if ($existing) {
                abort_unless($existing->usuario_id === $user->id && (float) $existing->amount === $amount && $existing->method_key === $validated['method_key'], 409, 'La clave de idempotencia ya fue usada para otra retirada.');

                return $existing;
            }

            $wallet = Cartera::where('usuario_id', $user->id)->lockForUpdate()->firstOrFail();
            $withdrawal = DemoWithdrawal::create([
                'usuario_id' => $user->id,
                'request_token' => $validated['request_token'],
                'amount' => $amount,
                'currency' => config('features.economy.currency_code', 'EUR_DEMO'),
                'method_key' => $validated['method_key'],
                'status' => DemoWithdrawal::REQUESTED,
                'metadata' => ['simulation' => true],
            ]);
            $this->treasury->reserve($amount);
            abort_unless($this->wallets->debit($wallet, $amount, 'retirada_demo_reserva', ['withdrawal_id' => $withdrawal->id], $withdrawal, 'demo-withdrawal:'.$user->id.':'.$validated['request_token']), 422, 'No tienes saldo demo suficiente.');
            $withdrawal->update(['reserved_at' => now()]);
            $created = true;

            return $withdrawal->fresh();
        });

        return response()->json(['withdrawal' => $withdrawal, 'message' => 'Retirada demo solicitada.'], $created ? 201 : 200);
    }

    public function cancel(Request $request, DemoWithdrawal $withdrawal): JsonResponse
    {
        abort_unless($withdrawal->usuario_id === $request->user()->id, 404);
        $cancelled = DB::transaction(function () use ($withdrawal) {
            $withdrawal = DemoWithdrawal::whereKey($withdrawal->id)->lockForUpdate()->firstOrFail();
            abort_unless(in_array($withdrawal->status, [DemoWithdrawal::REQUESTED, DemoWithdrawal::UNDER_REVIEW], true), 409, 'La retirada ya no se puede cancelar.');
            $wallet = Cartera::where('usuario_id', $withdrawal->usuario_id)->lockForUpdate()->firstOrFail();
            $this->treasury->release((float) $withdrawal->amount);
            $this->wallets->credit($wallet, (float) $withdrawal->amount, 'retirada_demo_liberada', ['withdrawal_id' => $withdrawal->id], $withdrawal, 'demo-withdrawal-release:'.$withdrawal->id);
            $withdrawal->update(['status' => DemoWithdrawal::CANCELLED]);

            return $withdrawal->fresh();
        });

        return response()->json(['withdrawal' => $cancelled]);
    }
}
