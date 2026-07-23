<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('usuarios', function (Blueprint $table): void {
            $table->index('created_at', 'usuarios_created_at_index');
        });

        Schema::table('partidas', function (Blueprint $table): void {
            $table->index(['usuario_id', 'juego', 'created_at'], 'partidas_user_game_created_index');
            $table->index('created_at', 'partidas_created_at_index');
        });

        Schema::table('apuestas_deportivas', function (Blueprint $table): void {
            $table->index(['usuario_id', 'created_at'], 'bets_user_created_index');
            $table->index(['partido_id', 'estado'], 'bets_match_status_index');
        });

        Schema::table('blackjack_hands', function (Blueprint $table): void {
            $table->index(['usuario_id', 'variante', 'created_at'], 'blackjack_user_variant_created_index');
        });

        Schema::table('poker_dealer_hands', function (Blueprint $table): void {
            $table->index(['usuario_id', 'created_at'], 'poker_user_created_index');
        });

        Schema::table('activity_logs', function (Blueprint $table): void {
            $table->index('created_at', 'activity_logs_created_at_index');
        });

        Schema::table('reviews', function (Blueprint $table): void {
            $table->index(['juego_slug', 'tipo', 'estado'], 'reviews_game_type_status_index');
        });

        Schema::table('feedback', function (Blueprint $table): void {
            $table->index(['estado', 'created_at'], 'feedback_status_created_index');
        });
    }

    public function down(): void
    {
        Schema::table('feedback', fn (Blueprint $table) => $table->dropIndex('feedback_status_created_index'));
        Schema::table('reviews', fn (Blueprint $table) => $table->dropIndex('reviews_game_type_status_index'));
        Schema::table('activity_logs', fn (Blueprint $table) => $table->dropIndex('activity_logs_created_at_index'));
        Schema::table('poker_dealer_hands', fn (Blueprint $table) => $table->dropIndex('poker_user_created_index'));
        Schema::table('blackjack_hands', fn (Blueprint $table) => $table->dropIndex('blackjack_user_variant_created_index'));
        Schema::table('apuestas_deportivas', function (Blueprint $table): void {
            $table->dropIndex('bets_user_created_index');
            $table->dropIndex('bets_match_status_index');
        });
        Schema::table('partidas', function (Blueprint $table): void {
            $table->dropIndex('partidas_user_game_created_index');
            $table->dropIndex('partidas_created_at_index');
        });
        Schema::table('usuarios', fn (Blueprint $table) => $table->dropIndex('usuarios_created_at_index'));
    }
};
