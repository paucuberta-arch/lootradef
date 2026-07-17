<?php

namespace Tests\Feature;

use App\Models\CaseDemoPrizeBoost;
use App\Models\CaseRewardDailyStat;
use App\Models\CaseRewardSetting;
use App\Models\Usuario;
use App\Services\CasePrizeService;
use Database\Seeders\RolesPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class CasePrizeAdministrationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesPermissionsSeeder::class);
    }

    public function test_case_prize_panel_requires_its_dedicated_permission(): void
    {
        $player = $this->user();
        $player->assignRole('user');
        $this->actingAs($player)->get(route('admin.case-prizes.index'))->assertForbidden();

        $admin = $this->user();
        $admin->assignRole('admin');
        $this->actingAs($admin)->get(route('admin.case-prizes.index'))
            ->assertOk()->assertSee('Premios y cupos diarios')->assertSee('Integridad de sorteos reales');
    }

    public function test_admin_can_update_probabilities_and_they_must_sum_exactly_one_hundred(): void
    {
        $admin = $this->user();
        $admin->assignRole('admin');
        $payload = $this->configurationPayload();
        $starter = CaseRewardSetting::where('case_key', 'starter')->with('prizeRules')->firstOrFail();
        $first = $starter->prizeRules->first();
        $second = $starter->prizeRules->skip(1)->first();
        $payload['rules'][$first->id]['probability'] = 99;
        $payload['rules'][$second->id]['probability'] = 0;

        $this->actingAs($admin)->put(route('admin.case-prizes.update'), $payload)
            ->assertSessionHasErrors('rules');

        $payload = $this->configurationPayload();
        foreach ($starter->prizeRules as $rule) {
            $payload['rules'][$rule->id]['probability'] = $rule->id === $first->id ? 100 : 0;
            $payload['rules'][$rule->id]['is_good'] = 0;
        }
        $this->actingAs($admin)->put(route('admin.case-prizes.update'), $payload)
            ->assertRedirect()->assertSessionHasNoErrors();

        $this->assertSame(100.0, (float) $first->fresh()->probability);
        $this->assertSame(0.0, (float) $second->fresh()->probability);
        $this->assertDatabaseHas('activity_logs', ['accion' => 'probabilidades_cajas_actualizadas']);
    }

    public function test_daily_cap_prevents_a_second_good_prize(): void
    {
        $setting = CaseRewardSetting::where('case_key', 'starter')->with('prizeRules')->firstOrFail();
        $setting->update(['good_daily_cap' => 1]);
        $setting->prizeRules()->update(['probability' => 0, 'is_good' => false]);
        $setting->prizeRules()->where('name', 'Smartwatch Fit')->update(['probability' => 100, 'is_good' => true]);
        $player = $this->user(100);

        $first = $this->actingAs($player)->postJson(route('cajas.open', 'starter'))->assertOk();
        $this->assertSame(1, CaseRewardDailyStat::where('case_reward_setting_id', $setting->id)->value('good_awarded'));
        $second = $this->actingAs($player)->postJson(route('cajas.open', 'starter'))->assertOk();

        $this->assertSame('legendario', $first->json('item.rareza'));
        $this->assertNotSame('legendario', $second->json('item.rareza'));
        $this->assertSame(1, CaseRewardDailyStat::where('case_reward_setting_id', $setting->id)->value('good_awarded'));
    }

    public function test_individual_multipliers_are_restricted_to_demo_accounts(): void
    {
        $admin = $this->user();
        $admin->assignRole('admin');
        $real = $this->user();

        $this->actingAs($admin)->post(route('admin.case-prizes.boosts.store'), [
            'user_id' => $real->id, 'multiplier' => 3, 'reason' => 'No debe permitirse',
        ])->assertUnprocessable();
        $this->assertDatabaseCount('case_demo_prize_boosts', 0);

        $demo = $this->user();
        $demo->update(['is_demo' => true, 'data_origin' => 'test']);
        $this->actingAs($admin)->post(route('admin.case-prizes.boosts.store'), [
            'user_id' => $demo->id, 'multiplier' => 3, 'reason' => 'Prueba QA controlada',
        ])->assertRedirect();

        $service = app(CasePrizeService::class);
        $this->assertSame(3.0, $service->boostMultiplierFor($demo->fresh()));
        $this->assertSame(1.0, $service->boostMultiplierFor($real));
        $this->assertDatabaseHas('activity_logs', ['accion' => 'multiplicador_demo_cajas_guardado']);

        CaseDemoPrizeBoost::create([
            'user_id' => $real->id, 'multiplier' => 5, 'reason' => 'Inserción directa de prueba', 'created_by' => $admin->id,
        ]);
        $this->assertSame(1.0, $service->boostMultiplierFor($real));
    }

    public function test_mobile_navigation_has_a_viewport_layer_above_the_campaign_hero(): void
    {
        $this->get(route('inicio'))->assertOk()
            ->assertSee('z-[300]', false)
            ->assertSee('z-[310]', false)
            ->assertSee('z-[320]', false);
    }

    private function configurationPayload(): array
    {
        $settings = CaseRewardSetting::with('prizeRules')->get();
        $payload = ['settings' => [], 'rules' => []];
        foreach ($settings as $setting) {
            $payload['settings'][$setting->id] = ['good_daily_cap' => $setting->good_daily_cap];
            foreach ($setting->prizeRules as $rule) {
                $payload['rules'][$rule->id] = [
                    'probability' => $rule->probability,
                    'is_good' => $rule->is_good ? 1 : 0,
                ];
            }
        }

        return $payload;
    }

    private function user(float $balance = 1000): Usuario
    {
        $user = Usuario::create([
            'name' => 'Case '.Str::random(6),
            'email' => Str::uuid().'@example.test',
            'password' => bcrypt('password'),
        ]);
        $user->cartera()->create(['saldo' => $balance]);

        return $user;
    }
}
