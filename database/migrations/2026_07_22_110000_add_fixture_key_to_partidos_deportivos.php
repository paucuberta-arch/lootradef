<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('partidos_deportivos', function (Blueprint $table): void {
            $table->string('fixture_key', 64)->nullable()->unique()->after('id');
        });
    }

    public function down(): void
    {
        Schema::table('partidos_deportivos', function (Blueprint $table): void {
            $table->dropUnique(['fixture_key']);
            $table->dropColumn('fixture_key');
        });
    }
};
