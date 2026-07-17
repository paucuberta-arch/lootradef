<?php

namespace App\Services;

use App\Models\CampaignAttribution;
use App\Models\CampaignChallenge;
use App\Models\CampaignEvent;
use App\Models\Usuario;
use Illuminate\Http\Request;

class CampaignAnalytics
{
    public function __construct(private readonly CampaignManager $campaigns) {}

    public function record(
        string $event,
        string $dedupe,
        array $metadata = [],
        ?Usuario $user = null,
        ?CampaignChallenge $challenge = null,
        ?CampaignAttribution $attribution = null,
        ?Request $request = null,
    ): CampaignEvent {
        $request ??= request();
        $origin = $challenge?->data_origin ?? 'real';
        $isDemo = (bool) ($challenge?->is_demo ?? false);

        return CampaignEvent::firstOrCreate(
            ['dedupe_key' => CampaignManager::KEY.':'.$event.':'.$dedupe],
            [
                'user_id' => $user?->id ?? $request->user()?->id,
                'campaign_challenge_id' => $challenge?->id,
                'campaign_attribution_id' => $attribution?->id,
                'campaign_key' => CampaignManager::KEY,
                'event' => $event,
                'session_id' => $request->hasSession() ? $this->campaigns->sessionHash($request) : null,
                'source' => 'server',
                'is_demo' => $isDemo,
                'data_origin' => $origin,
                'metadata' => $metadata ?: null,
                'occurred_at' => now(),
            ]
        );
    }
}
