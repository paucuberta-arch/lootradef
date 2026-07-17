<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CaseRewardSetting extends Model
{
    protected $fillable = ['case_key', 'good_daily_cap'];

    protected $casts = ['good_daily_cap' => 'integer'];

    public function prizeRules(): HasMany
    {
        return $this->hasMany(CasePrizeRule::class);
    }

    public function dailyStats(): HasMany
    {
        return $this->hasMany(CaseRewardDailyStat::class);
    }
}
