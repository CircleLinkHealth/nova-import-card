<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddForeignKeysToCpmProblemsActivateCpmMedicationGroupsTable extends Migration
{
    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('cpm_problems_activate_cpm_medication_groups', function (Blueprint $table) {
            $table->dropForeign('cpm_med_grp_problem_activates_med_grp_foreign');
            $table->dropForeign('cpm_problem_problem_activates_med_grp_foreign');
            $table->dropForeign('cpt_problem_activates_med_grp_foreign');
        });
    }

    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('cpm_problems_activate_cpm_medication_groups', function (Blueprint $table) {
            $table->foreign('cpm_medication_group_id', 'cpm_med_grp_problem_activates_med_grp_foreign')->references('id')->on('cpm_medication_groups')->onUpdate('CASCADE')->onDelete('CASCADE');
            $table->foreign('cpm_problem_id', 'cpm_problem_problem_activates_med_grp_foreign')->references('id')->on('cpm_problems')->onUpdate('CASCADE')->onDelete('CASCADE');
            $table->foreign('care_plan_template_id', 'cpt_problem_activates_med_grp_foreign')->references('id')->on('care_plan_templates')->onUpdate('CASCADE')->onDelete('CASCADE');
        });
    }
}
