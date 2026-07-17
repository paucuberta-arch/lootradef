<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CampaignChallenge extends Model
{
    public const PENDING = 'pending';

    public const ACTIVE = 'active';

    public const COMPLETED = 'completed';

    public const EXPIRED = 'expired';

    public const DISQUALIFIED = 'disqualified';

    protected $fillable = [
        'user_id', 'campaign_key', 'public_alias', 'status', 'initial_balance',
        'current_balance', 'final_balance', 'score', 'started_at', 'expires_at',
        'completed_at', 'games_played', 'is_demo', 'data_origin', 'metadata',
    ];

    protected $casts = [
        'initial_balance' => 'float', 'current_balance' => 'float',
        'final_balance' => 'float', 'score' => 'float',
        'started_at' => 'datetime', 'expires_at' => 'datetime', 'completed_at' => 'datetime',
        'is_demo' => 'boolean', 'metadata' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'user_id');
    }

    public function movements(): HasMany
    {
        return $this->hasMany(CampaignChallengeMovement::class);
    }

    public function games(): HasMany
    {
        return $this->hasMany(Partida::class);
    }

    public function getSecondsRemainingAttribute(): int
    {
        return $this->status === self::ACTIVE && $this->expires_at
            ? max(0, now()->diffInSeconds($this->expires_at, false))
            : 0;
    }
}
