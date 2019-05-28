<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePatientDatasTable extends Migration
{
    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::dropIfExists('patient_data');
    }

    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create(
            'patient_data',
            function (Blueprint $table) {
                $table->increments('id');
                $table->date('dob');
                $table->string('first_name');
                $table->string('last_name');
                $table->string('mrn')->unique();
                $table->timestamps();
            }
        );
    }
}
