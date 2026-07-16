<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BlackjackHand extends Model
{
    protected $table = 'blackjack_hands';

    protected $guarded = [];

    protected $casts = [
        'apuesta' => 'float',
        'ganancia' => 'float',
        'baraja' => 'array',
        'mano_jugador' => 'array',
        'mano_dealer' => 'array',
        'finalizada_at' => 'datetime',
    ];
}
