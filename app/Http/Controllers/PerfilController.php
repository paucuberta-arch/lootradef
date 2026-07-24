<?php

namespace App\Http\Controllers;

use App\Services\AccountMailService;
use App\Services\CampaignChallengeService;
use App\Services\CampaignLeaderboardService;
use App\Services\DemoTreasuryService;
use App\Models\DemoWithdrawal;
use App\Services\WalletService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class PerfilController extends Controller
{
    public function __construct(
        private readonly AccountMailService $accountMail,
        private readonly CampaignChallengeService $campaignChallenges,
        private readonly CampaignLeaderboardService $campaignLeaderboard,
        private readonly DemoTreasuryService $treasury,
        private readonly WalletService $wallets,
    ) {}

    public function index(Request $request): View
    {
        $user = $request->user()->loadMissing('cartera');
        $campaignChallenge = $this->campaignChallenges->findForUser($user);
        $movementsQuery = $user->movimientosCartera();
        $wagered = (float) (clone $movementsQuery)->where('direccion', 'debito')->where('tipo', '!=', 'retirada_demo_reserva')->sum('importe');
        $won = (float) (clone $movementsQuery)->where('direccion', 'credito')->where('tipo', 'like', 'premio%')->sum('importe');
        $financialSummary = [
            'available' => (float) ($user->cartera?->saldo ?? 0),
            'promotional' => 0.0,
            'reserved' => (float) DemoWithdrawal::where('usuario_id', $user->id)->whereIn('status', [DemoWithdrawal::REQUESTED, DemoWithdrawal::UNDER_REVIEW, DemoWithdrawal::APPROVED, DemoWithdrawal::PROCESSING])->sum('amount'),
            'deposited' => (float) (clone $movementsQuery)->where('tipo', 'deposito_demo')->sum('importe'),
            'wagered' => $wagered,
            'won' => $won,
            'withdrawn' => (float) (clone $movementsQuery)->where('tipo', 'retirada_demo_reserva')->sum('importe'),
            'net' => round($won - $wagered, 2),
        ];

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
            'campaignChallenge' => $campaignChallenge,
            'campaignPosition' => $this->campaignLeaderboard->position($campaignChallenge),
            'financialSummary' => $financialSummary,
        ]);
    }

    public function deposit(Request $request)
    {
        abort_unless(config('features.economy.enabled') && config('features.demo_deposits.enabled'), 404);

        $request->validate([
            'amount' => ['required', 'numeric', 'min:1', 'max:1000'],
            'request_token' => ['required', 'uuid'],
        ]);

        $user = $request->user();
        abort_unless($user->is_demo, 403, 'Los depósitos demo sólo están disponibles para cuentas demo.');
        $amount = round((float) $request->input('amount'), 2);

        if (! $user->cartera) {
            return response()->json(['error' => 'No tienes una cartera activa.'], 422);
        }

        $created = false;
        $movement = DB::transaction(function () use ($user, $amount, $request, &$created) {
            $movement = $this->wallets->credit(
                $user->cartera,
                $amount,
                'deposito_demo',
                ['origen' => 'perfil'],
                null,
                hash('sha256', 'demo-deposit|'.$user->id.'|'.$request->string('request_token')),
                (float) config('features.demo_deposits.max_balance'),
            );
            $created = $movement->wasRecentlyCreated;
            if ($created) {
                $this->treasury->fund($amount);
            }

            return $movement;
        });
        if ($created) {
            $this->accountMail->deposit($user, $amount);
        }
        $user->cartera->refresh();

        return response()->json([
            'ok' => true,
            'saldo' => $user->cartera->saldo,
            'message' => "{$amount} EUR Demo añadidos a tu cartera ficticia.",
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
        $challenge = $this->campaignChallenges->findForUser($request->user());

        return response()->json([
            'saldo' => $challenge?->status === 'active'
                ? (float) $challenge->current_balance
                : (float) ($request->user()->cartera()->value('saldo') ?? 0),
            'kind' => $challenge?->status === 'active' ? 'campaign' : 'wallet',
        ]);
    }
}
