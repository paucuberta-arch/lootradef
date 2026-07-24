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
        'math_version',
        'request_token',
        'apuesta',
        'ganancia',
        'detalles',
        'campaign_challenge_id',
        'campaign_key',
    ];

    protected $casts = [
        'apuesta' => 'decimal:2',
        'ganancia' => 'decimal:2',
        'detalles' => 'array',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $partida): void {
            $partida->math_version ??= app(\App\Services\GameMathVersionService::class)->forGame($partida->juego);
        });
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'usuario_id');
    }

    public function campaignChallenge(): BelongsTo
    {
        return $this->belongsTo(CampaignChallenge::class);
    }
}
