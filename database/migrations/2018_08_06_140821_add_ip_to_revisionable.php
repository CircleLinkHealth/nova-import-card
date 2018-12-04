<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddIpToRevisionable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('revisions', function (Blueprint $table) {
            if (! Schema::hasColumns('revisions', ['ip'])) {
                $table->string('ip')
                      ->nullable()
                      ->after('new_value');
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('revisions', function (Blueprint $table) {
            $table->dropColumn('ip');
        });
    }
}
