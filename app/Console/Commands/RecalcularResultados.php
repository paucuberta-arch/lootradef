<?php

namespace App\Console\Commands;

use App\Models\Operacion;
use Illuminate\Console\Command;

class RecalcularResultados extends Command
{
    protected $signature = 'operaciones:recalcular';

    protected $description = 'Recalcula todos los resultados de las operaciones existentes';

    public function handle()
    {

        $operaciones = Operacion::with('numeros')->get();

        foreach ($operaciones as $operacion) {

            $numeros = $operacion->numeros
                ->pluck('numero')
                ->map(fn ($numero) => (float) $numero)
                ->toArray();

            if (count($numeros) < 2) {

                continue;

            }

            switch ($operacion->tipo) {

                case 'suma':

                    $resultado = array_sum($numeros);

                    break;

                case 'resta':

                    $resultado = array_shift($numeros);

                    foreach ($numeros as $numero) {

                        $resultado -= $numero;

                    }

                    break;

                case 'multiplicacion':

                    $resultado = array_shift($numeros);

                    foreach ($numeros as $numero) {

                        $resultado *= $numero;

                    }

                    break;

                case 'division':

                    $resultado = array_shift($numeros);

                    foreach ($numeros as $numero) {

                        if ($numero == 0) {

                            $resultado = 0;

                            break;

                        }

                        $resultado /= $numero;

                    }

                    break;

                default:

                    continue 2;

            }

            $operacion->update([

                'resultado' => round($resultado, 6),

            ]);

            $this->info(

                "Operación {$operacion->id} actualizada"

            );

        }

        $this->info('Todos los resultados han sido recalculados.');

        return Command::SUCCESS;

    }
}
