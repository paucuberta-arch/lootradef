<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('poker_dealer_hands', function (Blueprint $table) {
            $table->id();
            $table->foreignId('usuario_id')->constrained('usuarios')->cascadeOnDelete();
            $table->decimal('ante', 12, 2);
            $table->decimal('apostado', 12, 2);
            $table->json('baraja');
            $table->json('mano_jugador');
            $table->json('mano_dealer');
            $table->json('comunitarias')->nullable();
            $table->string('fase')->default('preflop');
            $table->string('resultado')->nullable();
            $table->decimal('ganancia', 12, 2)->default(0);
            $table->timestamp('finalizada_at')->nullable();
            $table->timestamps();
            $table->index(['usuario_id', 'fase']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('poker_dealer_hands');
    }
};
