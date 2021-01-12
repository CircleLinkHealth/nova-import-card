<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProviderReportsTable extends Migration
{
    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('provider_reports');
    }

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('provider_reports', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('patient_id');
            $table->unsignedInteger('hra_instance_id');
            $table->unsignedInteger('vitals_instance_id');
            $table->string('reason_for_visit');
            $table->json('demographic_data');
            $table->json('allergy_history');
            $table->json('medical_history');
            $table->json('medication_history');
            $table->json('family_medical_history');
            $table->json('immunization_history');
            $table->json('screenings');
            $table->json('mental_state');
            $table->json('vitals');
            $table->json('diet');
            $table->json('social_factors');
            $table->json('sexual_activity');
            $table->json('exercise_activity_levels');
            $table->json('functional_capacity');
            $table->json('current_providers');
            $table->json('specific_patient_requests')->nullable();
            $table->json('advanced_care_planning')->nullable();
            $table->timestamps();

            /*

             $table->unique(['patient_id', 'hra_instance_id', 'vitals_instance_id'],
                 'provider_reports_patient_hra_vitals_unique');*/
        });
    }
}
