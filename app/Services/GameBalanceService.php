<?php

namespace App\Services;

use App\Models\CampaignChallenge;
use App\Models\Partida;
use App\Models\Usuario;
use Illuminate\Database\Eloquent\Model;
use InvalidArgumentException;
use Ramsey\Uuid\Uuid;

class GameBalanceService
{
    public function __construct(
        private readonly CampaignManager $campaigns,
        private readonly CampaignChallengeService $challenges,
    ) {}

    public function campaignId(Usuario $user, string $game): ?int
    {
        if (! $this->campaigns->gameAllowed($game)) {
            return null;
        }

        return $this->challenges->activeForUser($user)?->id;
    }

    public function debit(Usuario $user, string $game, float $amount, string $type, array $metadata = [], ?Model $reference = null, ?string $idempotencyKey = null, ?int $campaignId = null): bool
    {
        $this->assertIdempotencyKey($idempotencyKey);
        $challenge = $this->challenge($user, $game, $campaignId);
        if ($challenge) {
            $scopedIdempotencyKey = $idempotencyKey
                ? Uuid::uuid5(Uuid::NAMESPACE_URL, implode('|', [
                    'lootra-campaign-debit-v1',
                    (string) $challenge->id,
                    $game,
                    $type,
                    $idempotencyKey,
                ]))->toString()
                : null;

            return $this->challenges->debit($challenge, $amount, $type, $metadata, $reference, $scopedIdempotencyKey);
        }

        $wallet = $user->cartera()->first();

        return $wallet?->apostar($amount, $type, $metadata, $reference, $this->walletIdempotencyKey($user, $game, $type, $idempotencyKey)) ?? false;
    }

    public function credit(Usuario $user, string $game, float $amount, string $type, array $metadata = [], ?Model $reference = null, ?int $campaignId = null, ?string $idempotencyKey = null): void
    {
        $this->assertIdempotencyKey($idempotencyKey);
        $challenge = $this->challenge($user, $game, $campaignId);
        if ($challenge) {
            $scopedIdempotencyKey = Uuid::uuid5(Uuid::NAMESPACE_URL, implode('|', [
                'lootra-campaign-credit-v1',
                (string) $challenge->id,
                $game,
                $type,
                $idempotencyKey,
            ]))->toString();
            $this->challenges->credit($challenge, $amount, $type, $metadata, $reference, $scopedIdempotencyKey);

            return;
        }

        $user->cartera()->firstOrFail()->ganar(
            $amount,
            $type,
            $metadata,
            $reference,
            $this->walletIdempotencyKey($user, $game, $type, $idempotencyKey)
        );
    }

    public function balance(Usuario $user, string $game, ?int $campaignId = null): float
    {
        $challenge = $this->challenge($user, $game, $campaignId, false);

        return $challenge ? (float) $challenge->current_balance : (float) ($user->cartera()->value('saldo') ?? 0);
    }

    public function recordGame(Partida $game): void
    {
        $this->challenges->recordGame($game);
    }

    private function challenge(Usuario $user, string $game, ?int $campaignId, bool $failForExplicit = true): ?CampaignChallenge
    {
        if ($campaignId) {
            $challenge = CampaignChallenge::whereKey($campaignId)->where('user_id', $user->id)->first();
            if ($failForExplicit) {
                abort_unless($challenge, 404);
                $active = $this->challenges->activeForUser($user);
                abort_unless($active?->id === $challenge->id, 409, 'El reto ha finalizado.');

                return $active;
            }

            return $challenge;
        }

        return $this->campaigns->gameAllowed($game) ? $this->challenges->activeForUser($user) : null;
    }

    private function assertIdempotencyKey(?string $idempotencyKey): void
    {
        if (! is_string($idempotencyKey) || trim($idempotencyKey) === '') {
            throw new InvalidArgumentException('Las operaciones de juego requieren una clave de idempotencia.');
        }
    }

    private function walletIdempotencyKey(Usuario $user, string $game, string $type, string $idempotencyKey): string
    {
        return Uuid::uuid5(Uuid::NAMESPACE_URL, implode('|', [
            'lootra-wallet-movement-v1',
            (string) $user->id,
            $game,
            $type,
            $idempotencyKey,
        ]))->toString();
    }
}
