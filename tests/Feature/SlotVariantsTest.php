<?php

namespace Tests\Feature;

use App\Models\Partida;
use App\Models\Usuario;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class SlotVariantsTest extends TestCase
{
    use RefreshDatabase;

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
                ->assertSee('request_token: this.requestToken()', false);
        }

        $token = Str::uuid()->toString();
        $payload = ['apuesta' => 5, 'request_token' => $token];
        $first = $this->actingAs($user)->postJson(route('games.slots.play', 'sweet-bonanza'), $payload)->assertOk();
        $second = $this->actingAs($user)->postJson(route('games.slots.play', 'sweet-bonanza'), $payload)->assertOk();

        $this->assertSame($first->json('reels'), $second->json('reels'));
        $this->assertSame(1, Partida::where('request_token', $token)->count());
    }
}
