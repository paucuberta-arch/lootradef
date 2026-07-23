<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class WalletMovement extends Model
{
    protected $fillable = [
        'usuario_id', 'cartera_id', 'tipo', 'direccion', 'importe',
        'saldo_anterior', 'saldo_posterior', 'referencia_type', 'referencia_id',
        'estado', 'idempotency_key', 'metadatos',
    ];

    protected $casts = [
        'importe' => 'float',
        'saldo_anterior' => 'float',
        'saldo_posterior' => 'float',
        'metadatos' => 'array',
    ];

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'usuario_id');
    }

    public function cartera(): BelongsTo
    {
        return $this->belongsTo(Cartera::class, 'cartera_id');
    }

    public function referencia(): MorphTo
    {
        return $this->morphTo();
    }

    public function getEtiquetaAttribute(): string
    {
        return [
            'deposito_demo' => 'Depósito demo',
            'bono_registro' => 'Bono de registro',
            'apuesta_original' => 'Apuesta en Originals',
            'premio_original' => 'Premio de Originals',
            'apuesta_slots' => 'Apuesta en slots',
            'premio_slots' => 'Premio de slots',
            'apuesta_ruleta' => 'Apuesta en ruleta',
            'premio_ruleta' => 'Premio de ruleta',
            'apuesta_crash' => 'Apuesta en Crash',
            'premio_crash' => 'Premio de Crash',
            'apertura_caja' => 'Apertura de caja',
            'canje_inventario' => 'Canje de inventario',
            'apuesta_blackjack' => 'Apuesta en blackjack',
            'premio_blackjack' => 'Premio de blackjack',
            'ante_poker_dealer' => 'Ante de póker',
            'igualar_poker_dealer' => 'Igualar en póker',
            'apuesta_poker_dealer' => 'Apuesta por calle en póker',
            'premio_poker_dealer' => 'Premio de póker',
            'apuesta_deportiva' => 'Apuesta deportiva',
            'premio_apuesta_deportiva' => 'Premio de apuesta deportiva',
            'ajuste_administrador' => 'Ajuste de administración',
        ][$this->tipo] ?? str($this->tipo)->replace('_', ' ')->title()->toString();
    }
}
