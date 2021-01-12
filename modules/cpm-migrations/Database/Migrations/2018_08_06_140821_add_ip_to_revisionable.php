<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddIpToRevisionable extends Migration
{
    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('revisions', function (Blueprint $table) {
            $table->dropColumn('ip');
        });
    }

    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('revisions', function (Blueprint $table) {
            if ( ! Schema::hasColumns('revisions', ['ip'])) {
                $table->string('ip')
                    ->nullable()
                    ->after('new_value');
            }
        });
    }
}
