<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('partidas', function (Blueprint $table) {
            $table->uuid('request_token')->nullable()->after('juego');
            $table->unique(['usuario_id', 'juego', 'request_token'], 'partidas_round_token_unique');
        });
    }

    public function down(): void
    {
        Schema::table('partidas', function (Blueprint $table) {
            $table->dropUnique('partidas_round_token_unique');
            $table->dropColumn('request_token');
        });
    }
};
