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

        // Existing rows are matched by their stable case order. Probabilities,
        // audit history and awarded inventory are preserved; only the public
        // label and identity move from physical-looking prizes to cosmetics.
        foreach (config('cajas', []) as $caseKey => $definition) {
            $settingId = DB::table('case_reward_settings')->where('case_key', $caseKey)->value('id');
            if (! $settingId) {
                continue;
            }

            $rules = DB::table('case_prize_rules')
                ->where('case_reward_setting_id', $settingId)
                ->orderBy('id')
                ->get();
            foreach (collect($definition['premios'])->values() as $index => $prize) {
                $rule = $rules->get($index);
                if (! $rule) {
                    continue;
                }
                DB::table('case_prize_rules')->where('id', $rule->id)->update([
                    'prize_key' => Str::slug($prize['nombre']),
                    'name' => $prize['nombre'],
                    'updated_at' => now(),
                ]);
            }
        }
    }

    public function down(): void
    {
        // The old physical-looking labels are intentionally not restored.
    }
};
