<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Review extends Model
{
    protected $table = 'reviews';

    protected $fillable = [
        'usuario_id', 'juego_slug', 'titulo', 'contenido',
        'tipo', 'estado', 'puntuacion', 'likes', 'dislikes',
    ];

    protected $casts = [
        'puntuacion' => 'integer',
        'likes' => 'integer',
        'dislikes' => 'integer',
    ];

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'usuario_id');
    }

    public function votes(): HasMany
    {
        return $this->hasMany(ReviewVote::class);
    }

    public function scopeAprobados($query)
    {
        return $query->where('estado', 'aprobado');
    }

    public function scopeDeJuego($query, string $slug)
    {
        return $query->where('juego_slug', $slug)->where('tipo', 'juego');
    }
}
