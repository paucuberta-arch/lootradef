<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('blackjack_hands', function (Blueprint $table) {
            $table->uuid('request_token')->nullable()->after('variante');
            $table->unique(['usuario_id', 'variante', 'request_token'], 'blackjack_hands_request_unique');
        });
    }

    public function down(): void
    {
        Schema::table('blackjack_hands', function (Blueprint $table) {
            $table->dropUnique('blackjack_hands_request_unique');
            $table->dropColumn('request_token');
        });
    }
};
