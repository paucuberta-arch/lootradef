<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('demo_treasuries', function (Blueprint $table) {
            $table->id();
            $table->string('code', 60)->unique();
            $table->decimal('available_balance', 16, 2)->default(0);
            $table->decimal('reserved_balance', 16, 2)->default(0);
            $table->string('currency', 30)->default('EUR_DEMO');
            $table->string('status', 20)->default('active');
            $table->timestamps();
        });

        Schema::create('demo_withdrawals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('usuario_id')->constrained('usuarios')->cascadeOnDelete();
            $table->uuid('request_token')->unique();
            $table->decimal('amount', 16, 2);
            $table->string('currency', 30)->default('EUR_DEMO');
            $table->string('method_key', 40);
            $table->string('status', 24)->default('requested');
            $table->string('rejection_reason', 500)->nullable();
            $table->foreignId('reviewed_by')->nullable()->constrained('usuarios')->nullOnDelete();
            $table->timestamp('reserved_at')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->index(['usuario_id', 'status', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('demo_withdrawals');
        Schema::dropIfExists('demo_treasuries');
    }
};
