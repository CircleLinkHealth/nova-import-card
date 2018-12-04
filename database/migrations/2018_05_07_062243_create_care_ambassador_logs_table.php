<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateCareAmbassadorLogsTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('care_ambassador_logs', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('enroller_id')->unsigned()->nullable()->index('care_ambassador_logs_enroller_id_foreign');
            $table->date('day');
            $table->integer('no_enrolled');
            $table->integer('no_rejected');
            $table->integer('no_utc');
            $table->integer('total_calls');
            $table->integer('total_time_in_system');
            $table->timestamps();
        });
    }


    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('care_ambassador_logs');
    }
}
