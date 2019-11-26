<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateCarePlanTemplatesCpmProblemsTable extends Migration
{
    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::drop('care_plan_templates_cpm_problems');
    }

    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('care_plan_templates_cpm_problems', function (Blueprint $table) {
            $table->increments('id');
            $table->boolean('has_instruction')->default(0);
            $table->integer('cpm_instruction_id')->unsigned()->nullable()->index('care_plan_templates_cpm_problems_cpm_instruction_id_foreign');
            $table->integer('page')->unsigned();
            $table->integer('ui_sort')->unsigned()->nullable();
            $table->integer('care_plan_template_id')->unsigned();
            $table->integer('cpm_problem_id')->unsigned()->index('care_plan_templates_cpm_problems_cpm_problem_id_foreign');
            $table->timestamps();
            $table->index(['care_plan_template_id', 'cpm_problem_id'], 'cpt_id_problems_index');
        });
    }
}
