<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('apuestas_deportivas', function (Blueprint $table): void {
            $table->uuid('request_token')->nullable()->after('partido_id');
            $table->unique(['usuario_id', 'request_token']);
        });
    }

    public function down(): void
    {
        Schema::table('apuestas_deportivas', function (Blueprint $table): void {
            $table->dropUnique(['usuario_id', 'request_token']);
            $table->dropColumn('request_token');
        });
    }
};
