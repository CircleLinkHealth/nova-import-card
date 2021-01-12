<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddForeignKeysToCarePlanTemplatesCpmBiometricsTable extends Migration
{
    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('care_plan_templates_cpm_biometrics', function (Blueprint $table) {
            $table->dropForeign('care_plan_templates_cpm_biometrics_care_plan_template_id_foreign');
            $table->dropForeign('care_plan_templates_cpm_biometrics_cpm_biometric_id_foreign');
            $table->dropForeign('care_plan_templates_cpm_biometrics_cpm_instruction_id_foreign');
        });
    }

    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('care_plan_templates_cpm_biometrics', function (Blueprint $table) {
            $table->foreign('care_plan_template_id')->references('id')->on('care_plan_templates')->onUpdate('CASCADE')->onDelete('CASCADE');
            $table->foreign('cpm_biometric_id')->references('id')->on('cpm_biometrics')->onUpdate('CASCADE')->onDelete('CASCADE');
            $table->foreign('cpm_instruction_id')->references('id')->on('cpm_instructions')->onUpdate('CASCADE')->onDelete('SET NULL');
        });
    }
}
