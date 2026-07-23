<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ApuestaDeportiva extends Model
{
    protected $table = 'apuestas_deportivas';

    protected $fillable = [
        'usuario_id', 'partido_id', 'request_token', 'seleccion',
        'cuota', 'importe', 'ganancia', 'estado', 'liquidada_at',
    ];

    protected $casts = [
        'cuota' => 'float',
        'importe' => 'float',
        'ganancia' => 'float',
        'liquidada_at' => 'datetime',
    ];

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'usuario_id');
    }

    public function partido(): BelongsTo
    {
        return $this->belongsTo(PartidoDeportivo::class, 'partido_id');
    }
}
