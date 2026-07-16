<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('carteras', function (Blueprint $table) {
            $table->unique('usuario_id');
        });

        Schema::create('wallet_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('usuario_id')->constrained('usuarios')->cascadeOnDelete();
            $table->foreignId('cartera_id')->constrained('carteras')->cascadeOnDelete();
            $table->string('tipo', 60)->index();
            $table->string('direccion', 10);
            $table->decimal('importe', 12, 2);
            $table->decimal('saldo_anterior', 12, 2);
            $table->decimal('saldo_posterior', 12, 2);
            $table->nullableMorphs('referencia');
            $table->string('estado', 20)->default('confirmado');
            $table->string('idempotency_key', 100)->nullable()->unique();
            $table->json('metadatos')->nullable();
            $table->timestamps();
            $table->index(['usuario_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('wallet_movements');

        Schema::table('carteras', function (Blueprint $table) {
            $table->dropUnique(['usuario_id']);
        });
    }
};
