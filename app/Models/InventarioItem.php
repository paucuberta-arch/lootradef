<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InventarioItem extends Model
{
    protected $table = 'inventario_items';

    protected $fillable = [
        'usuario_id', 'request_token', 'caja', 'nombre', 'imagen', 'rareza',
        'precio_caja', 'valor_canje', 'estado', 'canjeado_at',
    ];

    protected $casts = [
        'precio_caja' => 'float',
        'valor_canje' => 'float',
        'canjeado_at' => 'datetime',
    ];

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'usuario_id');
    }
}
