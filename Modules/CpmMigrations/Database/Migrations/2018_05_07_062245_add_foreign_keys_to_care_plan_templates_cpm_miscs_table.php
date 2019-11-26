<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddForeignKeysToCarePlanTemplatesCpmMiscsTable extends Migration
{
    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('care_plan_templates_cpm_miscs', function (Blueprint $table) {
            $table->dropForeign('care_plan_templates_cpm_miscs_care_plan_template_id_foreign');
            $table->dropForeign('care_plan_templates_cpm_miscs_cpm_instruction_id_foreign');
            $table->dropForeign('care_plan_templates_cpm_miscs_cpm_misc_id_foreign');
        });
    }

    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('care_plan_templates_cpm_miscs', function (Blueprint $table) {
            $table->foreign('care_plan_template_id')->references('id')->on('care_plan_templates')->onUpdate('CASCADE')->onDelete('CASCADE');
            $table->foreign('cpm_instruction_id')->references('id')->on('cpm_instructions')->onUpdate('CASCADE')->onDelete('SET NULL');
            $table->foreign('cpm_misc_id')->references('id')->on('cpm_miscs')->onUpdate('CASCADE')->onDelete('CASCADE');
        });
    }
}
