<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateNurseContactWindowTable extends Migration
{
    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::drop('nurse_contact_window');
    }

    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('nurse_contact_window', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('nurse_info_id')->unsigned()->index('nurse_contact_window_nurse_info_id_foreign');
            $table->date('date');
            $table->integer('day_of_week');
            $table->time('window_time_start');
            $table->time('window_time_end');
            $table->timestamps();
            $table->softDeletes();
        });
    }
}
