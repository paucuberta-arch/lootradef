<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CampaignAttribution extends Model
{
    protected $fillable = [
        'user_id', 'session_id', 'campaign_key', 'utm_source', 'utm_medium',
        'utm_campaign', 'utm_content', 'referrer', 'creator_code',
        'first_touch_at', 'converted_at', 'is_demo', 'data_origin', 'metadata',
    ];

    protected $casts = [
        'first_touch_at' => 'datetime', 'converted_at' => 'datetime',
        'is_demo' => 'boolean', 'metadata' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'user_id');
    }
}
