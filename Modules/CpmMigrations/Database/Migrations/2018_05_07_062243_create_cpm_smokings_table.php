<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateCpmSmokingsTable extends Migration
{
    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::drop('cpm_smokings');
    }

    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('cpm_smokings', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('patient_id')->unsigned()->index('cpm_smokings_patient_id_foreign');
            $table->string('starting');
            $table->string('target');
            $table->timestamps();
        });
    }
}
