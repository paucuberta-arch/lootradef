<?php

namespace App\Services;

use InvalidArgumentException;

class EconomicSimulationService
{
    /**
     * Run a deterministic abstract game-economy scenario. It deliberately
     * does not write users, wallets or financial records to the database.
     */
    public function run(array $parameters): array
    {
        $users = max(1, (int) ($parameters['users'] ?? 100));
        $rounds = max(1, (int) ($parameters['rounds'] ?? 100));
        $initialBalance = max(0.0, round((float) ($parameters['initial_balance'] ?? 100.0), 2));
        $bet = max(0.01, round((float) ($parameters['bet'] ?? 1.0), 2));
        $rtp = (float) ($parameters['rtp'] ?? 0.96);
        $seed = (int) ($parameters['seed'] ?? 20260724);
        $payoutMultiplier = max(1.0, (float) ($parameters['payout_multiplier'] ?? 2.0));

        if ($rtp <= 0 || $rtp > 1 || $payoutMultiplier <= 1) {
            throw new InvalidArgumentException('RTP debe estar entre 0 y 1 y el multiplicador debe ser mayor que 1.');
        }

        $balances = array_fill(1, $users, $initialBalance);
        $winners = $losers = 0;
        $wagered = $paid = 0.0;
        $roundsPlayed = 0;
        $randomState = $seed & 0xFFFFFFFF;

        for ($user = 1; $user <= $users; $user++) {
            for ($round = 0; $round < $rounds; $round++) {
                if ($balances[$user] < $bet) {
                    break;
                }

                $wagered += $bet;
                $roundsPlayed++;
                $balances[$user] = round($balances[$user] - $bet, 2);
                $randomState = ($randomState * 1664525 + 1013904223) % 4294967296;
                $isWin = ($randomState / 4294967296) < ($rtp / $payoutMultiplier);
                if ($isWin) {
                    $payout = round($bet * $payoutMultiplier, 2);
                    $balances[$user] = round($balances[$user] + $payout, 2);
                    $paid += $payout;
                }
            }

            if ($balances[$user] > $initialBalance) {
                $winners++;
            } elseif ($balances[$user] < $initialBalance) {
                $losers++;
            }
        }

        $gameRevenue = round($wagered - $paid, 2);
        $complementaryRevenue = round((float) ($parameters['complementary_revenue'] ?? 0), 2);
        $variableCosts = round((float) ($parameters['variable_costs'] ?? 0), 2);
        $fixedCosts = round((float) ($parameters['fixed_costs'] ?? 0), 2);
        $netResult = round($gameRevenue + $complementaryRevenue - $variableCosts - $fixedCosts, 2);
        $observedRtp = $wagered > 0 ? round($paid / $wagered, 6) : 0.0;

        return [
            'parameters' => compact('users', 'rounds', 'initialBalance', 'bet', 'rtp', 'seed', 'payoutMultiplier'),
            'rounds_played' => $roundsPlayed,
            'wagered' => round($wagered, 2),
            'paid' => round($paid, 2),
            'observed_rtp' => $observedRtp,
            'observed_margin' => $wagered > 0 ? round(1 - $observedRtp, 6) : 0.0,
            'ggr' => $gameRevenue,
            'complementary_revenue' => $complementaryRevenue,
            'variable_costs' => $variableCosts,
            'fixed_costs' => $fixedCosts,
            'net_result' => $netResult,
            'winning_users' => $winners,
            'losing_users' => $losers,
            'unchanged_users' => $users - $winners - $losers,
            'minimum_balance' => round(min($balances), 2),
            'maximum_balance' => round(max($balances), 2),
            'reproducibility_hash' => hash('sha256', json_encode([
                $seed, $users, $rounds, $initialBalance, $bet, $rtp, $payoutMultiplier,
                $gameRevenue, $paid, $winners, $losers,
            ], JSON_THROW_ON_ERROR)),
        ];
    }
}
