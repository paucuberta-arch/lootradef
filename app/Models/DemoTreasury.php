<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DemoTreasury extends Model
{
    protected $fillable = [
        'code', 'available_balance', 'reserved_balance', 'currency', 'status',
    ];

    protected $casts = [
        'available_balance' => 'float',
        'reserved_balance' => 'float',
    ];
}
