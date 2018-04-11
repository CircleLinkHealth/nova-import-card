<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

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
            $table->date('month-year');
            $table->integer('no_enrolled');
            $table->integer('no_rejected');
            $table->integer('no_utc');
            $table->integer('total_calls');
            $table->integer('total_time_in_system');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('care_ambassador_logs', function (Blueprint $table) {

            $table->drop();
        });
    }
}
