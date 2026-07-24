<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GameActionToken extends Model
{
    protected $fillable = ['usuario_id', 'game_key', 'hand_id', 'request_token', 'action'];
}
