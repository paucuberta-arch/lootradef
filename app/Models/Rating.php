<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Rating extends Model
{
    protected $table = 'ratings';

    protected $fillable = ['usuario_id', 'juego_slug', 'puntuacion'];

    protected $casts = [
        'puntuacion' => 'integer',
    ];

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'usuario_id');
    }

    public static function promedioJuego(string $slug): float
    {
        return (float) static::where('juego_slug', $slug)->avg('puntuacion');
    }

    public static function totalVotos(string $slug): int
    {
        return (int) static::where('juego_slug', $slug)->count();
    }
}
