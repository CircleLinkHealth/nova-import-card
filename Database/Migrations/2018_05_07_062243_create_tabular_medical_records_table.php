<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateTabularMedicalRecordsTable extends Migration
{
    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::drop('tabular_medical_records');
    }

    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('tabular_medical_records', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('practice_id')->unsigned()->nullable()->index('tabular_medical_records_practice_id_foreign');
            $table->integer('location_id')->unsigned()->nullable()->index('tabular_medical_records_location_id_foreign');
            $table->integer('billing_provider_id')->unsigned()->nullable()->index('tabular_medical_records_billing_provider_id_foreign');
            $table->integer('uploaded_by')->unsigned()->nullable()->index('tabular_medical_records_uploaded_by_foreign');
            $table->integer('patient_id')->unsigned()->nullable()->index('tabular_medical_records_patient_id_foreign');
            $table->string('first_name')->nullable();
            $table->string('last_name')->nullable();
            $table->date('dob')->nullable();
            $table->string('problems_string')->nullable();
            $table->string('medications_string')->nullable();
            $table->string('allergies_string')->nullable();
            $table->string('provider_name')->nullable();
            $table->string('mrn')->nullable();
            $table->string('gender')->nullable();
            $table->string('language')->nullable();
            $table->date('consent_date');
            $table->string('primary_phone')->nullable();
            $table->string('cell_phone')->nullable();
            $table->string('home_phone')->nullable();
            $table->string('work_phone')->nullable();
            $table->string('email')->nullable();
            $table->string('address')->nullable();
            $table->string('address2')->nullable();
            $table->string('city')->nullable();
            $table->string('state')->nullable();
            $table->string('zip')->nullable();
            $table->string('primary_insurance')->nullable();
            $table->string('secondary_insurance')->nullable();
            $table->string('tertiary_insurance')->nullable();
            $table->timestamps();
            $table->string('preferred_call_times')->nullable();
            $table->string('preferred_call_days')->nullable();
            $table->softDeletes();
        });
    }
}
