<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateCpmBloodSugarsTable extends Migration
{
    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::drop('cpm_blood_sugars');
    }

    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('cpm_blood_sugars', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('patient_id')->unsigned()->index('cpm_blood_sugars_patient_id_foreign');
            $table->string('starting');
            $table->string('target');
            $table->string('starting_a1c');
            $table->string('high_alert');
            $table->string('low_alert');
            $table->timestamps();
        });
    }
}
