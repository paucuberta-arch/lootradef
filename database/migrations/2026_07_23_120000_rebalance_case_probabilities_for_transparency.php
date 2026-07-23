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

        $defaults = [
            'gaming' => [57, 25, 12, 5, .9, .1],
            'tech' => [56, 25, 12.5, 5.5, .9, .1],
        ];
        $targets = [
            'gaming' => [55.7, 25, 12, 6.3, .9, .1],
            'tech' => [54.6, 25, 13.4, 6, .9, .1],
        ];

        foreach ($targets as $caseKey => $probabilities) {
            $settingId = DB::table('case_reward_settings')->where('case_key', $caseKey)->value('id');
            $definition = config("cajas.{$caseKey}");
            if (! $settingId || ! $definition || count($definition['premios']) !== count($probabilities)) {
                continue;
            }

            $rules = DB::table('case_prize_rules')
                ->where('case_reward_setting_id', $settingId)
                ->orderBy('id')
                ->get();
            $legacyMatches = $rules->count() === count($defaults[$caseKey])
                && $rules->values()->every(fn ($rule, int $index) => abs((float) $rule->probability - $defaults[$caseKey][$index]) < .0001);
            if (! $legacyMatches) {
                continue;
            }

            foreach ($definition['premios'] as $index => $prize) {
                DB::table('case_prize_rules')
                    ->where('case_reward_setting_id', $settingId)
                    ->where('prize_key', Str::slug($prize['nombre']))
                    ->update(['probability' => $probabilities[$index], 'updated_at' => now()]);
            }
        }
    }

    public function down(): void
    {
        // The public probability tables are intentionally not reverted to older values.
    }
};
