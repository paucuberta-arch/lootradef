<?php

namespace App\Services;

use App\Models\CaseDemoPrizeBoost;
use App\Models\CaseRewardDailyStat;
use App\Models\CaseRewardSetting;
use App\Models\Usuario;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CasePrizeService
{
    private const REEL_SIZE = 30;

    private const REEL_WINNER_INDEX = 24;

    public function syncDefinitions(): void
    {
        foreach (config('cajas', []) as $caseKey => $definition) {
            $setting = CaseRewardSetting::firstOrCreate(['case_key' => $caseKey]);
            $totalWeight = max(1, array_sum(array_column($definition['premios'], 'peso')));

            foreach ($definition['premios'] as $prize) {
                $setting->prizeRules()->firstOrCreate(
                    ['prize_key' => $this->prizeKey($prize)],
                    [
                        'name' => $prize['nombre'],
                        'probability' => round(($prize['peso'] / $totalWeight) * 100, 4),
                        'is_good' => in_array($prize['rareza'], ['epico', 'legendario'], true),
                    ]
                );
            }
        }
    }

    public function adminCases(): Collection
    {
        $this->syncDefinitions();
        $settings = CaseRewardSetting::with(['prizeRules', 'dailyStats' => fn ($query) => $query->whereDate('award_date', today())])
            ->get()->keyBy('case_key');

        return collect(config('cajas', []))->map(function (array $definition, string $caseKey) use ($settings) {
            $setting = $settings->get($caseKey);
            $rules = $setting->prizeRules->keyBy('prize_key');

            return [
                'setting' => $setting,
                'definition' => $definition,
                'good_awarded_today' => (int) ($setting->dailyStats->first()?->good_awarded ?? 0),
                'prizes' => collect($definition['premios'])->map(fn (array $prize) => [
                    'definition' => $prize,
                    'rule' => $rules->get($this->prizeKey($prize)),
                ]),
            ];
        });
    }

    /**
     * Return the same prize definitions used by the picker, including the
     * currently active public probability for every prize.
     */
    public function publicDefinitions(): array
    {
        $this->syncDefinitions();
        $settings = CaseRewardSetting::with([
            'prizeRules',
            'dailyStats' => fn ($query) => $query->whereDate('award_date', today()),
        ])->get()->keyBy('case_key');

        return collect(config('cajas', []))->map(function (array $definition, string $caseKey) use ($settings) {
            $setting = $settings->get($caseKey);
            $rules = $setting?->prizeRules?->keyBy('prize_key') ?? collect();
            $totalWeight = max(1, array_sum(array_column($definition['premios'], 'peso')));
            $goodAwardedToday = (int) ($setting?->dailyStats?->first()?->good_awarded ?? 0);
            $capReached = $setting?->good_daily_cap !== null && $goodAwardedToday >= $setting->good_daily_cap;
            $configured = collect($definition['premios'])->map(function (array $prize) use ($rules, $totalWeight) {
                $fallbackProbability = ((float) $prize['peso'] / $totalWeight) * 100;
                $rule = $rules->get($this->prizeKey($prize));

                return [
                    ...$prize,
                    'probabilidad_configurada' => round((float) ($rule?->probability ?? $fallbackProbability), 4),
                    'es_premio_bueno' => (bool) ($rule?->is_good ?? in_array($prize['rareza'], ['epico', 'legendario'], true)),
                ];
            })->values();
            $configuredRegularTotal = $configured->where('es_premio_bueno', false)->sum('probabilidad_configurada');
            $regularTotal = $configuredRegularTotal;
            $usingConfiguredProbabilities = $configuredRegularTotal > 0;
            if ($capReached && ! $usingConfiguredProbabilities) {
                $regularTotal = $configured->where('es_premio_bueno', false)->sum(fn (array $prize) => (float) $prize['peso']);
            }
            $prizes = $configured->map(function (array $prize) use ($capReached, $regularTotal, $usingConfiguredProbabilities) {
                $probability = (float) $prize['probabilidad_configurada'];
                if ($capReached) {
                    $probability = $prize['es_premio_bueno']
                        ? 0
                        : ($regularTotal > 0 ? ((float) ($usingConfiguredProbabilities ? $prize['probabilidad_configurada'] : $prize['peso']) / $regularTotal) * 100 : 0);
                }

                return [...$prize, 'probabilidad' => round($probability, 4)];
            })->map(function (array $prize) {
                unset($prize['probabilidad_configurada'], $prize['es_premio_bueno']);

                return $prize;
            })->values()->all();

            $expectedValue = collect($prizes)->sum(fn (array $prize) => ((float) $prize['probabilidad'] / 100) * (float) $prize['valor']);

            return [
                ...$definition,
                'case_key' => $caseKey,
                'premios' => $prizes,
                'good_daily_cap' => $setting?->good_daily_cap,
                'good_awarded_today' => $goodAwardedToday,
                'probability_mode' => $capReached ? 'daily_cap_reached' : 'configured',
                'cap_reached' => $capReached,
                'rtp' => round(($expectedValue / max(.01, (float) $definition['precio'])) * 100, 2),
            ];
        })->all();
    }

    public function pick(string $caseKey, array $definition, Usuario $user): array
    {
        $setting = CaseRewardSetting::where('case_key', $caseKey)->lockForUpdate()->first();
        if (! $setting) {
            return $this->fallbackPick($definition['premios']);
        }

        $rules = $setting->prizeRules()->lockForUpdate()->get()->keyBy('prize_key');
        $daily = $this->dailyStat($setting);
        $capReached = $setting->good_daily_cap !== null
            && $daily->good_awarded >= $setting->good_daily_cap;
        // Per-user boosts are retained as QA metadata only. They must never
        // alter a production/demo draw because outcomes are global and equal
        // for every player.
        $multiplier = 1.0;

        $candidates = collect($definition['premios'])->map(function (array $prize) use ($rules, $multiplier) {
            $rule = $rules->get($this->prizeKey($prize));
            $isGood = (bool) ($rule?->is_good ?? in_array($prize['rareza'], ['epico', 'legendario'], true));
            $weight = max(0, (int) round((float) ($rule?->probability ?? $prize['peso']) * 100));

            return compact('prize', 'isGood', 'weight') + [
                'adjusted_weight' => $isGood ? (int) round($weight * $multiplier) : $weight,
            ];
        })->when($capReached, fn (Collection $items) => $items->where('isGood', false))->filter(fn (array $item) => $item['adjusted_weight'] > 0)->values();

        if ($candidates->isEmpty()) {
            $candidates = collect($definition['premios'])
                ->filter(fn (array $prize) => ! (bool) ($rules->get($this->prizeKey($prize))?->is_good ?? false))
                ->map(fn (array $prize) => ['prize' => $prize, 'isGood' => false, 'adjusted_weight' => max(1, (int) $prize['peso'])])
                ->values();
        }

        abort_if($candidates->isEmpty(), 409, 'No hay premios disponibles para esta caja en este momento.');
        if (! $this->isDemoEligible($user)) {
            $totalWeight = $candidates->sum('adjusted_weight');
            $expectedValue = $candidates->sum(
                fn (array $candidate) => ($candidate['adjusted_weight'] / $totalWeight) * (float) $candidate['prize']['valor']
            );
            abort_if(
                $expectedValue >= (float) $definition['precio'],
                409,
                'La configuración de premios de esta caja requiere revisión antes de admitir aperturas reales.'
            );
        }
        $selected = $this->weightedPick($candidates);

        if ($selected['isGood']) {
            $daily->increment('good_awarded');
        }

        return $selected['prize'];
    }

    public function boostMultiplierFor(Usuario $user): float
    {
        if (! $this->isDemoEligible($user)) {
            return 1.0;
        }

        return (float) (CaseDemoPrizeBoost::where('user_id', $user->id)
            ->where(fn ($query) => $query->whereNull('expires_at')->orWhere('expires_at', '>', now()))
            ->value('multiplier') ?? 1.0);
    }

    public function isDemoEligible(Usuario $user): bool
    {
        return $user->is_demo || in_array($user->data_origin, ['test', 'simulated'], true);
    }

    public function prizeKey(array $prize): string
    {
        return Str::slug($prize['nombre']);
    }

    /**
     * Build the visual reel around the already selected server prize.
     * The reel is display-only: its winner is never calculated again.
     *
     * @return array{items: array<int, array>, winner_index: int}
     */
    public function buildReel(array $prizes, array $winner): array
    {
        $pool = array_values($prizes);
        abort_if($pool === [], 409, 'Esta caja no tiene premios disponibles.');

        $items = [];
        $previousName = null;
        for ($index = 0; $index < self::REEL_SIZE; $index++) {
            if ($index === self::REEL_WINNER_INDEX) {
                $items[] = $winner;
                $previousName = $winner['nombre'];

                continue;
            }

            $candidates = array_values(array_filter(
                $pool,
                fn (array $prize) => $prize['nombre'] !== $previousName
                    && ($index !== self::REEL_WINNER_INDEX + 1 || $prize['nombre'] !== $winner['nombre'])
            ));
            if ($candidates === []) {
                $candidates = $pool;
            }

            $visualPrize = $candidates[random_int(0, count($candidates) - 1)];
            $items[] = $visualPrize;
            $previousName = $visualPrize['nombre'];
        }

        return ['items' => $items, 'winner_index' => self::REEL_WINNER_INDEX];
    }

    private function dailyStat(CaseRewardSetting $setting): CaseRewardDailyStat
    {
        DB::table('case_reward_daily_stats')->insertOrIgnore([
            'case_reward_setting_id' => $setting->id,
            'award_date' => today()->toDateString(),
            'good_awarded' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return CaseRewardDailyStat::where('case_reward_setting_id', $setting->id)
            ->whereDate('award_date', today())->lockForUpdate()->firstOrFail();
    }

    private function weightedPick(Collection $candidates): array
    {
        $roll = random_int(1, $candidates->sum('adjusted_weight'));
        foreach ($candidates as $candidate) {
            $roll -= $candidate['adjusted_weight'];
            if ($roll <= 0) {
                return $candidate;
            }
        }

        return $candidates->last();
    }

    private function fallbackPick(array $prizes): array
    {
        return $this->weightedPick(collect($prizes)->map(fn (array $prize) => [
            'prize' => $prize,
            'isGood' => in_array($prize['rareza'], ['epico', 'legendario'], true),
            'adjusted_weight' => max(1, (int) $prize['peso']),
        ]))['prize'];
    }
}
