<?php

namespace App\Http\Controllers;

use App\Models\Cartera;
use App\Models\CrashRound;
use App\Models\Partida;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class CrashController extends Controller
{
    private const MULTIPLIER_PER_SECOND = 0.20;

    public function index(Request $request): View
    {
        $active = DB::transaction(function () use ($request) {
            $round = CrashRound::where('usuario_id', $request->user()->id)
                ->where('estado', 'activa')->lockForUpdate()->latest()->first();

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
        ]);
    }

    public function play(Request $request): JsonResponse
    {
        $validated = $request->validate(['apuesta' => ['required', 'numeric', 'min:0.10', 'max:1000']]);

        $round = DB::transaction(function () use ($request, $validated) {
            $wallet = Cartera::where('usuario_id', $request->user()->id)->lockForUpdate()->first();
            abort_unless($wallet, 422, 'No tienes una cartera activa.');

            $active = CrashRound::where('usuario_id', $request->user()->id)
                ->where('estado', 'activa')->lockForUpdate()->latest()->first();
            if ($active && $this->currentMultiplier($active) >= $active->crash_point) {
                $this->finish($active, 'crashed', 0, null);
                $active = null;
            }
            abort_if($active, 409, 'Ya tienes una ronda Crash activa.');

            $bet = round((float) $validated['apuesta'], 2);
            abort_if($wallet->saldo < $bet, 422, 'Saldo insuficiente.');
            $round = CrashRound::create([
                'usuario_id' => $request->user()->id,
                'apuesta' => $bet,
                'crash_point' => $this->generateCrashPoint(),
                'estado' => 'activa',
                'iniciada_at' => now(),
            ]);
            abort_unless($wallet->apostar($bet, 'apuesta_crash', [], $round), 422, 'Saldo insuficiente.');

            return $round;
        });

        return response()->json($this->roundData($round) + ['ok' => true], 201);
    }

    public function status(Request $request): JsonResponse
    {
        $validated = $request->validate(['round_id' => ['required', 'integer']]);
        $round = DB::transaction(function () use ($request, $validated) {
            $round = CrashRound::whereKey($validated['round_id'])
                ->where('usuario_id', $request->user()->id)->lockForUpdate()->firstOrFail();

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
        $round = DB::transaction(function () use ($request, $validated) {
            $round = CrashRound::whereKey($validated['round_id'])
                ->where('usuario_id', $request->user()->id)->lockForUpdate()->firstOrFail();

            if ($round->estado !== 'activa') {
                return $round;
            }

            $multiplier = $this->currentMultiplier($round);
            if ($multiplier >= $round->crash_point) {
                $this->finish($round, 'crashed', 0, null);
            } else {
                abort_if($multiplier < 1.01, 422, 'Es demasiado pronto para cobrar.');
                $payout = round($round->apuesta * $multiplier, 2);
                $wallet = Cartera::where('usuario_id', $round->usuario_id)->lockForUpdate()->firstOrFail();
                $wallet->ganar($payout, 'premio_crash', [], $round);
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
        Partida::create([
            'usuario_id' => $round->usuario_id,
            'juego' => 'crash',
            'apuesta' => $round->apuesta,
            'ganancia' => $payout,
            'detalles' => [
                'crash_point' => $round->crash_point,
                'cashout_at' => $cashoutAt,
                'resultado' => $state,
                'round_id' => $round->id,
            ],
        ]);
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
            'saldo' => (float) (Cartera::where('usuario_id', $round->usuario_id)->value('saldo') ?? 0),
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
