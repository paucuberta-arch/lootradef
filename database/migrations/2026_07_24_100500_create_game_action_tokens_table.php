<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('game_action_tokens', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('usuario_id')->constrained('usuarios')->cascadeOnDelete();
            $table->string('game_key', 60);
            $table->unsignedBigInteger('hand_id');
            $table->uuid('request_token')->unique();
            $table->string('action', 40);
            $table->timestamps();
            $table->index(['usuario_id', 'game_key', 'hand_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('game_action_tokens');
    }
};
