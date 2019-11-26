<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateCpmBloodPressuresTable extends Migration
{
    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::drop('cpm_blood_pressures');
    }

    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('cpm_blood_pressures', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('patient_id')->unsigned()->index('cpm_blood_pressures_patient_id_foreign');
            $table->string('starting');
            $table->string('target');
            $table->string('systolic_high_alert');
            $table->string('systolic_low_alert');
            $table->string('diastolic_high_alert');
            $table->string('diastolic_low_alert');
            $table->timestamps();
        });
    }
}
