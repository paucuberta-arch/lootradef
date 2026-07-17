<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CampaignAttribution;
use App\Models\CampaignChallenge;
use App\Models\CampaignEvent;
use App\Services\CampaignManager;
use Illuminate\Http\Request;

class AdminCampaignController extends Controller
{
    public function index(Request $request)
    {
        $origin = in_array($request->query('origin', 'real'), ['real', 'test', 'simulated'], true)
            ? $request->query('origin', 'real') : 'real';
        $events = CampaignEvent::where('campaign_key', CampaignManager::KEY)->where('data_origin', $origin);
        $challenges = CampaignChallenge::where('campaign_key', CampaignManager::KEY)->where('data_origin', $origin);
        $attributions = CampaignAttribution::where('campaign_key', CampaignManager::KEY)->where('data_origin', $origin);

        $visits = (clone $events)->where('event', 'landing_view')->count();
        $registrations = (clone $events)->where('event', 'registration_completed')->count();
        $started = (clone $challenges)->count();
        $completed = (clone $challenges)->whereIn('status', ['completed', 'expired'])->count();
        $stats = [
            'visits' => $visits,
            'registrations' => $registrations,
            'started' => $started,
            'completed' => $completed,
            'conversion' => $visits ? round($registrations / $visits * 100, 1) : 0,
            'games' => (int) (clone $challenges)->sum('games_played'),
            'beat_creator' => (clone $challenges)->where('score', '>', config('campaigns.rickyedit.creator_score'))->count(),
            'day_1' => (clone $events)->where('event', 'day_1_return')->count(),
            'day_7' => (clone $events)->where('event', 'day_7_return')->count(),
        ];
        $sources = (clone $attributions)->selectRaw("coalesce(utm_source, 'direct') as source, count(*) as total")
            ->groupBy('utm_source')->orderByDesc('total')->get();
        $daily = (clone $events)->selectRaw('date(occurred_at) as day, count(*) as total')
            ->groupBy('day')->orderBy('day')->get();

        return view('admin.campaigns.rickyedit', compact('stats', 'sources', 'daily', 'origin'));
    }
}
