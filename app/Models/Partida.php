<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Partida extends Model
{
    protected $table = 'partidas';

    protected $fillable = [
        'usuario_id',
        'juego',
        'apuesta',
        'ganancia',
        'detalles',
    ];

    protected $casts = [
        'apuesta' => 'decimal:2',
        'ganancia' => 'decimal:2',
        'detalles' => 'array',
    ];

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'usuario_id');
    }
}
