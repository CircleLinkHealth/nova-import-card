<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddForeignKeysToCarePlanTemplatesCpmSymptomsTable extends Migration
{
    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('care_plan_templates_cpm_symptoms', function (Blueprint $table) {
            $table->dropForeign('care_plan_templates_cpm_symptoms_care_plan_template_id_foreign');
            $table->dropForeign('care_plan_templates_cpm_symptoms_cpm_instruction_id_foreign');
            $table->dropForeign('care_plan_templates_cpm_symptoms_cpm_symptom_id_foreign');
        });
    }

    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('care_plan_templates_cpm_symptoms', function (Blueprint $table) {
            $table->foreign('care_plan_template_id')->references('id')->on('care_plan_templates')->onUpdate('CASCADE')->onDelete('CASCADE');
            $table->foreign('cpm_instruction_id')->references('id')->on('cpm_instructions')->onUpdate('CASCADE')->onDelete('SET NULL');
            $table->foreign('cpm_symptom_id')->references('id')->on('cpm_symptoms')->onUpdate('CASCADE')->onDelete('CASCADE');
        });
    }
}
