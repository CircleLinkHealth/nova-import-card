<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreatePatientContactWindowTable extends Migration
{
    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::drop('patient_contact_window');
    }

    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('patient_contact_window', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('patient_info_id')->unsigned()->index('patient_contact_window_patient_info_id_foreign');
            $table->integer('day_of_week');
            $table->time('window_time_start');
            $table->time('window_time_end');
            $table->timestamps();
        });
    }
}
