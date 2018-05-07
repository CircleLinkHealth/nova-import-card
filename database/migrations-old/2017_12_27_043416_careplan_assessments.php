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
            $table->string('alcohol_misuse_counseling', 4294960);
            $table->string('diabetes_screening_interval', 4294960);
            $table->string('diabetes_screening_last_and_next_date', 4294960);
            $table->string('diabetes_screening_risk', 4294960);
            $table->string('eye_screening_last_and_next_date', 4294960);
            $table->string('key_treatment', 4294960);
            $table->string('patient_functional_assistance_areas', 4294960);
            $table->string('patient_psychosocial_areas_to_watch', 4294960);
            $table->string('risk', 4294960);
            $table->string('risk_factors', 4294960);
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
        Schema::drop('careplan_assessments');
    }
}
