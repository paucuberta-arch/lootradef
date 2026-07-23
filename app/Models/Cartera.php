<?php

namespace App\Models;

use App\Services\WalletService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Cartera extends Model
{
    protected $table = 'carteras';

    protected $fillable = [
        'usuario_id',
        'saldo',
    ];

    protected $casts = [
        'saldo' => 'float',
    ];

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'usuario_id');
    }

    public function movimientos(): HasMany
    {
        return $this->hasMany(WalletMovement::class, 'cartera_id');
    }

    public function tieneSaldo(float $cantidad): bool
    {
        return $this->saldo >= $cantidad;
    }

    public function apostar(float $cantidad, string $tipo = 'apuesta', array $metadatos = [], ?Model $referencia = null, ?string $idempotencyKey = null): bool
    {
        return app(WalletService::class)->debit($this, $cantidad, $tipo, $metadatos, $referencia, $idempotencyKey);
    }

    public function ganar(float $cantidad, string $tipo = 'premio', array $metadatos = [], ?Model $referencia = null): void
    {
        app(WalletService::class)->credit($this, $cantidad, $tipo, $metadatos, $referencia);
    }
}
