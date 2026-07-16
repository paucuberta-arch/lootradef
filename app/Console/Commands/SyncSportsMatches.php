<?php

namespace App\Console\Commands;

use App\Services\SportsSimulationService;
use Illuminate\Console\Command;

class SyncSportsMatches extends Command
{
    protected $signature = 'sports:sync';

    protected $description = 'Avanza y liquida de forma idempotente las simulaciones deportivas';

    public function handle(SportsSimulationService $simulation): int
    {
        $this->components->info("Partidos sincronizados: {$simulation->syncAll()}");

        return self::SUCCESS;
    }
}
