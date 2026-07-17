<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('case_reward_settings', function (Blueprint $table) {
            $table->id();
            $table->string('case_key', 80)->unique();
            $table->unsignedInteger('good_daily_cap')->nullable();
            $table->timestamps();
        });

        Schema::create('case_prize_rules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('case_reward_setting_id')->constrained()->cascadeOnDelete();
            $table->string('prize_key', 120);
            $table->string('name');
            $table->decimal('probability', 7, 4);
            $table->boolean('is_good')->default(false);
            $table->timestamps();
            $table->unique(['case_reward_setting_id', 'prize_key'], 'case_prize_rule_unique');
        });

        Schema::create('case_reward_daily_stats', function (Blueprint $table) {
            $table->id();
            $table->foreignId('case_reward_setting_id')->constrained()->cascadeOnDelete();
            $table->date('award_date');
            $table->unsignedInteger('good_awarded')->default(0);
            $table->timestamps();
            $table->unique(['case_reward_setting_id', 'award_date'], 'case_reward_daily_unique');
        });

        Schema::create('case_demo_prize_boosts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained('usuarios')->cascadeOnDelete();
            $table->decimal('multiplier', 4, 2);
            $table->string('reason', 500);
            $table->timestamp('expires_at')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('usuarios')->nullOnDelete();
            $table->timestamps();
        });

        foreach (config('cajas', []) as $caseKey => $definition) {
            $settingId = DB::table('case_reward_settings')->insertGetId([
                'case_key' => $caseKey,
                'good_daily_cap' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            $totalWeight = max(1, array_sum(array_column($definition['premios'], 'peso')));

            foreach ($definition['premios'] as $prize) {
                DB::table('case_prize_rules')->insert([
                    'case_reward_setting_id' => $settingId,
                    'prize_key' => Str::slug($prize['nombre']),
                    'name' => $prize['nombre'],
                    'probability' => round(($prize['peso'] / $totalWeight) * 100, 4),
                    'is_good' => in_array($prize['rareza'], ['epico', 'legendario'], true),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('case_demo_prize_boosts');
        Schema::dropIfExists('case_reward_daily_stats');
        Schema::dropIfExists('case_prize_rules');
        Schema::dropIfExists('case_reward_settings');
    }
};
