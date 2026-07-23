<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('usuarios', function (Blueprint $table): void {
            $table->text('admin_mfa_secret')->nullable()->after('remember_token');
            $table->timestamp('admin_mfa_enabled_at')->nullable()->after('admin_mfa_secret');
        });
    }

    public function down(): void
    {
        Schema::table('usuarios', function (Blueprint $table): void {
            $table->dropColumn(['admin_mfa_secret', 'admin_mfa_enabled_at']);
        });
    }
};
