<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CasePrizeRule extends Model
{
    protected $fillable = [
        'case_reward_setting_id', 'prize_key', 'name', 'probability', 'is_good',
    ];

    protected $casts = [
        'probability' => 'float',
        'is_good' => 'boolean',
    ];

    public function setting(): BelongsTo
    {
        return $this->belongsTo(CaseRewardSetting::class, 'case_reward_setting_id');
    }
}
