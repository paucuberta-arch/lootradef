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
        $multiplier = $this->boostMultiplierFor($user);

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
        ]));
    }
}
