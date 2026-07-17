<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CampaignEvent extends Model
{
    protected $fillable = [
        'user_id', 'campaign_challenge_id', 'campaign_attribution_id', 'campaign_key',
        'event', 'session_id', 'dedupe_key', 'source', 'is_demo', 'data_origin',
        'metadata', 'occurred_at',
    ];

    protected $casts = ['is_demo' => 'boolean', 'metadata' => 'array', 'occurred_at' => 'datetime'];
}
