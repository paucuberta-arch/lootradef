<?php

namespace App\Http\Controllers;

use App\Services\CampaignAnalytics;
use App\Services\CampaignChallengeService;
use App\Services\CampaignLeaderboardService;
use App\Services\CampaignManager;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RickyEditCampaignController extends Controller
{
    public function __construct(
        private readonly CampaignManager $campaigns,
        private readonly CampaignChallengeService $challenges,
        private readonly CampaignLeaderboardService $leaderboard,
        private readonly CampaignAnalytics $analytics,
    ) {}

    public function landing(Request $request): View
    {
        $attribution = $this->campaigns->attribution($request);
        $this->analytics->record('landing_view', $this->campaigns->sessionHash($request).':'.today()->toDateString(), [], $request->user(), null, $attribution, $request);

        return view('campaigns.rickyedit.landing', [
            'campaign' => $this->campaigns->config(),
            'campaignEnabled' => $this->campaigns->enabled(),
            'leaders' => $this->leaderboard->query()->take(10)->get(),
            'challenge' => $request->user() ? $this->challenges->findForUser($request->user()) : null,
        ]);
    }

    public function intro(Request $request): View
    {
        abort_unless($this->campaigns->enabled(), 404);

        return view('campaigns.rickyedit.intro', [
            'campaign' => $this->campaigns->config(),
            'challenge' => $this->challenges->findForUser($request->user()),
        ]);
    }

    public function start(Request $request): RedirectResponse
    {
        $challenge = $this->challenges->start($request->user());
        $startedNow = $challenge->wasRecentlyCreated && $challenge->status === 'active';

        $response = redirect()->route('games.index')->with(
            $challenge->status === 'active' ? 'success' : 'info',
            $challenge->status === 'active'
                ? 'El reto ha comenzado. Tienes '.(int) $this->campaigns->config()['duration_minutes'].' minutos.'
                : 'Ya utilizaste tu participación.'
        );

        return $startedNow ? $response->with('ga_reto_iniciado', true) : $response;
    }

    public function finish(Request $request): RedirectResponse
    {
        $this->challenges->complete($request->user());

        return redirect()->route('rickyedit.ranking')->with('success', 'Resultado congelado correctamente.');
    }

    public function status(Request $request): JsonResponse
    {
        $challenge = $this->challenges->findForUser($request->user());
        abort_unless($challenge, 404);

        return response()->json([
            'status' => $challenge->status,
            'balance' => (float) $challenge->current_balance,
            'score' => (float) ($challenge->score ?? floor($challenge->current_balance)),
            'games_played' => $challenge->games_played,
            'seconds_remaining' => $challenge->seconds_remaining,
            'expires_at' => $challenge->expires_at?->toIso8601String(),
        ]);
    }

    public function ranking(Request $request): View
    {
        $challenge = $request->user() ? $this->challenges->findForUser($request->user()) : null;
        $this->analytics->record('ranking_viewed', $this->campaigns->sessionHash($request).':'.today()->toDateString(), [], $request->user(), $challenge, $this->campaigns->attribution($request), $request);

        return view('campaigns.rickyedit.ranking', [
            'leaders' => $this->leaderboard->query()->paginate(50),
            'challenge' => $challenge,
            'userPosition' => $this->leaderboard->position($challenge),
            'creatorScore' => $this->campaigns->config()['creator_score'],
        ]);
    }

    public function event(Request $request): JsonResponse
    {
        $validated = $request->validate(['event' => ['required', 'in:banner_click,result_shared']]);
        $challenge = $request->user() ? $this->challenges->findForUser($request->user()) : null;
        $dedupe = $this->campaigns->sessionHash($request).':'.today()->toDateString();
        $this->analytics->record($validated['event'], $dedupe, [], $request->user(), $challenge, $this->campaigns->attribution($request), $request);

        return response()->json(['ok' => true]);
    }
}
