<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ledger_accounts', function (Blueprint $table) {
            $table->id();
            $table->string('code', 160)->unique();
            $table->string('type', 40);
            $table->foreignId('usuario_id')->nullable()->constrained('usuarios')->nullOnDelete();
            $table->string('currency', 30)->default('EUR_DEMO');
            $table->string('status', 20)->default('active');
            $table->timestamps();
            $table->index(['type', 'status']);
        });

        Schema::create('ledger_transactions', function (Blueprint $table) {
            $table->id();
            $table->uuid('transaction_uuid')->unique();
            $table->string('type', 80);
            $table->string('status', 20)->default('posted');
            $table->string('currency', 30)->default('EUR_DEMO');
            $table->string('idempotency_key', 191)->nullable()->unique();
            $table->nullableMorphs('reference');
            $table->foreignId('created_by')->nullable()->constrained('usuarios')->nullOnDelete();
            $table->json('metadata')->nullable();
            $table->timestamp('occurred_at');
            $table->timestamps();
            $table->index(['type', 'occurred_at']);
        });

        Schema::create('ledger_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ledger_transaction_id')->constrained()->cascadeOnDelete();
            $table->foreignId('ledger_account_id')->constrained()->restrictOnDelete();
            $table->enum('direction', ['debit', 'credit']);
            $table->decimal('amount', 16, 2);
            $table->string('currency', 30)->default('EUR_DEMO');
            $table->unsignedSmallInteger('line_number');
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->unique(['ledger_transaction_id', 'line_number']);
            $table->index(['ledger_account_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ledger_entries');
        Schema::dropIfExists('ledger_transactions');
        Schema::dropIfExists('ledger_accounts');
    }
};
