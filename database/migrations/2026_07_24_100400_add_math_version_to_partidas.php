<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('partidas', function (Blueprint $table): void {
            $table->string('math_version', 80)->nullable()->after('juego')->index();
        });
    }

    public function down(): void
    {
        Schema::table('partidas', function (Blueprint $table): void {
            $table->dropColumn('math_version');
        });
    }
};
