<?php

namespace App\Services;

use App\Models\CampaignAttribution;
use Carbon\CarbonImmutable;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\RateLimiter;

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
        if (! $this->enabled()) {
            return null;
        }

        $utmKeys = ['utm_source', 'utm_medium', 'utm_campaign', 'utm_content'];
        $hasUtm = collect($utmKeys)->contains(fn (string $key) => $request->filled($key));
        $isCampaignPath = $request->is('rickyedit') || $request->is('rickyedit/*');
        $hasCreator = $request->filled('creator_code') || $request->filled('creator');

        if (! $hasUtm && ! $isCampaignPath && ! $hasCreator && ! $request->session()->has('rickyedit_attribution')) {
            return null;
        }

        if (! $hasUtm && ! $hasCreator && $id = $request->session()->get('rickyedit_attribution_id')) {
            return CampaignAttribution::whereKey($id)->where('campaign_key', self::KEY)->first();
        }

        $attributionLimitKey = 'campaign-attribution:'.hash('sha256', (string) $request->ip());
        abort_if(
            RateLimiter::tooManyAttempts($attributionLimitKey, 60),
            429,
            'Demasiadas visitas nuevas a la campaña. Inténtalo de nuevo en un minuto.'
        );
        RateLimiter::hit($attributionLimitKey, 60);

        $existing = $request->session()->get('rickyedit_attribution', []);
        $incoming = collect(Arr::only($request->query(), $utmKeys))
            ->map(fn ($value) => $this->cleanScalar($value, 255))
            ->filter(fn (?string $value) => filled($value))
            ->all();
        $data = [
            ...$existing,
            ...$incoming,
            'referrer' => $existing['referrer'] ?? $this->cleanScalar($request->headers->get('referer'), 2000),
            'creator_code' => $existing['creator_code']
                ?? $this->cleanScalar($request->query('creator_code', $request->query('creator', $this->config()['creator_code'] ?? 'rickyedit')), 100),
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

    private function cleanScalar(mixed $value, int $length): ?string
    {
        if (! is_scalar($value)) {
            return null;
        }

        return mb_substr(preg_replace('/[\x00-\x1F\x7F]/', '', (string) $value) ?? '', 0, $length);
    }
}
