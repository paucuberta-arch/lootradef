<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('blackjack_hands', function (Blueprint $table) {
            $table->id();
            $table->foreignId('usuario_id')->constrained('usuarios')->cascadeOnDelete();
            $table->string('variante', 20);
            $table->decimal('apuesta', 12, 2);
            $table->json('baraja');
            $table->json('mano_jugador');
            $table->json('mano_dealer');
            $table->string('estado')->default('jugando');
            $table->decimal('ganancia', 12, 2)->default(0);
            $table->timestamp('finalizada_at')->nullable();
            $table->timestamps();
            $table->index(['usuario_id', 'variante', 'estado']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('blackjack_hands');
    }
};
