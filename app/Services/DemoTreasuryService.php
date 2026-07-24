<?php

namespace App\Services;

use App\Models\DemoTreasury;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class DemoTreasuryService
{
    public function current(): DemoTreasury
    {
        return DemoTreasury::firstOrCreate(
            ['code' => 'main'],
            [
                'available_balance' => config('features.economy.treasury_initial_balance', 100000),
                'reserved_balance' => 0,
                'currency' => config('features.economy.currency_code', 'EUR_DEMO'),
                'status' => 'active',
            ]
        );
    }

    public function fund(float $amount): void
    {
        $amount = $this->amount($amount);
        DB::transaction(function () use ($amount): void {
            $treasury = DemoTreasury::where('code', 'main')->lockForUpdate()->first() ?? $this->current();
            $treasury->increment('available_balance', $amount);
        });
    }

    public function reserve(float $amount): void
    {
        $amount = $this->amount($amount);
        DB::transaction(function () use ($amount): void {
            $treasury = DemoTreasury::where('code', 'main')->lockForUpdate()->first() ?? $this->current();
            abort_if((float) $treasury->available_balance < $amount, 422, 'La tesorería demo no tiene liquidez suficiente.');
            $treasury->update([
                'available_balance' => round((float) $treasury->available_balance - $amount, 2),
                'reserved_balance' => round((float) $treasury->reserved_balance + $amount, 2),
            ]);
        });
    }

    public function release(float $amount): void
    {
        $amount = $this->amount($amount);
        DB::transaction(function () use ($amount): void {
            $treasury = DemoTreasury::where('code', 'main')->lockForUpdate()->firstOrFail();
            abort_if((float) $treasury->reserved_balance < $amount, 409, 'La reserva demo no es consistente.');
            $treasury->update([
                'available_balance' => round((float) $treasury->available_balance + $amount, 2),
                'reserved_balance' => round((float) $treasury->reserved_balance - $amount, 2),
            ]);
        });
    }

    public function consumeReserved(float $amount): void
    {
        $amount = $this->amount($amount);
        DB::transaction(function () use ($amount): void {
            $treasury = DemoTreasury::where('code', 'main')->lockForUpdate()->firstOrFail();
            abort_if((float) $treasury->reserved_balance < $amount, 409, 'La reserva demo no es consistente.');
            $treasury->decrement('reserved_balance', $amount);
        });
    }

    private function amount(float $amount): float
    {
        $amount = round($amount, 2);
        if ($amount <= 0) {
            throw new RuntimeException('El importe demo debe ser mayor que cero.');
        }

        return $amount;
    }
}
