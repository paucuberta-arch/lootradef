<?php

namespace App\Services;

use App\Models\Cartera;
use App\Models\CampaignChallengeMovement;
use App\Models\LedgerAccount;
use App\Models\LedgerEntry;
use App\Models\LedgerTransaction;
use App\Models\WalletMovement;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use RuntimeException;

class LedgerService
{
    public function walletMovement(WalletMovement $movement): LedgerTransaction
    {
        return DB::transaction(function () use ($movement) {
            $walletAccount = $this->walletAccount($movement->cartera_id, $movement->usuario_id, (float) $movement->saldo_anterior);
            $counterparty = LedgerAccount::firstOrCreate(
                ['code' => $this->counterpartyCode($movement->tipo, $movement->direccion)],
                ['type' => $this->counterpartyType($movement->tipo), 'currency' => $this->currency(), 'status' => 'active']
            );
            $idempotencyKey = 'wallet-movement:'.$movement->id;
            $existing = LedgerTransaction::where('idempotency_key', $idempotencyKey)->first();
            if ($existing) {
                return $existing;
            }

            $transaction = LedgerTransaction::create([
                'transaction_uuid' => (string) Str::uuid(),
                'type' => $movement->tipo,
                'status' => 'posted',
                'currency' => $this->currency(),
                'idempotency_key' => $idempotencyKey,
                'reference_type' => $movement->referencia_type,
                'reference_id' => $movement->referencia_id,
                'metadata' => $movement->metadatos,
                'occurred_at' => $movement->created_at ?? now(),
            ]);

            $walletDirection = $movement->direccion === 'credito' ? 'credit' : 'debit';
            $counterpartyDirection = $walletDirection === 'credit' ? 'debit' : 'credit';
            $this->entry($transaction, $walletAccount, $walletDirection, (float) $movement->importe, 1);
            $this->entry($transaction, $counterparty, $counterpartyDirection, (float) $movement->importe, 2);

            return $transaction;
        });
    }

    public function campaignMovement(CampaignChallengeMovement $movement): LedgerTransaction
    {
        return DB::transaction(function () use ($movement) {
            $challengeAccount = LedgerAccount::firstOrCreate(
                ['code' => 'campaign-challenge:'.$movement->campaign_challenge_id],
                ['type' => 'campaign_demo_wallet', 'currency' => $this->currency(), 'status' => 'active']
            );
            $counterparty = LedgerAccount::firstOrCreate(
                ['code' => 'platform:campaign-budget'],
                ['type' => 'promotion_budget', 'currency' => $this->currency(), 'status' => 'active']
            );
            $idempotencyKey = 'campaign-movement:'.$movement->id;
            $existing = LedgerTransaction::where('idempotency_key', $idempotencyKey)->first();
            if ($existing) {
                return $existing;
            }

            $transaction = LedgerTransaction::create([
                'transaction_uuid' => (string) Str::uuid(),
                'type' => 'campaign_'.$movement->type,
                'status' => 'posted',
                'currency' => $this->currency(),
                'idempotency_key' => $idempotencyKey,
                'reference_type' => $movement->reference_type,
                'reference_id' => $movement->reference_id,
                'metadata' => $movement->metadata,
                'occurred_at' => $movement->created_at ?? now(),
            ]);
            $challengeDirection = $movement->direction === 'credit' ? 'credit' : 'debit';
            $counterpartyDirection = $challengeDirection === 'credit' ? 'debit' : 'credit';
            $this->entry($transaction, $challengeAccount, $challengeDirection, (float) $movement->amount, 1);
            $this->entry($transaction, $counterparty, $counterpartyDirection, (float) $movement->amount, 2);

            return $transaction;
        });
    }

    public function assertBalanced(LedgerTransaction $transaction): void
    {
        $debits = (float) $transaction->entries()->where('direction', 'debit')->sum('amount');
        $credits = (float) $transaction->entries()->where('direction', 'credit')->sum('amount');
        if (round($debits, 2) !== round($credits, 2)) {
            throw new RuntimeException('La transacción de ledger no está equilibrada.');
        }
    }

    private function walletAccount(int $walletId, int $userId, float $openingBalance): LedgerAccount
    {
        $wallet = Cartera::whereKey($walletId)->lockForUpdate()->firstOrFail();
        if ($wallet->ledger_account_id) {
            return LedgerAccount::whereKey($wallet->ledger_account_id)->lockForUpdate()->firstOrFail();
        }

        $account = LedgerAccount::firstOrCreate(
            ['code' => 'wallet:'.$walletId],
            ['type' => 'player_wallet', 'usuario_id' => $userId, 'currency' => $this->currency(), 'status' => 'active']
        );
        $wallet->update(['ledger_account_id' => $account->id]);

        if ($openingBalance > 0 && ! LedgerTransaction::where('idempotency_key', 'wallet-opening:'.$walletId)->exists()) {
            $opening = LedgerTransaction::create([
                'transaction_uuid' => (string) Str::uuid(), 'type' => 'legacy_opening_balance',
                'status' => 'posted', 'currency' => $this->currency(),
                'idempotency_key' => 'wallet-opening:'.$walletId,
                'metadata' => ['source' => 'existing_wallet_balance'], 'occurred_at' => now(),
            ]);
            $capital = LedgerAccount::firstOrCreate(
                ['code' => 'platform:opening-capital'],
                ['type' => 'platform_capital', 'currency' => $this->currency(), 'status' => 'active']
            );
            $this->entry($opening, $capital, 'debit', $openingBalance, 1);
            $this->entry($opening, $account, 'credit', $openingBalance, 2);
        }

        return $account;
    }

    private function entry(LedgerTransaction $transaction, LedgerAccount $account, string $direction, float $amount, int $line): void
    {
        LedgerEntry::create([
            'ledger_transaction_id' => $transaction->id,
            'ledger_account_id' => $account->id,
            'direction' => $direction,
            'amount' => round($amount, 2),
            'currency' => $this->currency(),
            'line_number' => $line,
        ]);
    }

    private function counterpartyCode(string $type, string $direction): string
    {
        if ($type === 'deposito_demo') {
            return 'platform:demo-treasury';
        }
        if (str_starts_with($type, 'premio') || str_starts_with($type, 'canje')) {
            return 'platform:prize-reserve';
        }
        if (str_starts_with($type, 'bono')) {
            return 'platform:promotion-budget';
        }
        if (str_starts_with($type, 'retirada_demo')) {
            return 'platform:withdrawal-reserve';
        }

        return $direction === 'debito' ? 'platform:game-revenue' : 'platform:game-payouts';
    }

    private function counterpartyType(string $type): string
    {
        return $type === 'deposito_demo' ? 'demo_treasury' : (str_starts_with($type, 'premio') ? 'prize_reserve' : 'platform_income');
    }

    private function currency(): string
    {
        return (string) config('features.economy.currency_code', 'EUR_DEMO');
    }
}
