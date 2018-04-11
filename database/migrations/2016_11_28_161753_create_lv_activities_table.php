<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateLvActivitiesTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('lv_activities', function (Blueprint $table) {
            $table->increments('id');
            $table->string('type')->nullable()->index('type');
            $table->integer('duration')->unsigned();
            $table->string('duration_unit', 30)->nullable();
            $table->integer('patient_id')->unsigned()->index('patient_id');
            $table->integer('provider_id')->unsigned()->index('provider_id');
            $table->integer('logger_id')->unsigned();
            $table->integer('comment_id')->unsigned()->index('comment_id');
            $table->integer('sequence_id')->unsigned()->nullable();
            $table->string('obs_message_id', 30)->index('obs_message_id');
            $table->string('logged_from', 30);
            $table->dateTime('performed_at')->default('0000-00-00 00:00:00')->index('preformed_at');
            $table->dateTime('performed_at_gmt')->default('0000-00-00 00:00:00');
            $table->timestamps();
            $table->softDeletes();
            $table->integer('page_timer_id')->unsigned()->nullable();
        });
    }


    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('lv_activities');
    }
}
