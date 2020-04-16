<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CareplanAssessmentHerokuEdits extends Migration
{
    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
    }

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('careplan_assessments', function (Blueprint $table) {
            $table->text('alcohol_misuse_counseling')->nullable()->change();
            $table->text('diabetes_screening_interval')->nullable()->change();
            $table->text('diabetes_screening_risk', 16777215)->nullable()->change();
            $table->text('key_treatment')->nullable()->change();
            $table->text('patient_functional_assistance_areas', 16777215)->nullable()->change();
            $table->text('patient_psychosocial_areas_to_watch', 16777215)->nullable()->change();
            $table->text('risk')->nullable()->change();
            $table->text('risk_factors', 16777215)->nullable()->change();
            $table->string('tobacco_misuse_counseling')->nullable()->change();
            $table->date('diabetes_screening_last_date')->nullable()->change();
            $table->date('diabetes_screening_next_date')->nullable()->change();
            $table->date('eye_screening_last_date')->nullable()->change();
            $table->date('eye_screening_next_date')->nullable()->change();
        });
    }
}
