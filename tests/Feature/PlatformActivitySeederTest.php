<?php

namespace Tests\Feature;

use Database\Seeders\PlatformActivitySeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class PlatformActivitySeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_seeder_populates_three_categories_and_is_repeatable(): void
    {
        $this->seed(PlatformActivitySeeder::class);

        $this->assertSame(900, DB::table('partidas')->where('detalles', 'like', '%platform_activity%')->count());
        $this->assertSame(420, DB::table('apuestas_deportivas')->whereIn('partido_id', DB::table('partidos_deportivos')->where('liga', 'like', '[SEED]%')->select('id'))->count());
        $this->assertSame(320, DB::table('inventario_items')->where('caja', 'like', 'seed_%')->count());
        $this->assertGreaterThan(0, DB::table('apuestas_deportivas')->where('estado', 'ganada')->count());
        $this->assertGreaterThan(0, DB::table('inventario_items')->where('estado', 'canjeado')->count());

        $this->seed(PlatformActivitySeeder::class);

        $this->assertSame(900, DB::table('partidas')->where('detalles', 'like', '%platform_activity%')->count());
        $this->assertSame(420, DB::table('apuestas_deportivas')->count());
        $this->assertSame(320, DB::table('inventario_items')->where('caja', 'like', 'seed_%')->count());
    }
}
