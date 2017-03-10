<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

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
            $table->unsignedInteger('care_ambassador_id');
            $table->date('month-year');
            $table->integer('no_enrolled');
            $table->integer('no_rejected');
            $table->integer('no_utc');
            $table->integer('total_calls');
            $table->integer('total_time_in_system');

            $table->foreign('care_ambassador_id')
                ->references('id')
                ->on('users')
                ->onUpdate('cascade')
                ->onDelete('cascade');

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
