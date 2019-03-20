<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateCpmProblemsActivateCpmMedicationGroupsTable extends Migration
{
    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::drop('cpm_problems_activate_cpm_medication_groups');
    }

    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('cpm_problems_activate_cpm_medication_groups', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('care_plan_template_id')->unsigned()->index('cpt_problem_activates_med_grp_foreign');
            $table->integer('cpm_problem_id')->unsigned()->index('cpm_problem_problem_activates_med_grp_foreign');
            $table->integer('cpm_medication_group_id')->unsigned();
            $table->timestamps();
            $table->unique(['cpm_medication_group_id', 'care_plan_template_id', 'cpm_problem_id'], 'cpt_medication_careplan_problem_activates_med_group');
        });
    }
}
