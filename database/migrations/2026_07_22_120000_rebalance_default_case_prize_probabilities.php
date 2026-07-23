<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('case_reward_settings') || ! Schema::hasTable('case_prize_rules')) {
            return;
        }

        $legacyWeights = [
            'starter' => [30, 26, 22, 14, 7, 1],
            'gaming' => [30, 28, 22, 13, 6, 1],
            'tech' => [29, 28, 23, 13, 6, 1],
            'luxury' => [29, 28, 23, 13, 6, 1],
        ];

        foreach (config('cajas', []) as $caseKey => $definition) {
            $settingId = DB::table('case_reward_settings')->where('case_key', $caseKey)->value('id');
            if (! $settingId || ! isset($legacyWeights[$caseKey])) {
                continue;
            }

            $rules = DB::table('case_prize_rules')
                ->where('case_reward_setting_id', $settingId)
                ->get()->keyBy('prize_key');
            $legacy = $legacyWeights[$caseKey];
            $legacyMatches = collect($definition['premios'])->values()->every(function (array $prize, int $index) use ($rules, $legacy) {
                $rule = $rules->get(Str::slug($prize['nombre']));

                return $rule && abs((float) $rule->probability - $legacy[$index]) < 0.0001;
            });

            if (! $legacyMatches) {
                continue;
            }

            $totalWeight = array_sum(array_column($definition['premios'], 'peso'));
            foreach ($definition['premios'] as $prize) {
                DB::table('case_prize_rules')
                    ->where('case_reward_setting_id', $settingId)
                    ->where('prize_key', Str::slug($prize['nombre']))
                    ->update([
                        'probability' => round(($prize['peso'] / $totalWeight) * 100, 4),
                        'updated_at' => now(),
                    ]);
            }
        }
    }

    public function down(): void
    {
        // Security rebalancing is intentionally not reverted to unsafe probabilities.
    }
};
