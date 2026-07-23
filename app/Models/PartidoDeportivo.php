<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PartidoDeportivo extends Model
{
    protected $table = 'partidos_deportivos';

    protected $fillable = [
        'fixture_key', 'deporte', 'liga', 'local', 'visitante',
        'local_siglas', 'visitante_siglas', 'imagen', 'estado',
        'inicia_at', 'duracion_segundos', 'minuto', 'goles_local',
        'goles_visitante', 'cuota_local', 'cuota_empate', 'cuota_visitante',
        'simulacion', 'eventos',
    ];

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
