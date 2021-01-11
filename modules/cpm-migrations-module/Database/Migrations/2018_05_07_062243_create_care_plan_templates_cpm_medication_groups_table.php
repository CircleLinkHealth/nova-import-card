<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateCarePlanTemplatesCpmMedicationGroupsTable extends Migration
{
    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::drop('care_plan_templates_cpm_medication_groups');
    }

    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('care_plan_templates_cpm_medication_groups', function (Blueprint $table) {
            $table->increments('id');
            $table->boolean('has_instruction')->default(0);
            $table->integer('cpm_instruction_id')->unsigned()->nullable()->index('care_plan_templates_cpm_medication_groups_instrction_foreign');
            $table->integer('page')->unsigned();
            $table->integer('ui_sort')->unsigned()->nullable();
            $table->integer('care_plan_template_id')->unsigned();
            $table->integer('cpm_medication_group_id')->unsigned()->index('cpm_medi_groups_rel_foreign');
            $table->timestamps();
            $table->index(['care_plan_template_id', 'cpm_medication_group_id'], 'med_grp_cpt_index');
        });
    }
}
