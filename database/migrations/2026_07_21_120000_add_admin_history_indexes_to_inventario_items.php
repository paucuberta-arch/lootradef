<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('inventario_items', function (Blueprint $table) {
            $table->index(['caja', 'created_at'], 'inventory_case_created_index');
            $table->index(['estado', 'rareza'], 'inventory_status_rarity_index');
        });
    }

    public function down(): void
    {
        Schema::table('inventario_items', function (Blueprint $table) {
            $table->dropIndex('inventory_case_created_index');
            $table->dropIndex('inventory_status_rarity_index');
        });
    }
};
