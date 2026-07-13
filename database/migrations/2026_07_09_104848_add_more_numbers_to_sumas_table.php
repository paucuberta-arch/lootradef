<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {

        Schema::table('sumas', function (Blueprint $table) {

            $table->integer('numero3')
                ->nullable()
                ->after('numero2');

            $table->integer('numero4')
                ->nullable()
                ->after('numero3');

            $table->integer('numero5')
                ->nullable()
                ->after('numero4');

        });

    }

    public function down()
    {

        Schema::table('sumas', function (Blueprint $table) {

            $table->dropColumn([
                'numero3',
                'numero4',
                'numero5',
            ]);

        });

    }
};
