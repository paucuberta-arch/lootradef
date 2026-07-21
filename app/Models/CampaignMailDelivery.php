<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CampaignMailDelivery extends Model
{
    protected $fillable = [
        'user_id', 'campaign_key', 'message_key', 'status', 'sent_at', 'failure',
    ];

    protected $casts = ['sent_at' => 'datetime'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'user_id');
    }
}
