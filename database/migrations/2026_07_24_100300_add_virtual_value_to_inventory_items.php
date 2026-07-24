<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('inventario_items', function (Blueprint $table): void {
            $table->unsignedInteger('valor_virtual')->default(0)->after('valor_canje');
        });

        // Convert legacy display values to non-monetary virtual points. The
        // legacy column is retained solely for backwards-compatible history.
        DB::statement('UPDATE inventario_items SET valor_virtual = ROUND(COALESCE(valor_canje, 0) * 100) WHERE valor_virtual = 0');
    }

    public function down(): void
    {
        Schema::table('inventario_items', function (Blueprint $table): void {
            $table->dropColumn('valor_virtual');
        });
    }
};
