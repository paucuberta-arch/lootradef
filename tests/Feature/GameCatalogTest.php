<?php

namespace Tests\Feature;

use App\Models\Usuario;
use App\Services\GameCatalog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GameCatalogTest extends TestCase
{
    use RefreshDatabase;

    public function test_catalog_has_unique_slugs_and_complete_playable_definitions(): void
    {
        $catalog = app(GameCatalog::class);
        $games = $catalog->active();

        $this->assertCount(21, $games);
        $this->assertSame($games->count(), $games->keys()->unique()->count());

        foreach ($games as $slug => $game) {
            $this->assertSame($slug, $game['slug']);
            $this->assertNotEmpty($game['name']);
            $this->assertNotEmpty($game['category']);
            $this->assertNotEmpty($game['image']);
            $this->assertNotEmpty($game['description']);
            $this->assertTrue(app('router')->has($game['route_name']));
        }
    }

    public function test_home_and_detail_use_the_shared_catalog(): void
    {
        $user = $this->createPlayer('catalog@example.com');

        $this->get(route('games.index'))
            ->assertOk()
            ->assertSee('Gates of Olympus')
            ->assertSee('Crazy Time Neon')
            ->assertSee(':href="j.detail_url"', false)
            ->assertDontSee(":href=''", false)
            ->assertViewHas('juegos', fn ($games) => $games->every(
                fn (array $game) => $game['detail_url'] === route('games.show', $game['slug'])
            ));

        $this->actingAs($user)->get(route('games.show', 'texas-holdem'))
            ->assertOk()
            ->assertSee('Poker All-In')
            ->assertSee(route('games.poker.all-in'), false);
    }

    private function createPlayer(string $email): Usuario
    {
        $user = Usuario::create(['name' => 'Catalog Tester', 'email' => $email, 'password' => bcrypt('password')]);
        $user->cartera()->create(['saldo' => 100]);

        return $user;
    }
}
