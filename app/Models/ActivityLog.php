<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ActivityLog extends Model
{
    protected $table = 'activity_logs';

    protected $fillable = [
        'usuario_id', 'accion', 'modelo', 'modelo_id', 'detalles', 'ip',
    ];

    protected $casts = [
        'detalles' => 'array',
    ];

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'usuario_id');
    }

    public static function log(string $accion, ?string $modelo = null, ?int $modeloId = null, ?array $detalles = null): void
    {
        static::create([
            'usuario_id' => auth()->id(),
            'accion' => $accion,
            'modelo' => $modelo,
            'modelo_id' => $modeloId,
            'detalles' => $detalles,
            'ip' => request()->ip(),
        ]);
    }
}
