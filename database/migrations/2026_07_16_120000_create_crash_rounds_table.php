<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('crash_rounds', function (Blueprint $table) {
            $table->id();
            $table->foreignId('usuario_id')->constrained('usuarios')->cascadeOnDelete();
            $table->decimal('apuesta', 12, 2);
            $table->decimal('crash_point', 12, 2);
            $table->string('estado')->default('activa');
            $table->decimal('cashout_at', 12, 2)->nullable();
            $table->decimal('ganancia', 12, 2)->default(0);
            $table->timestamp('iniciada_at', 3);
            $table->timestamp('finalizada_at', 3)->nullable();
            $table->timestamps();
            $table->index(['usuario_id', 'estado']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('crash_rounds');
    }
};
