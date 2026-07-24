<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('carteras', function (Blueprint $table) {
            $table->foreignId('ledger_account_id')->nullable()->unique()->after('usuario_id')->constrained('ledger_accounts')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('carteras', function (Blueprint $table) {
            $table->dropForeign(['ledger_account_id']);
            $table->dropUnique(['ledger_account_id']);
            $table->dropColumn('ledger_account_id');
        });
    }
};
