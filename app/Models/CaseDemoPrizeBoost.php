<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CaseDemoPrizeBoost extends Model
{
    protected $fillable = ['user_id', 'multiplier', 'reason', 'expires_at', 'created_by'];

    protected $casts = [
        'multiplier' => 'float',
        'expires_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'user_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'created_by');
    }
}
