<?php

namespace App\Services;

use App\Models\CampaignChallenge;
use App\Models\CampaignChallengeMovement;
use App\Models\Partida;
use App\Models\Usuario;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use InvalidArgumentException;

class CampaignChallengeService
{
    public function __construct(
        private readonly CampaignManager $campaigns,
        private readonly CampaignAnalytics $analytics,
    ) {}

    public function start(Usuario $user): CampaignChallenge
    {
        abort_unless($this->campaigns->enabled(), 404, 'La campaña no está disponible.');

        return DB::transaction(function () use ($user) {
            Usuario::whereKey($user->id)->lockForUpdate()->firstOrFail();
            $existing = CampaignChallenge::where('user_id', $user->id)
                ->where('campaign_key', CampaignManager::KEY)->lockForUpdate()->first();
            if ($existing) {
                return $this->syncState($existing);
            }

            $config = $this->campaigns->config();
            $balance = round((float) $config['initial_balance'], 2);
            $firstName = Str::of($user->name)->before(' ')->replaceMatches('/[^\pL\pN_-]/u', '')->limit(24, '')->value() ?: 'Jugador';
            $challenge = CampaignChallenge::create([
                'user_id' => $user->id,
                'campaign_key' => CampaignManager::KEY,
                'public_alias' => $firstName.'#'.str_pad(base_convert((string) $user->id, 10, 36), 3, '0', STR_PAD_LEFT),
                'status' => CampaignChallenge::ACTIVE,
                'initial_balance' => $balance,
                'current_balance' => $balance,
                'started_at' => now(),
                'expires_at' => now()->addMinutes((int) $config['duration_minutes']),
                'data_origin' => app()->environment('testing') ? 'test' : 'real',
            ]);
            CampaignChallengeMovement::create([
                'campaign_challenge_id' => $challenge->id,
                'type' => 'initial_credit', 'direction' => 'credit', 'amount' => $balance,
                'balance_before' => 0, 'balance_after' => $balance,
                'metadata' => ['campaign_key' => CampaignManager::KEY],
            ]);
            $this->analytics->record('challenge_started', 'challenge-'.$challenge->id, [], $user, $challenge);

            return $challenge;
        });
    }

    public function activeForUser(Usuario $user): ?CampaignChallenge
    {
        return DB::transaction(function () use ($user) {
            $challenge = CampaignChallenge::where('user_id', $user->id)
                ->where('campaign_key', CampaignManager::KEY)
                ->where('status', CampaignChallenge::ACTIVE)->lockForUpdate()->first();

            return $challenge ? $this->syncState($challenge) : null;
        });
    }

    public function findForUser(Usuario $user): ?CampaignChallenge
    {
        $challenge = CampaignChallenge::where('user_id', $user->id)
            ->where('campaign_key', CampaignManager::KEY)->first();

        return $challenge?->status === CampaignChallenge::ACTIVE ? $this->activeForUser($user) : $challenge;
    }

    public function debit(CampaignChallenge $challenge, float $amount, string $type, array $metadata = [], ?Model $reference = null, ?string $idempotencyKey = null): bool
    {
        return DB::transaction(function () use ($challenge, $amount, $type, $metadata, $reference, $idempotencyKey) {
            $locked = CampaignChallenge::whereKey($challenge->id)->lockForUpdate()->firstOrFail();
            $locked = $this->assertPlayable($locked);
            $amount = $this->amount($amount);
            if ($idempotencyKey) {
                $existing = CampaignChallengeMovement::where('idempotency_key', $idempotencyKey)->first();
                if ($existing) {
                    abort_unless(
                        $existing->campaign_challenge_id === $locked->id
                        && $existing->direction === 'debit'
                        && $existing->type === $type
                        && (float) $existing->amount === $amount,
                        409,
                        'La clave de idempotencia ya fue usada para otra operación.'
                    );
                    $challenge->refresh();

                    return true;
                }
            }
            if ($locked->current_balance < $amount) {
                return false;
            }
            $before = (float) $locked->current_balance;
            $after = round($before - $amount, 2);
            $locked->update(['current_balance' => $after]);
            $this->movement($locked, $type, 'debit', $amount, $before, $after, $metadata, $reference, $idempotencyKey);
            $challenge->setRawAttributes($locked->getAttributes(), true);

            return true;
        });
    }

    public function credit(CampaignChallenge $challenge, float $amount, string $type, array $metadata = [], ?Model $reference = null): void
    {
        DB::transaction(function () use ($challenge, $amount, $type, $metadata, $reference) {
            $locked = CampaignChallenge::whereKey($challenge->id)->lockForUpdate()->firstOrFail();
            $locked = $this->assertPlayable($locked);
            $amount = $this->amount($amount);
            $before = (float) $locked->current_balance;
            $after = round($before + $amount, 2);
            $locked->update(['current_balance' => $after]);
            $this->movement($locked, $type, 'credit', $amount, $before, $after, $metadata, $reference, null);
            $challenge->setRawAttributes($locked->getAttributes(), true);
        });
    }

    public function recordGame(Partida $game): void
    {
        if (! $game->campaign_challenge_id) {
            return;
        }

        DB::transaction(function () use ($game) {
            $challenge = CampaignChallenge::whereKey($game->campaign_challenge_id)->lockForUpdate()->firstOrFail();
            if ($challenge->status !== CampaignChallenge::ACTIVE) {
                return;
            }
            $challenge->increment('games_played');
            $this->analytics->record('game_completed', 'game-'.$game->id, ['game' => $game->juego], $challenge->user, $challenge);
        });
    }

    public function complete(Usuario $user): CampaignChallenge
    {
        return DB::transaction(function () use ($user) {
            $challenge = CampaignChallenge::where('user_id', $user->id)
                ->where('campaign_key', CampaignManager::KEY)->lockForUpdate()->firstOrFail();
            if ($challenge->status === CampaignChallenge::ACTIVE) {
                $this->finalize($challenge, CampaignChallenge::COMPLETED, 'manual');
            }

            return $challenge->fresh();
        });
    }

    private function syncState(CampaignChallenge $challenge): CampaignChallenge
    {
        if ($challenge->status === CampaignChallenge::ACTIVE
            && (! $this->campaigns->enabled() || ! $challenge->expires_at || now()->greaterThanOrEqualTo($challenge->expires_at))) {
            $this->finalize($challenge, CampaignChallenge::EXPIRED, $this->campaigns->enabled() ? 'time' : 'campaign_disabled');
        }

        return $challenge->fresh();
    }

    private function assertPlayable(CampaignChallenge $challenge): CampaignChallenge
    {
        $challenge = $this->syncState($challenge);
        abort_unless($challenge->status === CampaignChallenge::ACTIVE, 409, 'El reto ha finalizado. El resultado ya está congelado.');

        return $challenge;
    }

    private function finalize(CampaignChallenge $challenge, string $status, string $reason): void
    {
        if ($challenge->status !== CampaignChallenge::ACTIVE) {
            return;
        }
        $balance = round((float) $challenge->current_balance, 2);
        $challenge->update([
            'status' => $status,
            'final_balance' => $balance,
            'score' => floor($balance),
            'completed_at' => now(),
            'metadata' => [...($challenge->metadata ?? []), 'completion_reason' => $reason],
        ]);
        $this->analytics->record('challenge_completed', 'challenge-'.$challenge->id, ['status' => $status, 'reason' => $reason], $challenge->user, $challenge);
    }

    private function movement(CampaignChallenge $challenge, string $type, string $direction, float $amount, float $before, float $after, array $metadata, ?Model $reference, ?string $idempotencyKey): void
    {
        CampaignChallengeMovement::create([
            'campaign_challenge_id' => $challenge->id, 'type' => $type, 'direction' => $direction,
            'amount' => $amount, 'balance_before' => $before, 'balance_after' => $after,
            'reference_type' => $reference?->getMorphClass(), 'reference_id' => $reference?->getKey(),
            'idempotency_key' => $idempotencyKey, 'metadata' => $metadata ?: null,
        ]);
    }

    private function amount(float $amount): float
    {
        $amount = round($amount, 2);
        if ($amount <= 0) {
            throw new InvalidArgumentException('El importe debe ser mayor que cero.');
        }

        return $amount;
    }
}
