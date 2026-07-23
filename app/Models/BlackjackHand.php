<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BlackjackHand extends Model
{
    protected $table = 'blackjack_hands';

    protected $fillable = [
        'usuario_id', 'request_token', 'variante', 'apuesta', 'baraja',
        'mano_jugador', 'mano_dealer', 'estado', 'ganancia', 'finalizada_at',
        'campaign_challenge_id', 'campaign_key',
    ];

    protected $casts = [
        'apuesta' => 'float',
        'ganancia' => 'float',
        'baraja' => 'array',
        'mano_jugador' => 'array',
        'mano_dealer' => 'array',
        'finalizada_at' => 'datetime',
    ];
}
