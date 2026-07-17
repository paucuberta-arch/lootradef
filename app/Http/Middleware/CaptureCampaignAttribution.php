<?php

namespace App\Http\Middleware;

use App\Models\CampaignAttribution;
use App\Services\CampaignAnalytics;
use App\Services\CampaignManager;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CaptureCampaignAttribution
{
    public function __construct(
        private readonly CampaignManager $campaigns,
        private readonly CampaignAnalytics $analytics,
    ) {}

    public function handle(Request $request, Closure $next): Response
    {
        $attribution = $this->campaigns->captureAttribution($request);

        if ($request->user() && $attribution) {
            $attribution->update(['user_id' => $request->user()->id]);
            $this->recordRetention($request, $attribution);
        }

        return $next($request);
    }

    private function recordRetention(Request $request, CampaignAttribution $attribution): void
    {
        $days = $attribution->first_touch_at->diffInDays(now());
        foreach ([1, 7] as $day) {
            if ($days >= $day) {
                $this->analytics->record('day_'.$day.'_return', 'user-'.$request->user()->id, [], $request->user(), null, $attribution, $request);
            }
        }
    }
}
