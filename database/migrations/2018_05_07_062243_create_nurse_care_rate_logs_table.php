<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateNurseCareRateLogsTable extends Migration
{
    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::drop('nurse_care_rate_logs');
    }

    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('nurse_care_rate_logs', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('nurse_id')->unsigned()->index('nurse_care_rate_logs_nurse_id_foreign');
            $table->integer('activity_id')->unsigned()->nullable()->index('nurse_care_rate_logs_activity_id_foreign');
            $table->string('ccm_type');
            $table->integer('increment');
            $table->timestamps();
        });
    }
}
