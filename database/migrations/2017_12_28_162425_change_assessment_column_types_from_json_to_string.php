<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ChangeAssessmentColumnTypesFromJsonToString extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('careplan_assessments', function ($table) {
            // Will set the type to string.
            $table->string('diabetes_screening_risk', 4294960)->change();
            $table->string('patient_functional_assistance_areas', 4294960)->change();
            $table->string('patient_psychosocial_areas_to_watch', 4294960)->change();
            $table->string('risk_factors', 4294960)->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
        $table->json('diabetes_screening_risk')->change();
        $table->json('patient_functional_assistance_areas')->change();
        $table->json('patient_psychosocial_areas_to_watch')->change();
        $table->json('risk_factors')->change();
    }
}
