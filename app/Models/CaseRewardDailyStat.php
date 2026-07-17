<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CaseRewardDailyStat extends Model
{
    protected $fillable = ['case_reward_setting_id', 'award_date', 'good_awarded'];

    protected $casts = [
        'award_date' => 'date',
        'good_awarded' => 'integer',
    ];

    public function setting(): BelongsTo
    {
        return $this->belongsTo(CaseRewardSetting::class, 'case_reward_setting_id');
    }
}
