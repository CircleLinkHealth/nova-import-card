<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreatePatientReportsTable extends Migration
{
    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::drop('patient_reports');
    }

    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('patient_reports', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('patient_id')->unsigned()->index('patient_reports_patient_id_foreign');
            $table->string('patient_mrn');
            $table->string('provider_id');
            $table->string('file_type');
            $table->integer('location_id')->unsigned()->index('patient_reports_location_id_foreign');
            $table->text('file_base64', 16777215);
            $table->softDeletes();
            $table->timestamps();
        });
    }
}
