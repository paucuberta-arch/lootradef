<?php

namespace App\Http\Middleware;

use App\Services\CampaignChallengeService;
use App\Services\CampaignManager;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;
use Symfony\Component\HttpFoundation\Response;

class ShareCampaignData
{
    public function __construct(
        private readonly CampaignManager $campaigns,
        private readonly CampaignChallengeService $challenges,
    ) {}

    public function handle(Request $request, Closure $next): Response
    {
        $challenge = $request->user() ? $this->challenges->findForUser($request->user()) : null;
        $active = $challenge?->status === 'active' ? $challenge : null;
        View::share([
            'rickyeditCampaign' => $this->campaigns->config(),
            'rickyeditCampaignEnabled' => $this->campaigns->enabled(),
            'rickyeditChallenge' => $challenge,
            'rickyeditActiveChallenge' => $active,
            'displayBalance' => $active?->current_balance ?? ($request->user()?->cartera?->saldo ?? 0),
            'displayBalanceKind' => $active ? 'campaign' : 'wallet',
        ]);

        return $next($request);
    }
}
