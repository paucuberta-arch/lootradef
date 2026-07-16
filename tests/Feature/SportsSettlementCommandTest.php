<?php

namespace Tests\Feature;

use App\Models\PartidoDeportivo;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SportsSettlementCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_feed_is_read_only_and_command_advances_matches(): void
    {
        $match = PartidoDeportivo::create([
            'liga' => 'Liga Command', 'local' => 'Local', 'visitante' => 'Visitante',
            'local_siglas' => 'LOC', 'visitante_siglas' => 'VIS', 'inicia_at' => now()->subSeconds(180),
            'duracion_segundos' => 360, 'cuota_local' => 2, 'cuota_empate' => 3, 'cuota_visitante' => 2.5,
            'simulacion' => [['minute' => 20, 'type' => 'goal', 'team' => 'local', 'text' => 'Gol local']],
        ]);

        $this->getJson(route('sports.feed'))->assertOk()->assertJsonPath('matches.0.minute', 0);
        $this->assertSame(0, $match->fresh()->minuto);

        $this->artisan('sports:sync')->assertSuccessful();
        $this->assertGreaterThanOrEqual(44, $match->fresh()->minuto);
    }

    public function test_sync_command_is_scheduled_without_overlapping(): void
    {
        $event = collect(app(Schedule::class)->events())
            ->first(fn ($event) => str_contains($event->command ?? '', 'sports:sync'));

        $this->assertNotNull($event);
        $this->assertSame('* * * * *', $event->expression);
        $this->assertTrue($event->withoutOverlapping);
    }
}
