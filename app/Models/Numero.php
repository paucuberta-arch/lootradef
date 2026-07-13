<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Numero extends Model
{
    protected $fillable = [

        'numero',

    ];

    public function operacion()
    {

        return $this->belongsTo(

            Operacion::class

        );

    }
}
