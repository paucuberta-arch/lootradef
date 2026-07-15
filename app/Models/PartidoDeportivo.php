<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PartidoDeportivo extends Model
{
    protected $table = 'partidos_deportivos';

    protected $guarded = [];

    protected $casts = [
        'inicia_at' => 'datetime',
        'simulacion' => 'array',
        'eventos' => 'array',
        'cuota_local' => 'float',
        'cuota_empate' => 'float',
        'cuota_visitante' => 'float',
    ];

    public function apuestas(): HasMany
    {
        return $this->hasMany(ApuestaDeportiva::class, 'partido_id');
    }
}
