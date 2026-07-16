<?php

namespace App\Services;

use App\Models\Cartera;
use App\Models\WalletMovement;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class WalletService
{
    public function debit(
        Cartera $wallet,
        float $amount,
        string $type,
        array $metadata = [],
        ?Model $reference = null,
        ?string $idempotencyKey = null,
    ): bool {
        return DB::transaction(function () use ($wallet, $amount, $type, $metadata, $reference, $idempotencyKey) {
            $locked = Cartera::whereKey($wallet->id)->lockForUpdate()->firstOrFail();
            $amount = $this->normalizeAmount($amount);

            if ($idempotencyKey && WalletMovement::where('idempotency_key', $idempotencyKey)->exists()) {
                $wallet->refresh();

                return true;
            }
            if ($locked->saldo < $amount) {
                return false;
            }

            $before = (float) $locked->saldo;
            $after = round($before - $amount, 2);
            $locked->update(['saldo' => $after]);
            $this->record($locked, $type, 'debito', $amount, $before, $after, $metadata, $reference, $idempotencyKey);
            $wallet->setRawAttributes($locked->getAttributes(), true);

            return true;
        });
    }

    public function credit(
        Cartera $wallet,
        float $amount,
        string $type,
        array $metadata = [],
        ?Model $reference = null,
        ?string $idempotencyKey = null,
    ): WalletMovement {
        return DB::transaction(function () use ($wallet, $amount, $type, $metadata, $reference, $idempotencyKey) {
            $locked = Cartera::whereKey($wallet->id)->lockForUpdate()->firstOrFail();
            if ($idempotencyKey && $existing = WalletMovement::where('idempotency_key', $idempotencyKey)->first()) {
                $wallet->refresh();

                return $existing;
            }

            $amount = $this->normalizeAmount($amount);
            $before = (float) $locked->saldo;
            $after = round($before + $amount, 2);
            $locked->update(['saldo' => $after]);
            $movement = $this->record($locked, $type, 'credito', $amount, $before, $after, $metadata, $reference, $idempotencyKey);
            $wallet->setRawAttributes($locked->getAttributes(), true);

            return $movement;
        });
    }

    public function setBalance(Cartera $wallet, float $balance, string $type, array $metadata = []): ?WalletMovement
    {
        $balance = round($balance, 2);
        if ($balance < 0) {
            throw new InvalidArgumentException('El saldo no puede ser negativo.');
        }

        return DB::transaction(function () use ($wallet, $balance, $type, $metadata) {
            $locked = Cartera::whereKey($wallet->id)->lockForUpdate()->firstOrFail();
            $current = (float) $locked->saldo;
            if ($balance === $current) {
                return null;
            }

            return $balance > $current
                ? $this->credit($locked, $balance - $current, $type, $metadata)
                : ($this->debit($locked, $current - $balance, $type, $metadata) ? $locked->movimientos()->latest()->first() : null);
        });
    }

    private function normalizeAmount(float $amount): float
    {
        $amount = round($amount, 2);
        if ($amount <= 0) {
            throw new InvalidArgumentException('El importe debe ser mayor que cero.');
        }

        return $amount;
    }

    private function record(
        Cartera $wallet,
        string $type,
        string $direction,
        float $amount,
        float $before,
        float $after,
        array $metadata,
        ?Model $reference,
        ?string $idempotencyKey,
    ): WalletMovement {
        return WalletMovement::create([
            'usuario_id' => $wallet->usuario_id,
            'cartera_id' => $wallet->id,
            'tipo' => $type,
            'direccion' => $direction,
            'importe' => $amount,
            'saldo_anterior' => $before,
            'saldo_posterior' => $after,
            'referencia_type' => $reference?->getMorphClass(),
            'referencia_id' => $reference?->getKey(),
            'estado' => 'confirmado',
            'idempotency_key' => $idempotencyKey,
            'metadatos' => $metadata ?: null,
        ]);
    }
}
