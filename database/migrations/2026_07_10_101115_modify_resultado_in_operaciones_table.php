<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {

        Schema::table('operaciones', function (Blueprint $table) {

            $table->decimal('resultado', 12, 4)
                ->change();

        });

    }

    public function down(): void
    {

        Schema::table('operaciones', function (Blueprint $table) {

            $table->integer('resultado')
                ->change();

        });

    }
};
