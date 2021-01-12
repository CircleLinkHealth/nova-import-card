<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateCareplanAssessmentsTable extends Migration
{
    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::drop('careplan_assessments');
    }

    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('careplan_assessments', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('careplan_id')->unsigned();
            $table->integer('provider_approver_id')->unsigned()->index('careplan_assessments_provider_approver_id_foreign');
            $table->text('alcohol_misuse_counseling');
            $table->text('diabetes_screening_interval');
            $table->text('diabetes_screening_risk', 16777215);
            $table->text('key_treatment');
            $table->text('patient_functional_assistance_areas', 16777215);
            $table->text('patient_psychosocial_areas_to_watch', 16777215);
            $table->text('risk');
            $table->text('risk_factors', 16777215);
            $table->string('tobacco_misuse_counseling');
            $table->timestamps();
            $table->date('diabetes_screening_last_date');
            $table->date('diabetes_screening_next_date');
            $table->date('eye_screening_last_date');
            $table->date('eye_screening_next_date');
        });
    }
}
