<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('usuarios', function (Blueprint $table) {
            $table->timestamp('marketing_emails_opted_out_at')->nullable()->after('remember_token');
        });

        Schema::create('campaign_mail_deliveries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('usuarios')->cascadeOnDelete();
            $table->string('campaign_key', 50);
            $table->string('message_key', 100);
            $table->string('status', 20)->default('processing');
            $table->timestamp('sent_at')->nullable();
            $table->text('failure')->nullable();
            $table->timestamps();
            $table->unique(['user_id', 'campaign_key', 'message_key'], 'campaign_mail_delivery_unique');
            $table->index(['campaign_key', 'message_key', 'status'], 'campaign_mail_status_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('campaign_mail_deliveries');

        Schema::table('usuarios', function (Blueprint $table) {
            $table->dropColumn('marketing_emails_opted_out_at');
        });
    }
};
