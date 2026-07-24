<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Cartera;
use App\Models\DemoWithdrawal;
use App\Models\ActivityLog;
use App\Services\DemoTreasuryService;
use App\Services\WalletService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class AdminDemoWithdrawalController extends Controller
{
    public function __construct(
        private readonly DemoTreasuryService $treasury,
        private readonly WalletService $wallets,
    ) {}

    public function index(Request $request): JsonResponse|View
    {
        $treasury = $this->treasury->current();
        $withdrawals = DemoWithdrawal::with('usuario')->latest()->take(100)->get();
        if ($request->expectsJson()) {
            return response()->json(['treasury' => $treasury, 'withdrawals' => $withdrawals]);
        }

        return view('admin.demo-withdrawals.index', compact('treasury', 'withdrawals'));
    }

    public function review(Request $request, DemoWithdrawal $withdrawal): JsonResponse
    {
        $validated = $request->validate([
            'action' => ['required', 'in:approve,reject,complete'],
            'reason' => ['nullable', 'string', 'max:500'],
        ]);

        $result = DB::transaction(function () use ($request, $withdrawal, $validated) {
            $withdrawal = DemoWithdrawal::whereKey($withdrawal->id)->lockForUpdate()->firstOrFail();
            $action = $validated['action'];

            if ($action === 'approve') {
                abort_unless(in_array($withdrawal->status, [DemoWithdrawal::REQUESTED, DemoWithdrawal::UNDER_REVIEW], true), 409, 'La retirada no está pendiente de aprobación.');
                $withdrawal->update([
                    'status' => DemoWithdrawal::APPROVED,
                    'reviewed_by' => $request->user()->id,
                    'reviewed_at' => now(),
                ]);
            } elseif ($action === 'reject') {
                abort_unless(in_array($withdrawal->status, [DemoWithdrawal::REQUESTED, DemoWithdrawal::UNDER_REVIEW, DemoWithdrawal::APPROVED], true), 409, 'La retirada ya no se puede rechazar.');
                $wallet = Cartera::where('usuario_id', $withdrawal->usuario_id)->lockForUpdate()->firstOrFail();
                $this->treasury->release((float) $withdrawal->amount);
                $this->wallets->credit($wallet, (float) $withdrawal->amount, 'retirada_demo_liberada', ['withdrawal_id' => $withdrawal->id, 'reason' => $validated['reason'] ?? null], $withdrawal, 'demo-withdrawal-release:'.$withdrawal->id);
                $withdrawal->update([
                    'status' => DemoWithdrawal::REJECTED,
                    'rejection_reason' => $validated['reason'] ?? 'Rechazo administrativo demo',
                    'reviewed_by' => $request->user()->id,
                    'reviewed_at' => now(),
                ]);
            } else {
                abort_unless($withdrawal->status === DemoWithdrawal::APPROVED, 409, 'La retirada no está aprobada para completar.');
                $withdrawal->update(['status' => DemoWithdrawal::PROCESSING]);
                $this->treasury->consumeReserved((float) $withdrawal->amount);
                $withdrawal->update(['status' => DemoWithdrawal::COMPLETED, 'completed_at' => now()]);
            }

            ActivityLog::log('retirada_demo_'.$action, DemoWithdrawal::class, $withdrawal->id, [
                'amount' => (float) $withdrawal->amount,
                'status' => $withdrawal->status,
                'reason' => $validated['reason'] ?? null,
            ]);

            return $withdrawal->fresh();
        });

        return response()->json(['withdrawal' => $result, 'treasury' => $this->treasury->current()]);
    }
}
