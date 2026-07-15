<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inventario_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('usuario_id')->constrained('usuarios')->cascadeOnDelete();
            $table->string('caja', 50);
            $table->string('nombre', 120);
            $table->string('imagen');
            $table->enum('rareza', ['comun', 'poco_comun', 'raro', 'epico', 'legendario']);
            $table->decimal('precio_caja', 12, 2);
            $table->decimal('valor_canje', 12, 2);
            $table->enum('estado', ['disponible', 'canjeado'])->default('disponible');
            $table->timestamp('canjeado_at')->nullable();
            $table->timestamps();

            $table->index(['usuario_id', 'estado']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventario_items');
    }
};
