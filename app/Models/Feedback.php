<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Feedback extends Model
{
    protected $fillable = [
        'usuario_id', 'tipo', 'asunto', 'contenido',
        'estado', 'prioridad', 'respuesta',
    ];

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'usuario_id');
    }

    public function scopeAbiertos($query)
    {
        return $query->where('estado', '!=', 'cerrado');
    }
}
