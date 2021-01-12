<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddForeignKeysToCarePlanTemplatesCpmMedicationGroupsTable extends Migration
{
    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('care_plan_templates_cpm_medication_groups', function (Blueprint $table) {
            $table->dropForeign('care_plan_templates_cpm_medication_groups_instrction_foreign');
            $table->dropForeign('cpm_medi_groups_rel_foreign');
            $table->dropForeign('cpt_id_cpt_id_rel_foreign');
        });
    }

    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('care_plan_templates_cpm_medication_groups', function (Blueprint $table) {
            $table->foreign('cpm_instruction_id', 'care_plan_templates_cpm_medication_groups_instrction_foreign')->references('id')->on('cpm_instructions')->onUpdate('CASCADE')->onDelete('SET NULL');
            $table->foreign('cpm_medication_group_id', 'cpm_medi_groups_rel_foreign')->references('id')->on('cpm_medication_groups')->onUpdate('CASCADE')->onDelete('CASCADE');
            $table->foreign('care_plan_template_id', 'cpt_id_cpt_id_rel_foreign')->references('id')->on('care_plan_templates')->onUpdate('CASCADE')->onDelete('CASCADE');
        });
    }
}
