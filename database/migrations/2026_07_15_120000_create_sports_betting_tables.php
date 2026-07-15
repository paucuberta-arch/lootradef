<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('partidos_deportivos', function (Blueprint $table) {
            $table->id();
            $table->string('deporte')->default('futbol');
            $table->string('liga');
            $table->string('local');
            $table->string('visitante');
            $table->string('local_siglas', 5);
            $table->string('visitante_siglas', 5);
            $table->string('imagen')->nullable();
            $table->string('estado')->default('programado')->index();
            $table->timestamp('inicia_at')->index();
            $table->unsignedInteger('duracion_segundos')->default(360);
            $table->unsignedTinyInteger('minuto')->default(0);
            $table->unsignedTinyInteger('goles_local')->default(0);
            $table->unsignedTinyInteger('goles_visitante')->default(0);
            $table->decimal('cuota_local', 6, 2);
            $table->decimal('cuota_empate', 6, 2);
            $table->decimal('cuota_visitante', 6, 2);
            $table->json('simulacion');
            $table->json('eventos')->nullable();
            $table->timestamps();
        });

        Schema::create('apuestas_deportivas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('usuario_id')->constrained('usuarios')->cascadeOnDelete();
            $table->foreignId('partido_id')->constrained('partidos_deportivos')->cascadeOnDelete();
            $table->string('seleccion');
            $table->decimal('cuota', 6, 2);
            $table->decimal('importe', 12, 2);
            $table->decimal('ganancia', 12, 2)->default(0);
            $table->string('estado')->default('pendiente');
            $table->timestamp('liquidada_at')->nullable();
            $table->timestamps();
            $table->index(['usuario_id', 'estado']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('apuestas_deportivas');
        Schema::dropIfExists('partidos_deportivos');
    }
};
