<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateLvObservationsTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('lv_observations', function (Blueprint $table) {
            $table->increments('id');
            $table->dateTime('obs_date')->default('0000-00-00 00:00:00');
            $table->dateTime('obs_date_gmt')->default('0000-00-00 00:00:00');
            $table->integer('comment_id')->unsigned();
            $table->integer('sequence_id')->unsigned();
            $table->string('obs_message_id', 30);
            $table->integer('user_id')->unsigned();
            $table->string('obs_method', 30);
            $table->string('obs_key', 30);
            $table->string('obs_value', 30);
            $table->string('obs_unit', 30);
            $table->integer('program_id')->unsigned();
            $table->integer('legacy_obs_id')->unsigned();
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
        Schema::drop('lv_observations');
    }
}
