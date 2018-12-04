<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddForeignKeysToCareAmbassadorLogsTable extends Migration
{
    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('care_ambassador_logs', function (Blueprint $table) {
            $table->dropForeign('care_ambassador_logs_enroller_id_foreign');
        });
    }

    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('care_ambassador_logs', function (Blueprint $table) {
            $table->foreign('enroller_id')->references('id')->on('care_ambassadors')->onUpdate('CASCADE')->onDelete('CASCADE');
        });
    }
}
