<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CrashRound extends Model
{
    protected $table = 'crash_rounds';

    protected $fillable = [
        'usuario_id', 'request_token', 'apuesta', 'crash_point', 'estado',
        'cashout_at', 'ganancia', 'iniciada_at', 'finalizada_at',
        'campaign_challenge_id', 'campaign_key',
    ];

    protected $casts = [
        'apuesta' => 'float',
        'crash_point' => 'float',
        'cashout_at' => 'float',
        'ganancia' => 'float',
        'iniciada_at' => 'datetime',
        'finalizada_at' => 'datetime',
    ];

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'usuario_id');
    }
}
