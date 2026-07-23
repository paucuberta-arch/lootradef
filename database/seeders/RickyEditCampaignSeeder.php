<?php

namespace Database\Seeders;

use App\Models\CampaignAttribution;
use App\Models\CampaignChallenge;
use App\Models\CampaignEvent;
use App\Models\Usuario;
use App\Services\CampaignManager;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use RuntimeException;

class RickyEditCampaignSeeder extends Seeder
{
    public function run(): void
    {
        if (! app()->environment(['local', 'testing'])) {
            throw new RuntimeException('RickyEditCampaignSeeder solo puede ejecutarse en local o testing.');
        }

        mt_srand(17072026);
        DB::transaction(function () {
            CampaignEvent::where('data_origin', 'simulated')->where('campaign_key', CampaignManager::KEY)->delete();
            CampaignChallenge::where('data_origin', 'simulated')->where('campaign_key', CampaignManager::KEY)->delete();
            CampaignAttribution::where('data_origin', 'simulated')->where('campaign_key', CampaignManager::KEY)->delete();
            Usuario::withTrashed()->where('data_origin', 'simulated')->where('email', 'like', 'ricky.sim%@demo.lootra.test')->forceDelete();

            $users = collect();
            for ($i = 1; $i <= 180; $i++) {
                $user = Usuario::create([
                    'name' => 'Jugador Simulado '.$i,
                    'email' => sprintf('ricky.sim%03d@demo.lootra.test', $i),
                    'password' => Hash::make(Str::random(64)),
                    'is_demo' => true, 'data_origin' => 'simulated',
                ]);
                $user->cartera()->create(['saldo' => 0]);
                $user->assignRole('user');
                $users->push($user);
            }

            $cursor = 0;
            for ($day = 0; $day < 30; $day++) {
                $visits = $this->trafficForDay($day);
                $date = now()->subDays(29 - $day)->startOfDay();
                for ($visit = 0; $visit < $visits; $visit++) {
                    $at = $date->copy()->addMinutes(mt_rand(0, 1439));
                    $session = hash('sha256', "simulated-{$day}-{$visit}");
                    $source = ['youtube', 'instagram', 'direct', 'twitch'][array_rand(['youtube', 'instagram', 'direct', 'twitch'])];
                    $attribution = CampaignAttribution::create([
                        'session_id' => $session, 'campaign_key' => CampaignManager::KEY,
                        'utm_source' => $source, 'utm_medium' => $source === 'direct' ? null : 'creator',
                        'utm_campaign' => 'rickyedit_1000', 'creator_code' => 'rickyedit',
                        'first_touch_at' => $at, 'is_demo' => true, 'data_origin' => 'simulated',
                    ]);
                    $this->event('landing_view', "visit-{$day}-{$visit}", $at, null, null, $attribution);

                    if (mt_rand(1, 100) <= 24 && $cursor < $users->count()) {
                        $user = $users[$cursor++];
                        $attribution->update(['user_id' => $user->id, 'converted_at' => $at->copy()->addMinutes(2)]);
                        $this->event('registration_completed', 'user-'.$user->id, $at->copy()->addMinutes(2), $user, null, $attribution);
                        if (mt_rand(1, 100) <= 82) {
                            $balance = max(0, round(1000 + mt_rand(-85000, 120000) / 100, 2));
                            if (mt_rand(1, 100) <= 8) {
                                $balance = mt_rand(130000, 240000) / 100;
                            }
                            $challenge = CampaignChallenge::create([
                                'user_id' => $user->id, 'campaign_key' => CampaignManager::KEY,
                                'public_alias' => 'Simulado#'.str_pad((string) $user->id, 3, '0', STR_PAD_LEFT),
                                'status' => 'expired', 'initial_balance' => 1000, 'current_balance' => $balance,
                                'final_balance' => $balance, 'score' => floor($balance),
                                'started_at' => $at->copy()->addMinutes(3), 'expires_at' => $at->copy()->addMinutes(18),
                                'completed_at' => $at->copy()->addMinutes(18), 'games_played' => mt_rand(4, 32),
                                'is_demo' => true, 'data_origin' => 'simulated',
                            ]);
                            $this->event('challenge_started', 'challenge-'.$challenge->id, $challenge->started_at, $user, $challenge, $attribution);
                            $this->event('challenge_completed', 'challenge-'.$challenge->id, $challenge->completed_at, $user, $challenge, $attribution);
                            if ($day <= 28 && mt_rand(1, 100) <= 34) {
                                $this->event('day_1_return', 'user-'.$user->id, $at->copy()->addDay(), $user, $challenge, $attribution);
                            }
                            if ($day <= 22 && mt_rand(1, 100) <= 13) {
                                $this->event('day_7_return', 'user-'.$user->id, $at->copy()->addDays(7), $user, $challenge, $attribution);
                            }
                        }
                    }
                }
            }
        });

        $this->command?->info('Curva simulada RickyEdit creada. Todos los registros de campaña están marcados como is_demo=true.');
    }

    private function trafficForDay(int $day): int
    {
        if ($day < 5) {
            return mt_rand(2, 7);
        }
        if ($day === 5) {
            return 190;
        }
        if ($day < 14) {
            return max(18, (int) (155 * exp(-0.24 * ($day - 6))) + mt_rand(-5, 8));
        }
        if ($day === 14) {
            return 105;
        }
        if ($day < 21) {
            return max(12, (int) (78 * exp(-0.28 * ($day - 15))) + mt_rand(-4, 6));
        }

        return mt_rand(4, 13);
    }

    private function event(string $event, string $dedupe, $at, ?Usuario $user, ?CampaignChallenge $challenge, CampaignAttribution $attribution): void
    {
        CampaignEvent::create([
            'user_id' => $user?->id, 'campaign_challenge_id' => $challenge?->id,
            'campaign_attribution_id' => $attribution->id, 'campaign_key' => CampaignManager::KEY,
            'event' => $event, 'session_id' => $attribution->session_id,
            'dedupe_key' => CampaignManager::KEY.':'.$event.':simulated-'.$dedupe,
            'source' => 'seeder', 'is_demo' => true, 'data_origin' => 'simulated',
            'occurred_at' => $at, 'created_at' => $at, 'updated_at' => $at,
        ]);
    }
}
