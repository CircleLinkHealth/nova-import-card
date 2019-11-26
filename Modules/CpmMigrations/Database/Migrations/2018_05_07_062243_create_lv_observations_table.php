<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateLvObservationsTable extends Migration
{
    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::drop('lv_observations');
    }

    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('lv_observations', function (Blueprint $table) {
            $table->increments('id');
            $table->dateTime('obs_date')->nullable();
            $table->dateTime('obs_date_gmt')->nullable();
            $table->integer('comment_id')->unsigned();
            $table->integer('sequence_id')->unsigned();
            $table->string('obs_message_id', 30);
            $table->integer('user_id')->unsigned()->index();
            $table->string('obs_method', 30);
            $table->string('obs_key', 30);
            $table->string('obs_value', 30);
            $table->string('obs_unit', 30);
            $table->integer('program_id')->unsigned();
            $table->integer('legacy_obs_id')->unsigned();
            $table->timestamps();
        });
    }
}
