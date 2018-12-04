<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateCcmTimeApiLogsTable extends Migration
{
    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::drop('ccm_time_api_logs');
    }

    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('ccm_time_api_logs', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('activity_id')->unsigned()->index('activity_id_foreign');
            $table->timestamps();
        });
    }
}
