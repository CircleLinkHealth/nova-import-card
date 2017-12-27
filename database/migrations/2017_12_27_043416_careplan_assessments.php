<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CareplanAssessments extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('careplan_assessments', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('careplan_id')->unsigned();
            $table->integer('provider_approver_id')->unsigned();
            $table->string('alcohol_misuse_counseling');
            $table->string('diabetes_screening_interval');
            $table->string('diabetes_screening_last_and_next_date');
            $table->json('diabetes_screening_risk');
            $table->string('eye_screening_last_and_next_date');
            $table->string('key_treatment');
            $table->json('patient_functional_assistance_areas');
            $table->json('patient_psychosocial_areas_to_watch');
            $table->string('risk');
            $table->json('risk_factors');
            $table->string('tobacco_misuse_counseling');
            $table->timestamps();
        });

        Schema::table('careplan_assessments', function (Blueprint $table) {
            $table->foreign('careplan_id')->references('user_id')->on('care_plans');
            $table->foreign('provider_approver_id')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //Schema::drop('careplan_assessments');
    }
}
