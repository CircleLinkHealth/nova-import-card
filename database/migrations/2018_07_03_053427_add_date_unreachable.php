<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDateUnreachable extends Migration
{
    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('patient_info', function (Blueprint $table) {
            $table->dropColumn('date_unreachable');
        });
    }

    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('patient_info', function (Blueprint $table) {
            $table->dateTime('date_unreachable')
                ->after('date_withdrawn')
                ->nullable();

            $table->dateTime('date_withdrawn')
                ->nullable()
                ->change();

            $table->dateTime('date_paused')
                ->nullable()
                ->change();
        });
    }
}
