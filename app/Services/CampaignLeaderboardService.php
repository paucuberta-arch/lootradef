<?php

namespace App\Services;

use App\Models\CampaignChallenge;
use Illuminate\Database\Eloquent\Builder;

class CampaignLeaderboardService
{
    public function query(): Builder
    {
        return CampaignChallenge::query()
            ->where('campaign_key', CampaignManager::KEY)
            ->whereIn('status', [CampaignChallenge::COMPLETED, CampaignChallenge::EXPIRED])
            ->where('is_demo', false)
            ->where('data_origin', 'real')
            ->whereHas('user', fn (Builder $query) => $query
                ->where('is_demo', false)->where('data_origin', 'real')
                ->whereDoesntHave('roles', fn (Builder $roles) => $roles->whereIn('name', ['super_admin', 'admin', 'moderator'])))
            ->with('user:id,name')
            ->orderByDesc('score')
            ->orderByDesc('final_balance')
            ->orderBy('completed_at')
            ->orderBy('id');
    }

    public function position(?CampaignChallenge $challenge): ?int
    {
        if (! $challenge || $challenge->is_demo || $challenge->data_origin !== 'real'
            || ! in_array($challenge->status, [CampaignChallenge::COMPLETED, CampaignChallenge::EXPIRED], true)) {
            return null;
        }

        $higher = (clone $this->query())->where(function (Builder $query) use ($challenge) {
            $query->where('score', '>', $challenge->score)
                ->orWhere(function (Builder $tie) use ($challenge) {
                    $tie->where('score', $challenge->score)->where('id', '<', $challenge->id);
                });
        })->count();

        return $higher + 1;
    }
}
