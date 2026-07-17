<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private array $tables = ['partidas', 'blackjack_hands', 'poker_dealer_hands', 'crash_rounds'];

    public function up(): void
    {
        Schema::table('usuarios', function (Blueprint $table) {
            $table->boolean('is_demo')->default(false)->index();
            $table->string('data_origin', 20)->default('real')->index();
        });

        foreach ($this->tables as $tableName) {
            Schema::table($tableName, function (Blueprint $table) use ($tableName) {
                $table->foreignId('campaign_challenge_id')->nullable()->constrained()->nullOnDelete();
                $table->string('campaign_key', 50)->nullable();
                $table->index(['campaign_challenge_id', 'created_at'], $tableName.'_campaign_history_index');
            });
        }

        foreach (['poker_dealer_hands', 'crash_rounds'] as $tableName) {
            Schema::table($tableName, function (Blueprint $table) use ($tableName) {
                $table->uuid('request_token')->nullable();
                $table->unique(['usuario_id', 'request_token'], $tableName.'_request_unique');
            });
        }
    }

    public function down(): void
    {
        foreach (['poker_dealer_hands', 'crash_rounds'] as $tableName) {
            Schema::table($tableName, function (Blueprint $table) use ($tableName) {
                $table->dropUnique($tableName.'_request_unique');
                $table->dropColumn('request_token');
            });
        }

        foreach (array_reverse($this->tables) as $tableName) {
            Schema::table($tableName, function (Blueprint $table) use ($tableName) {
                $table->dropIndex($tableName.'_campaign_history_index');
                $table->dropConstrainedForeignId('campaign_challenge_id');
                $table->dropColumn('campaign_key');
            });
        }

        Schema::table('usuarios', function (Blueprint $table) {
            $table->dropIndex(['is_demo']);
            $table->dropIndex(['data_origin']);
            $table->dropColumn(['is_demo', 'data_origin']);
        });
    }
};
