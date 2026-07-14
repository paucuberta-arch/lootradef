<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ratings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('usuario_id')->constrained('usuarios')->cascadeOnDelete();
            $table->string('juego_slug', 100);
            $table->unsignedTinyInteger('puntuacion')->between(1, 5);
            $table->timestamps();

            $table->unique(['usuario_id', 'juego_slug']);
            $table->index('juego_slug');
        });

        Schema::create('reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('usuario_id')->constrained('usuarios')->cascadeOnDelete();
            $table->string('juego_slug', 100)->nullable();
            $table->string('titulo', 200)->nullable();
            $table->text('contenido');
            $table->enum('tipo', ['juego', 'web'])->default('juego');
            $table->enum('estado', ['pendiente', 'aprobado', 'rechazado'])->default('pendiente');
            $table->unsignedTinyInteger('puntuacion')->nullable()->between(1, 5);
            $table->integer('likes')->default(0);
            $table->integer('dislikes')->default(0);
            $table->timestamps();

            $table->index('juego_slug');
            $table->index('estado');
            $table->index('tipo');
        });

        Schema::create('review_votes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('usuario_id')->constrained('usuarios')->cascadeOnDelete();
            $table->foreignId('review_id')->constrained()->cascadeOnDelete();
            $table->boolean('upvote');
            $table->timestamps();

            $table->unique(['usuario_id', 'review_id']);
        });

        Schema::create('feedback', function (Blueprint $table) {
            $table->id();
            $table->foreignId('usuario_id')->nullable()->constrained('usuarios')->nullOnDelete();
            $table->enum('tipo', ['sugerencia', 'bug', 'mejora', 'otro'])->default('sugerencia');
            $table->string('asunto', 200);
            $table->text('contenido');
            $table->enum('estado', ['abierto', 'en_progreso', 'resuelto', 'cerrado'])->default('abierto');
            $table->enum('prioridad', ['baja', 'normal', 'alta', 'urgente'])->default('normal');
            $table->string('respuesta')->nullable();
            $table->timestamps();

            $table->index('estado');
            $table->index('tipo');
        });

        Schema::create('activity_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('usuario_id')->nullable()->constrained('usuarios')->nullOnDelete();
            $table->string('accion', 100);
            $table->string('modelo', 50)->nullable();
            $table->unsignedBigInteger('modelo_id')->nullable();
            $table->json('detalles')->nullable();
            $table->string('ip', 45)->nullable();
            $table->timestamps();

            $table->index(['modelo', 'modelo_id']);
            $table->index('accion');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('activity_logs');
        Schema::dropIfExists('feedback');
        Schema::dropIfExists('review_votes');
        Schema::dropIfExists('reviews');
        Schema::dropIfExists('ratings');
    }
};
