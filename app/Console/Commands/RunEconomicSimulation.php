<?php

namespace App\Console\Commands;

use App\Services\EconomicSimulationService;
use Illuminate\Console\Command;

class RunEconomicSimulation extends Command
{
    protected $signature = 'economy:simulate
        {--users=100 : Usuarios simulados}
        {--rounds=100 : Partidas por usuario}
        {--initial-balance=100 : Saldo demo inicial}
        {--bet=1 : Apuesta por partida}
        {--rtp=0.96 : RTP teórico entre 0 y 1}
        {--seed=20260724 : Semilla reproducible}
        {--payout-multiplier=2 : Multiplicador abstracto del escenario}
        {--complementary-revenue=0 : Ingresos premium/publicidad simulados}
        {--variable-costs=0 : Costes variables simulados}
        {--fixed-costs=0 : Costes fijos simulados}
        {--json : Imprimir JSON en lugar de tabla}';

    protected $description = 'Ejecuta una simulación económica determinista sin escribir en la base de datos';

    public function handle(EconomicSimulationService $simulation): int
    {
        if (! config('features.economy.business_simulation')) {
            $this->error('Activa BUSINESS_SIMULATION_MODE=true para ejecutar simulaciones empresariales.');

            return self::FAILURE;
        }

        $result = $simulation->run([
            'users' => $this->option('users'),
            'rounds' => $this->option('rounds'),
            'initial_balance' => $this->option('initial-balance'),
            'bet' => $this->option('bet'),
            'rtp' => $this->option('rtp'),
            'seed' => $this->option('seed'),
            'payout_multiplier' => $this->option('payout-multiplier'),
            'complementary_revenue' => $this->option('complementary-revenue'),
            'variable_costs' => $this->option('variable-costs'),
            'fixed_costs' => $this->option('fixed-costs'),
        ]);

        if ($this->option('json')) {
            $this->line(json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR));

            return self::SUCCESS;
        }

        $this->table(['Métrica', 'Resultado'], [
            ['Partidas', number_format($result['rounds_played'])],
            ['Apuesta total', $result['wagered'].' EUR Demo'],
            ['Premios', $result['paid'].' EUR Demo'],
            ['RTP observado', $result['observed_rtp']],
            ['GGR', $result['ggr'].' EUR Demo'],
            ['Ingresos complementarios', $result['complementary_revenue'].' EUR Demo'],
            ['Costes', ($result['variable_costs'] + $result['fixed_costs']).' EUR Demo'],
            ['Resultado neto', $result['net_result'].' EUR Demo'],
            ['Usuarios ganadores', $result['winning_users']],
            ['Usuarios perdedores', $result['losing_users']],
            ['Hash reproducible', $result['reproducibility_hash']],
        ]);

        return self::SUCCESS;
    }
}
