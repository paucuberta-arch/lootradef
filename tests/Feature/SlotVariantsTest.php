<?php

namespace Tests\Feature;

use App\Http\Controllers\SlotsController;
use App\Models\Partida;
use App\Models\Usuario;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class SlotVariantsTest extends TestCase
{
    use RefreshDatabase;

    public function test_every_slot_theme_is_normalized_below_one_hundred_percent_expected_return(): void
    {
        $controller = app(SlotsController::class);
        $reflection = new \ReflectionClass($controller);
        $themes = $reflection->getProperty('themes')->getValue($controller);
        $calculate = $reflection->getMethod('calculateWin');

        foreach ($themes as $slug => $theme) {
            $totalWeight = array_sum($theme['weights']);
            $rawReturn = 0.0;

            foreach ($theme['weights'] as $first => $firstWeight) {
                foreach ($theme['weights'] as $second => $secondWeight) {
                    foreach ($theme['weights'] as $third => $thirdWeight) {
                        $reels = [$first, $second, $third];
                        $probability = ($firstWeight * $secondWeight * $thirdWeight) / ($totalWeight ** 3);
                        $lightningCount = count(array_filter($reels, fn ($symbol) => $symbol === '⚡'));
                        $rawPayout = $slug === 'gates-of-olympus' && $lightningCount > 0
                            ? $lightningCount * 3.5
                            : $calculate->invoke($controller, $reels, 1.0, $slug);
                        $rawReturn += $probability * $rawPayout;
                    }
                }
            }

            $this->assertEqualsWithDelta($theme['raw_return'], $rawReturn, 0.000001, "Retorno bruto desactualizado para {$slug}");
            $normalizedReturn = $rawReturn * ($theme['target_return'] / $theme['raw_return']);
            $this->assertLessThan(1, $normalizedReturn, "RTP inseguro para {$slug}");
        }
    }

    public function test_each_slot_uses_its_atlas_and_server_round_is_idempotent(): void
    {
        $user = Usuario::create(['name' => 'Slot Tester', 'email' => 'slots@example.com', 'password' => bcrypt('password')]);
        $user->cartera()->create(['saldo' => 100]);
        $variants = ['gates-of-olympus', 'sweet-bonanza', 'book-of-dead', 'starburst', 'big-bass-bonanza'];
        foreach ($variants as $variant) {
            $this->actingAs($user)->get(route('games.slots.show', $variant))
                ->assertOk()
                ->assertSee('symbols-v2.webp')
                ->assertSee('webCrypto.getRandomValues(bytes)', false)
                ->assertSee('request_token: this.requestToken()', false)
                ->assertSee('Number(h.apuesta).toFixed(2)', false);
        }

        $token = Str::uuid()->toString();
        $payload = ['apuesta' => 5, 'request_token' => $token];
        $first = $this->actingAs($user)->postJson(route('games.slots.play', 'sweet-bonanza'), $payload)->assertOk();
        $second = $this->actingAs($user)->postJson(route('games.slots.play', 'sweet-bonanza'), $payload)->assertOk();

        $this->assertSame($first->json('reels'), $second->json('reels'));
        $this->assertSame(1, Partida::where('request_token', $token)->count());
    }
}
