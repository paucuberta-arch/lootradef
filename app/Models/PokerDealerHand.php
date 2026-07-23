<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PokerDealerHand extends Model
{
    protected $table = 'poker_dealer_hands';

    protected $fillable = [
        'usuario_id', 'request_token', 'campaign_challenge_id', 'campaign_key', 'ante', 'apostado', 'baraja',
        'mano_jugador', 'mano_dealer', 'comunitarias', 'fase', 'resultado',
        'ganancia', 'finalizada_at',
    ];

    protected $casts = [
        'ante' => 'float', 'apostado' => 'float', 'ganancia' => 'float',
        'baraja' => 'array', 'mano_jugador' => 'array', 'mano_dealer' => 'array', 'comunitarias' => 'array',
        'finalizada_at' => 'datetime',
    ];
}
