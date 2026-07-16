<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PokerDealerHand extends Model
{
    protected $table = 'poker_dealer_hands';

    protected $guarded = [];

    protected $casts = [
        'ante' => 'float', 'apostado' => 'float', 'ganancia' => 'float',
        'baraja' => 'array', 'mano_jugador' => 'array', 'mano_dealer' => 'array', 'comunitarias' => 'array',
        'finalizada_at' => 'datetime',
    ];
}
