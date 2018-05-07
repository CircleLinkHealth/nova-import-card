<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateCpmProblemsActivateCpmSymptomsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('cpm_problems_activate_cpm_symptoms', function(Blueprint $table)
		{
			$table->increments('id');
			$table->integer('care_plan_template_id')->unsigned()->index('cpt_problem_activates_symptom_foreign');
			$table->integer('cpm_problem_id')->unsigned()->index('cpm_problem_problem_activates_symptom_foreign');
			$table->integer('cpm_symptom_id')->unsigned();
			$table->timestamps();
			$table->unique(['cpm_symptom_id','care_plan_template_id','cpm_problem_id'], 'cpt_problem_activates_med_group');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('cpm_problems_activate_cpm_symptoms');
	}

}
