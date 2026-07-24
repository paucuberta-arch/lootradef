<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InventarioItem extends Model
{
    protected $table = 'inventario_items';

    protected $fillable = [
        'usuario_id', 'request_token', 'caja', 'nombre', 'imagen', 'rareza',
        'precio_caja', 'valor_canje', 'valor_virtual', 'estado', 'canjeado_at',
    ];

    protected $casts = [
        'precio_caja' => 'float',
        'valor_canje' => 'float',
        'valor_virtual' => 'integer',
        'canjeado_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $item): void {
            if ((int) ($item->valor_virtual ?? 0) === 0 && $item->valor_canje !== null) {
                $item->valor_virtual = (int) round((float) $item->valor_canje * 100);
            }
        });
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'usuario_id');
    }
}
