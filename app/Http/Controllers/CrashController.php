<?php

namespace App\Http\Controllers;

use App\Models\CrashRound;
use App\Models\Partida;
use App\Models\Usuario;
use App\Services\CampaignManager;
use App\Services\GameBalanceService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;

class CrashController extends Controller
{
    private const MULTIPLIER_PER_SECOND = 0.20;

    public function __construct(private readonly GameBalanceService $balances) {}

    public function index(Request $request): View
    {
        $campaignId = $this->balances->campaignId($request->user(), 'crash');
        $active = DB::transaction(function () use ($request, $campaignId) {
            $round = CrashRound::where('usuario_id', $request->user()->id)
                ->where('campaign_challenge_id', $campaignId)->where('estado', 'activa')->lockForUpdate()->latest()->first();

            if ($round && $this->currentMultiplier($round) >= $round->crash_point) {
                $this->finish($round, 'crashed', 0, null);

                return null;
            }

            return $round;
        });

        return view('games.crash', [
            'partidas' => Partida::where('usuario_id', $request->user()->id)
                ->where('juego', 'crash')->latest()->take(10)->get(),
            'activeRound' => $active ? $this->roundData($active) : null,
            'gameBalance' => $this->balances->balance($request->user(), 'crash', $campaignId),
        ]);
    }

    public function play(Request $request): JsonResponse
    {
        if (! $request->filled('request_token')) {
            $request->merge(['request_token' => (string) Str::uuid()]);
        }
        $validated = $request->validate([
            'apuesta' => ['required', 'numeric', 'min:0.10', 'max:1000'],
            'request_token' => ['required', 'uuid'],
        ]);

        $campaignId = $this->balances->campaignId($request->user(), 'crash');
        $round = DB::transaction(function () use ($request, $validated, $campaignId) {
            $existing = CrashRound::where('usuario_id', $request->user()->id)
                ->where('request_token', $validated['request_token'])->first();
            if ($existing) {
                return $existing;
            }

            $active = CrashRound::where('usuario_id', $request->user()->id)
                ->where('campaign_challenge_id', $campaignId)->where('estado', 'activa')->lockForUpdate()->latest()->first();
            if ($active && $this->currentMultiplier($active) >= $active->crash_point) {
                $this->finish($active, 'crashed', 0, null);
                $active = null;
            }
            abort_if($active, 409, 'Ya tienes una ronda Crash activa.');

            $bet = round((float) $validated['apuesta'], 2);
            $round = CrashRound::create([
                'usuario_id' => $request->user()->id,
                'request_token' => $validated['request_token'],
                'apuesta' => $bet,
                'crash_point' => $this->generateCrashPoint(),
                'estado' => 'activa',
                'iniciada_at' => now(),
                'campaign_challenge_id' => $campaignId,
                'campaign_key' => $campaignId ? CampaignManager::KEY : null,
            ]);
            abort_unless($this->balances->debit($request->user(), 'crash', $bet, 'apuesta_crash', [], $round, $validated['request_token'], $campaignId), 422, 'Saldo insuficiente.');

            return $round;
        });

        return response()->json($this->roundData($round) + ['ok' => true], 201);
    }

    public function status(Request $request): JsonResponse
    {
        $validated = $request->validate(['round_id' => ['required', 'integer']]);
        $campaignId = $this->balances->campaignId($request->user(), 'crash');
        $round = DB::transaction(function () use ($request, $validated, $campaignId) {
            $round = CrashRound::whereKey($validated['round_id'])
                ->where('usuario_id', $request->user()->id)
                ->where('campaign_challenge_id', $campaignId)->lockForUpdate()->firstOrFail();

            if ($round->estado === 'activa' && $this->currentMultiplier($round) >= $round->crash_point) {
                $this->finish($round, 'crashed', 0, null);
            }

            return $round->fresh();
        });

        return response()->json($this->roundData($round));
    }

    public function cashout(Request $request): JsonResponse
    {
        $validated = $request->validate(['round_id' => ['required', 'integer']]);
        $campaignId = $this->balances->campaignId($request->user(), 'crash');
        $round = DB::transaction(function () use ($request, $validated, $campaignId) {
            $round = CrashRound::whereKey($validated['round_id'])
                ->where('usuario_id', $request->user()->id)
                ->where('campaign_challenge_id', $campaignId)->lockForUpdate()->firstOrFail();

            if ($round->estado !== 'activa') {
                return $round;
            }

            $multiplier = $this->currentMultiplier($round);
            if ($multiplier >= $round->crash_point) {
                $this->finish($round, 'crashed', 0, null);
            } else {
                abort_if($multiplier < 1.01, 422, 'Es demasiado pronto para cobrar.');
                $payout = round($round->apuesta * $multiplier, 2);
                $this->balances->credit(Usuario::findOrFail($round->usuario_id), 'crash', $payout, 'premio_crash', [], $round, $round->campaign_challenge_id);
                $this->finish($round, 'cobrado', $payout, $multiplier);
            }

            return $round->fresh();
        });

        return response()->json($this->roundData($round));
    }

    public function crash(Request $request): JsonResponse
    {
        return $this->status($request);
    }

    private function currentMultiplier(CrashRound $round): float
    {
        $elapsedMilliseconds = max(0, $round->iniciada_at->diffInMilliseconds(now()));
        $raw = 1 + ($elapsedMilliseconds / 1000) * self::MULTIPLIER_PER_SECOND;

        return floor($raw * 100) / 100;
    }

    private function finish(CrashRound $round, string $state, float $payout, ?float $cashoutAt): void
    {
        if ($round->estado !== 'activa') {
            return;
        }

        $round->update([
            'estado' => $state,
            'cashout_at' => $cashoutAt,
            'ganancia' => $payout,
            'finalizada_at' => now(),
        ]);
        $game = Partida::create([
            'usuario_id' => $round->usuario_id,
            'juego' => 'crash',
            'apuesta' => $round->apuesta,
            'ganancia' => $payout,
            'campaign_challenge_id' => $round->campaign_challenge_id,
            'campaign_key' => $round->campaign_key,
            'detalles' => [
                'crash_point' => $round->crash_point,
                'cashout_at' => $cashoutAt,
                'resultado' => $state,
                'round_id' => $round->id,
            ],
        ]);
        $this->balances->recordGame($game);
    }

    private function roundData(CrashRound $round): array
    {
        $finished = $round->estado !== 'activa';

        return [
            'round_id' => $round->id,
            'estado' => $round->estado,
            'multiplier' => $round->estado === 'cobrado'
                ? $round->cashout_at
                : ($round->estado === 'crashed' ? $round->crash_point : $this->currentMultiplier($round)),
            'crash_point' => $finished ? $round->crash_point : null,
            'ganancia' => $round->ganancia,
            'saldo' => $this->balances->balance(Usuario::findOrFail($round->usuario_id), 'crash', $round->campaign_challenge_id),
            'rate_per_second' => self::MULTIPLIER_PER_SECOND,
        ];
    }

    private function generateCrashPoint(): float
    {
        $houseEdge = 0.03;
        $e = 2 ** 32;
        $h = $e * (1 - $houseEdge);

        if ($h <= 0) {
            return 1.0;
        }

        $r = random_int(1, $e - 1);

        if ($r >= $h) {
            return 1.0;
        }

        return round($e / ($e - $r), 2);
    }
}
