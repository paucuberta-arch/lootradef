<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('campaign_attributions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('usuarios')->nullOnDelete();
            $table->string('session_id', 64);
            $table->string('campaign_key', 50);
            $table->string('utm_source')->nullable();
            $table->string('utm_medium')->nullable();
            $table->string('utm_campaign')->nullable();
            $table->string('utm_content')->nullable();
            $table->text('referrer')->nullable();
            $table->string('creator_code', 100)->nullable();
            $table->timestamp('first_touch_at');
            $table->timestamp('converted_at')->nullable();
            $table->boolean('is_demo')->default(false);
            $table->string('data_origin', 20)->default('real');
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->unique(['session_id', 'campaign_key']);
            $table->index(['campaign_key', 'created_at']);
        });

        Schema::create('campaign_challenges', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('usuarios')->cascadeOnDelete();
            $table->string('campaign_key', 50);
            $table->string('public_alias', 80);
            $table->string('status', 20)->default('pending');
            $table->decimal('initial_balance', 12, 2);
            $table->decimal('current_balance', 12, 2);
            $table->decimal('final_balance', 12, 2)->nullable();
            $table->decimal('score', 12, 2)->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->unsignedInteger('games_played')->default(0);
            $table->boolean('is_demo')->default(false);
            $table->string('data_origin', 20)->default('real');
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->unique(['user_id', 'campaign_key']);
            $table->index(['campaign_key', 'status', 'is_demo', 'score'], 'campaign_ranking_index');
        });

        Schema::create('campaign_challenge_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('campaign_challenge_id')->constrained()->cascadeOnDelete();
            $table->string('type', 80);
            $table->string('direction', 10);
            $table->decimal('amount', 12, 2);
            $table->decimal('balance_before', 12, 2);
            $table->decimal('balance_after', 12, 2);
            $table->nullableMorphs('reference');
            $table->uuid('idempotency_key')->nullable()->unique();
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->index(['campaign_challenge_id', 'created_at'], 'campaign_movement_history_index');
        });

        Schema::create('campaign_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('usuarios')->nullOnDelete();
            $table->foreignId('campaign_challenge_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('campaign_attribution_id')->nullable()->constrained()->nullOnDelete();
            $table->string('campaign_key', 50);
            $table->string('event', 80);
            $table->string('session_id', 64)->nullable();
            $table->string('dedupe_key', 191)->unique();
            $table->string('source', 50)->default('server');
            $table->boolean('is_demo')->default(false);
            $table->string('data_origin', 20)->default('real');
            $table->json('metadata')->nullable();
            $table->timestamp('occurred_at');
            $table->timestamps();
            $table->index(['campaign_key', 'event', 'occurred_at'], 'campaign_event_analytics_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('campaign_events');
        Schema::dropIfExists('campaign_challenge_movements');
        Schema::dropIfExists('campaign_challenges');
        Schema::dropIfExists('campaign_attributions');
    }
};
