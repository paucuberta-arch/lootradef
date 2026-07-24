<?php

namespace Tests\Unit;

use App\Services\EconomicSimulationService;
use Tests\TestCase;

class EconomicSimulationServiceTest extends TestCase
{
    public function test_same_seed_and_parameters_are_reproducible(): void
    {
        $service = app(EconomicSimulationService::class);
        $parameters = ['users' => 25, 'rounds' => 80, 'seed' => 42, 'rtp' => .96];

        $this->assertSame($service->run($parameters), $service->run($parameters));
    }

    public function test_simulation_keeps_game_revenue_separate_from_complementary_revenue_and_costs(): void
    {
        $result = app(EconomicSimulationService::class)->run([
            'users' => 10, 'rounds' => 10, 'seed' => 7, 'rtp' => .96,
            'complementary_revenue' => 50, 'variable_costs' => 10, 'fixed_costs' => 5,
        ]);

        $this->assertSame(
            round($result['ggr'] + $result['complementary_revenue'] - $result['variable_costs'] - $result['fixed_costs'], 2),
            $result['net_result']
        );
        $this->assertGreaterThan(0, $result['winning_users']);
    }
}
