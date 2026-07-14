<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Cartera extends Model
{
    protected $table = 'carteras';

    protected $fillable = [
        'usuario_id',
        'saldo',
    ];

    protected $casts = [
        'saldo' => 'decimal:2',
    ];

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'usuario_id');
    }

    public function tieneSaldo(float $cantidad): bool
    {
        return $this->saldo >= $cantidad;
    }

    public function apostar(float $cantidad): bool
    {
        if (!$this->tieneSaldo($cantidad)) {
            return false;
        }
        $this->decrement('saldo', $cantidad);
        return true;
    }

    public function ganar(float $cantidad): void
    {
        $this->increment('saldo', $cantidad);
    }
}
