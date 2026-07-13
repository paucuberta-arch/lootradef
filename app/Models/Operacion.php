<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Operacion extends Model
{
    protected $table = 'operaciones';

    protected $fillable = [

        'tipo',

        'resultado',

    ];

    protected $casts = [

        'resultado' => 'float',

    ];

    public function numeros()
    {

        return $this->hasMany(
            Numero::class
        );

    }

    public function getOperacionAttribute()
    {

        $simbolos = [

            'suma' => '+',

            'resta' => '-',

            'multiplicacion' => '×',

            'division' => '÷',

        ];

        return $this->numeros

            ->pluck('numero')

            ->implode(

                ' '.
                ($simbolos[$this->tipo] ?? '')
                .
                ' '

            );

    }
}
