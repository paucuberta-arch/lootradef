<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class CampaignChallengeMovement extends Model
{
    protected $fillable = [
        'campaign_challenge_id', 'type', 'direction', 'amount', 'balance_before',
        'balance_after', 'reference_type', 'reference_id', 'idempotency_key', 'metadata',
    ];

    protected $casts = [
        'amount' => 'float', 'balance_before' => 'float', 'balance_after' => 'float', 'metadata' => 'array',
    ];

    public function challenge(): BelongsTo
    {
        return $this->belongsTo(CampaignChallenge::class, 'campaign_challenge_id');
    }

    public function reference(): MorphTo
    {
        return $this->morphTo();
    }
}
