<?php

namespace App\Services;

use App\Models\CampaignAttribution;
use Carbon\CarbonImmutable;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;

class CampaignManager
{
    public const KEY = 'rickyedit';

    public function config(?string $key = null): array
    {
        return config('campaigns.'.($key ?? self::KEY), []);
    }

    public function enabled(?string $key = null): bool
    {
        $config = $this->config($key);
        if (! ($config['enabled'] ?? false)) {
            return false;
        }

        $now = CarbonImmutable::now();
        $start = filled($config['start_at'] ?? null) ? CarbonImmutable::parse($config['start_at']) : null;
        $end = filled($config['end_at'] ?? null) ? CarbonImmutable::parse($config['end_at']) : null;

        return (! $start || $now->greaterThanOrEqualTo($start))
            && (! $end || $now->lessThanOrEqualTo($end));
    }

    public function gameAllowed(string $game): bool
    {
        return in_array($game, $this->config()['allowed_games'] ?? [], true);
    }

    public function asset(string $name): string
    {
        $config = $this->config();
        $filename = $config['assets'][$name] ?? $name;
        $base = 'images/campaigns/rickyedit/';
        $official = $base.'official/'.$filename;

        if (($config['official_assets_enabled'] ?? false) && is_file(public_path($official))) {
            return asset($official);
        }

        return asset($base.$filename);
    }

    public function captureAttribution(Request $request): ?CampaignAttribution
    {
        $utmKeys = ['utm_source', 'utm_medium', 'utm_campaign', 'utm_content'];
        $hasUtm = collect($utmKeys)->contains(fn (string $key) => $request->filled($key));
        $isCampaignPath = $request->is('rickyedit') || $request->is('rickyedit/*');
        $hasCreator = $request->filled('creator_code') || $request->filled('creator');

        if (! $hasUtm && ! $isCampaignPath && ! $hasCreator && ! $request->session()->has('rickyedit_attribution')) {
            return null;
        }

        $existing = $request->session()->get('rickyedit_attribution', []);
        $incoming = Arr::only($request->query(), $utmKeys);
        $data = [
            ...$existing,
            ...array_filter($incoming, fn ($value) => is_scalar($value) && filled($value)),
            'referrer' => $existing['referrer'] ?? mb_substr((string) $request->headers->get('referer'), 0, 2000),
            'creator_code' => $existing['creator_code']
                ?? mb_substr((string) ($request->query('creator_code', $request->query('creator', $this->config()['creator_code'] ?? 'rickyedit'))), 0, 100),
            'campaign_key' => self::KEY,
            'first_touch_at' => $existing['first_touch_at'] ?? now()->toIso8601String(),
        ];
        $request->session()->put('rickyedit_attribution', $data);

        $attribution = CampaignAttribution::updateOrCreate(
            ['session_id' => $this->sessionHash($request), 'campaign_key' => self::KEY],
            [
                ...Arr::only($data, [...$utmKeys, 'referrer', 'creator_code']),
                'user_id' => $request->user()?->id,
                'first_touch_at' => $data['first_touch_at'],
            ]
        );
        $request->session()->put('rickyedit_attribution_id', $attribution->id);

        return $attribution;
    }

    public function attribution(Request $request): ?CampaignAttribution
    {
        if (! $request->session()->has('rickyedit_attribution')) {
            return null;
        }

        if ($id = $request->session()->get('rickyedit_attribution_id')) {
            return CampaignAttribution::whereKey($id)->where('campaign_key', self::KEY)->first();
        }

        return CampaignAttribution::where('session_id', $this->sessionHash($request))
            ->where('campaign_key', self::KEY)->first();
    }

    public function isAttributed(Request $request): bool
    {
        return $request->session()->has('rickyedit_attribution');
    }

    public function sessionHash(Request $request): string
    {
        return hash('sha256', $request->session()->getId());
    }
}
